<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App;

require_once __DIR__ . '/../src/settings.php';

require_once __DIR__ . '/../src/dependencies.php';

$uri = $app->getContainer()->get('uri');
$host = $uri->getHost();
$cookieDomain = explode('.', $host);

if (count($cookieDomain) > 2) {
    array_shift($cookieDomain);
    $cookieDomain = "." . join(".", $cookieDomain);
} else {
    $cookieDomain = $host;
}

session_start([
    'cookie_domain' => $cookieDomain
]);

$app->get('/', function (Request $request, Response $response) {
    $env = [];
    foreach (['SCRIPT_NAME', 'REQUEST_URI', 'QUERY_STRING'] as $key) {
        $env[$key] = $_SERVER[$key];
    }
    return $this->view->render($response, 'index.html.twig', [
        'session' => $_SESSION,
        'uri' => $this->uri,
        'env' => $env,
    ]);
})->setName('home');

$app->get('/update', function (Request $request, Response $response) {
    if (isset($_SESSION['user'])) {
        $this->userDao->update($_SESSION['user']);
    }

    return $response->withRedirect('/');
})->setName('update');

$app->get('/auth', function (Request $request, Response $response) {
    // 認証
    if (!isset($_SESSION['roles'])) {
        return $response->withStatus(401);
    }

    // 認可
    if (isset($_SERVER['HTTP_ROLE']) && !in_array($_SESSION['HTTP_ROLE'], $_SESSION['roles'], true)) {
        return $response->withStatus(403);
    }

    return $response->withStatus(200);
});

$app->group('/admin', function () {
    $this->get('/users', function (Request $request, Response $response) {
        if (!isset($_SESSION['roles'])) {
            return $response->withStatus(401);
        }

        if (!in_array('ADMIN', $_SESSION['roles'], true)) {
            return $response->withStatus(403);
        }

        $users = $this->userDao->getAll();
        return $this->view->render($response, 'admin/users.html.twig', [
            'users' => $users
        ]);
    });

    $this->post('/users/{user_id}', function (Request $request, Response $response, $args) {
        var_dump($args);
        exit(0);
    })->setName('user_update');
});


$app->get('/private', function (Request $request, Response $response) {
    return $this->view->render($response, 'private.html.twig');
});

$app->get('/private/test', function (Request $request, Response $response) {
    return $this->view->render($response, 'private.html.twig');
});


$app->get('/logout', function (Request $request, Response $response) {
    $rd = $request->getParam('rd');
    $rd = filter_var($rd, FILTER_VALIDATE_URL);
    $rd = filter_var($rd, FILTER_SANITIZE_URL);

    if (!$rd) {
        return $response->withStatus(400);
    }

    session_destroy();

    return $response->withRedirect($rd);

})->setName('logout');

$app->group('/github', function () {

    $this->get('', function (Request $request, Response $response) {
        $rd = $request->getParam('rd');
        $rd = filter_var($rd, FILTER_VALIDATE_URL);
        $rd = filter_var($rd, FILTER_SANITIZE_URL);
        if (!$rd) {
            return $response->withStatus(400);
        }

        if (isset($_SESSION['access_token'])) {
            $user = fetchUserInfo($_SESSION['access_token']);
            if ($user) {
                return $response->withRedirect($rd);
            }
        }

        $_SESSION['rd'] = $rd;

        $query = http_build_query(['client_id' => CLIENT_ID, 'scope' => 'read:user']);
        $url = 'https://github.com/login/oauth/authorize?' . $query;

        return $response->withRedirect($url);
    })->setName('login');

    $this->get('/callback', function (Request $request, Response $response) {
        if (($code = $request->getQueryParam('code')) === null) {
            return $response->withStatus(400);
        }

        if (!isset($_SESSION['rd'])) {
            error_log('redirect url is not set.');
            return $response->withStatus(400);
        }

        $rd = $_SESSION['rd'];
        unset($_SESSION['rd']);

        list($str, $httpStatus) = http_post('https://github.com/login/oauth/access_token', [
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'code' => $code,
        ]);

        if ($httpStatus !== 200) {
            error_log('Failed to fetch access token.');
            return $response->withRedirect($this->uri->getBaseUrl());
        }

        parse_str($str, $data);
        $accessToken = $data['access_token'];

        $user = fetchUserInfo($accessToken);
        if (!$user) {
            error_log('ユーザー情報の取得に失敗.');
            return $response->withRedirect($this->uri->getBaseUrl());
        }

        $userRoleDao = new \Dao\UserRoleDao($this->pdo);
        $roles = $userRoleDao->findByUserId($user['user_id']);

        $_SESSION['access_token'] = $accessToken;
        $_SESSION['user'] = $user;
        $_SESSION['roles'] = $roles;

        if (!$this->userDao->update($user)) {
            error_log('ユーザー情報のセーブに失敗.');
        }

        return $response->withRedirect($rd);
    });
});

$app->run();
