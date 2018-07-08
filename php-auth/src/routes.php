<?php

use Controller\Admin\UserController;
use Controller\GithubController;

$app->group('/github', function () {
    $this->get('', GithubController::class . ':start')->setName('login');
    $this->get('/callback', GithubController::class . ':callback');
});

$app->group('/admin', function () {
    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('admin_users');
        $this->post('/{user_id}', UserController::class . ':update')->setName('admin_user_update');
    });
});

