<?php

use Controller\HomeController;
use Controller\StaticController;
use Controller\OAuth\GithubController;
use Controller\OAuth\GoogleController;
use Controller\Admin\UserController;
use Controller\Admin\StorageController;
use Slim\Http\Request;
use Slim\Http\Response;


$app->get('/dist[/{params:.*}]', StaticController::class . ':dist');
$app->get('/images[/{params:.*}]', StaticController::class . ':images');

$app->get('/', HomeController::class . ':home')->setName('home');
$app->post('/', HomeController::class . ':userUpdate')->setName('home_user_update');
$app->get('/auth', HomeController::class . ':auth')->setName('auth');
$app->get('/logout', HomeController::class . ':signOut')->setName('logout');
$app->get('/signout', HomeController::class . ':signOut')->setName('signout');
$app->get('/destroy', HomeController::class . ':sessionDestroy')->setName('session_destroy');

$app->group('/github', function () {
    $this->get('', GitHubController::class . ':start')->setName('login');
    $this->get('/callback', GitHubController::class . ':callback');
});

$app->group('/google', function () {
    $this->get('', GoogleController::class . ':start')->setName('oauth_google');
    $this->get('/callback', GoogleController::class . ':callback')->setName('oauth_google_callback');
});

$app->group('/admin', function () {
    $this->get('', function (Request $request, Response $response) {
        $session = $this->get('session');
        return $this->view->render($response, 'admin/index.html.twig', [
            'session' => $session,
            'uri' => $this->uri,
        ]);
    })->setName('admin');

    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('users');
        $this->get('/{user_id:[a-f0-9]+}', UserController::class . ':show')->setName('user');
        $this->post('/{user_id:[a-f0-9]+}/roles', UserController::class . ':userAddRole')->setName('user_add_role');
        $this->post('/{user_id:[a-f0-9]+}/roles/{role}', UserController::class . ':userRemoveRole')->setName('user_remove_role');
        $this->post('/{user_id:[a-f0-9]+}/providers/{provider_id}', UserController::class . ':userRemoveProvider')->setName('user_remove_provider');
    });
    $this->group('/storage', function () {
        $this->get('/redis', StorageController::class . ':redis')->setName('storage_redis');
    });
})->add(function (Request $request, Response $response, callable $next) {
    /** @var \Util\Session $session */
    $session = $this->get('session');
    if (!isset($session['roles'])) {
        $session['flash'] = 'ログインしてください。';
        return $response->withRedirect("/");
    }

    if (!in_array('ADMIN', $session['roles'], true)) {
        $session['flash'] = 'アクセス権限がありません。';
        return $response->withRedirect("/");
    }

    return $next($request, $response);
});
