<?php

use Controller\Admin\UserController;
use Controller\GithubController;

$app->group('/github', function () {
    $this->get('', GithubController::class . ':start')->setName('login');
    $this->get('/callback', GithubController::class . ':callback');
});

$app->group('/admin', function () {
    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('users');
        $this->get('/{user_id:[0-9]+}', UserController::class . ':show')->setName('user');
        $this->post('/{user_id:[0-9]+}', UserController::class . ':update')->setName('user');
        $this->post('/{user_id:[0-9]+}/roles', UserController::class . ':userAddRole')->setName('user_add_role');
        $this->post('/{user_id:[0-9]+}/roles/${role}', UserController::class . ':userRemoveRole')->setName('user_remove_role');
    });
});

