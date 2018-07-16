<?php

use Controller\AuthController;
use Controller\GithubController;
use Controller\Admin\UserController;
use Controller\Admin\StorageController;
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/auth', AuthController::class . ':auth')->setName('auth');
$app->get('/logout', AuthController::class . ':signOut')->setName('logout');
$app->get('/signout', AuthController::class . ':signOut')->setName('signout');

$app->group('/github', function () {
    $this->get('', GitHubController::class . ':start')->setName('login');
    $this->get('/callback', GitHubController::class . ':callback');
});

$app->group('/admin', function () {
    $this->get('', function (Request $request, Response $response) {
        $session = $this->get('session');
        return $this->view->render($response, 'admin/index.html.twig', [
            'session' => $session,
            'uri' => $this->uri,
        ]);
    });

    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('users');
        $this->get('/{user_id:[a-f0-9]+}', UserController::class . ':show')->setName('user');
        $this->post('/{user_id:[a-f0-9]+}/roles', UserController::class . ':userAddRole')->setName('user_add_role');
        $this->post('/{user_id:[a-f0-9]+}/roles/{role}', UserController::class . ':userRemoveRole')->setName('user_remove_role');
    });
    $this->group('/storage', function () {
        $this->get('/redis', StorageController::class . ':redis')->setName('storage_redis');
    });
})->add(function (Request $request, Response $response, callable $next) {
    $session = $this->get('session');
    if (!isset($session['roles'])) {
        return $response->withStatus(401);
    }

    if (!in_array('ADMIN', $session['roles'], true)) {
        return $response->withStatus(403);
    }

    return $next($request, $response);
});
