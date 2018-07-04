<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$app = new \Slim\App;

require_once __DIR__ . '/../src/settings.php';

require_once __DIR__ . '/../src/dependencies.php';


$app->get('/', function (Request $request, Response $response) {
    $users = $this->userDao->getAll();
    $env = [];
    foreach(['SCRIPT_NAME', 'REQUEST_URI', 'QUERY_STRING'] as $key) {
        $env[$key] = $_SERVER[$key ];
    }
    return $this->view->render($response, 'index.html.twig', [
        'users' => $users,
        'session' => $_SESSION,
        'uri' => $this->uri,
        'env' => $env,
    ]);
});

$app->get('/a', function (Request $request, Response $response) {
    $users = $this->userDao->getAll();
    $env = [];
    foreach(['SCRIPT_NAME', 'REQUEST_URI', 'QUERY_STRING'] as $key) {
        $env[$key] = $_SERVER[$key ];
    }

    return $this->view->render($response, 'index.html.twig', [
        'users' => $users,
        'session' => $_SESSION,
        'uri' => $this->uri,
        'env' => $env,
    ]);
});

$app->get('/auth', function (Request $request, Response $response) {
    if(isset($_SESSION['user'])) {
        return $response->withStatus(200);
    }
    return $response->withStatus(403);
});

$app->get('/private', function (Request $request, Response $response) {
    return $this->view->render($response, 'private.html.twig');
});

$app->get('/private/test', function (Request $request, Response $response) {
    return $this->view->render($response, 'private.html.twig');
});


$app->get('/phpinfo', function (Request $request, Response $response) {
    ob_start();
    phpinfo();
    $tmp = ob_get_clean();
    ob_end_clean();

    $response->getBody()->write($tmp);
    return $response;
});


$app->get('/dump', function (Request $request, Response $response) {
    $tmp = var_export($this->uri, true);
    $response->getBody()->write('<pre>');
    $response->getBody()->write($tmp);
    return $response;
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
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['user'] = $user;

        if (!$this->userDao->update($user)) {
            error_log('ユーザー情報のセーブに失敗.');
        }

        return $response->withRedirect($rd);
    });
});

$app->run();
