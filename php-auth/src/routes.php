<?php

use Controller\Admin\UserController;
use Controller\GithubController;
use Controller\Admin\StorageController;
use Slim\Http\Request;
use Slim\Http\Response;

$app->group('/github', function () {
    $this->get('', GitHubController::class . ':start')->setName('login');
    $this->get('/callback', GitHubController::class . ':callback');
});

$app->group('/admin', function () {
    $this->get('', function (Request $request, Response $response) {
        return $this->view->render($response, 'admin/index.html.twig', [
            'session' => $_SESSION,
            'uri' => $this->uri,
        ]);
    });

    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('users');
        $this->get('/{user_id:[0-9]+}', UserController::class . ':show')->setName('user');
        $this->post('/{user_id:[0-9]+}/roles', UserController::class . ':userAddRole')->setName('user_add_role');
        $this->post('/{user_id:[0-9]+}/roles/{role}', UserController::class . ':userRemoveRole')->setName('user_remove_role');
    });
    $this->group('/storage', function () {
        $this->get('/redis', StorageController::class . ':redis')->setName('storage_redis');
    });
})->add(function (Request $request, Response $response, callable $next) {
    if (!isset($_SESSION['roles'])) {
        return $response->withStatus(401);
    }

    if (!in_array('ADMIN', $_SESSION['roles'], true)) {
        return $response->withStatus(403);
    }

    return $next($request, $response);
});

