<?php

use Controller\Admin\UserController;
use Controller\AdminController;
use Controller\HomeController;
use Controller\OAuth\GitHubController;
use Controller\OAuth\GoogleController;
use Controller\SettingsController;
use Controller\StaticController;
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
$app->get('/signin/token', HomeController::class . ':signinWithToken')->setName('token_signin');


$app->group('/github', function () {
    $this->get('', GitHubController::class . ':start')->setName('login');
    $this->get('/callback', GitHubController::class . ':callback');
});

$app->group('/google', function () {
    $this->get('', GoogleController::class . ':start')->setName('oauth_google');
    $this->get('/callback', GoogleController::class . ':callback');
});

$app->group('/settings', function () {
    $this->get('', SettingsController::class . ':home')->setName('settings');
    $this->get('/profile', SettingsController::class . ':profile')->setName('settings_profile');
    $this->put('/profile', SettingsController::class . ':profileUpdate');
    $this->delete('/social_login/{provider_id:[0-9]+}', SettingsController::class . ':deleteSocialLogin')->setName('settings_social_login');
    $this->get('/account', SettingsController::class . ':account')->setName('settings_account');
    $this->delete('/account', SettingsController::class . ':deleteAccount');
})->add(function (Request $request, Response $response, callable $next) {
    /**
     * @var \Manager\UserManager $userManager
     * @var \Util\Session $session
     */
    $userManager = $this->get('userManager');
    $session = $this->get('session');
    $userId = $session->get('user_id');

    if ($userId === null) {
        $session['flash'] = 'ログインしてください。';
        return $response->withRedirect("/");
    }

    $user = $userManager->getUserByUserId($userId);
    if ($user === null) {
        unset($session['user_id']);
        $session['flash'] = 'ユーザー情報の取得に失敗しました。';
        return $response->withRedirect("/");
    }

    return $next($request, $response);
});

$app->group('/admin', function () {
    $this->get('', AdminController::class . ':index')->setName('admin');
    $this->group('/users', function () {
        $this->get('', UserController::class . ':index')->setName('users');
        $this->get('/new', UserController::class . ':new')->setName('users_new');
        $this->post('', UserController::class . ':create')->setName('users');
        $this->group('/{user_id:[a-f0-9]+}', function () {
            $this->get('', UserController::class . ':show')->setName('user');
            $this->post('/roles', UserController::class . ':addRole')->setName('user_add_role');
            $this->post('/roles/{role}', UserController::class . ':removeRole')->setName('user_remove_role');
            $this->post('/providers/{provider_id}', UserController::class . ':removeProvider')->setName('user_remove_provider');
            $this->post('/signin_token', UserController::class . ':generateSigninToken')->setName('user_signin_token');
            $this->delete('/signin_token', UserController::class . ':deleteSigninToken')->setName('user_signin_token');
        });
    });
    $this->get('/phpinfo', function () {
        phpinfo();
    });
})->add(function (Request $request, Response $response, callable $next) {
    /**
     * @var \Manager\UserManager $userManager
     * @var \Util\Session $session
     */
    $userManager = $this->get('userManager');
    $session = $this->get('session');
    $userId = $session->get('user_id');

    if ($userId === null) {
        $session['flash'] = 'ログインしてください。';
        return $response->withRedirect("/");
    }

    $user = $userManager->getUserByUserId($userId);
    if ($user === null) {
        unset($session['user_id']);
        $session['flash'] = 'ユーザー情報の取得に失敗しました。';
        return $response->withRedirect("/");
    }

    if (!in_array('ADMIN', $user['roles'], true)) {
        $session['flash'] = 'アクセス権限がありません。';
        return $response->withRedirect("/");
    }

    return $next($request, $response);
});
