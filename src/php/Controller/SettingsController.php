<?php

namespace Controller;

use Psr\Http\Message\ResponseInterface;
use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Util\Providers;


class SettingsController extends BaseController
{
    /** @var AuthService */
    private $authService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container['authService'];
    }

    public function home(Request $request, Response $response): ResponseInterface
    {
        return $response->withRedirect($this->router->pathFor('settings_profile'));
    }

    public function profile(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();

        return $this->view->render($response, 'settings/profile.html.twig', [
            'user' => $user,
            'providers' => [
                [
                    'id' => Providers::GITHUB,
                    'name' => 'GitHub',
                    'enable_path' => 'login',
                ], [
                    'id' => Providers::GOOGLE,
                    'name' => 'Google',
                    'enable_path' => 'oauth_google',
                ]
            ],
            'current_url' => (string)$this->uri,
            'current_path' => $this->uri->getPath()
        ]);
    }

    public function profileUpdate(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();

        $name = $request->getParam('name', null);

        if ($name === null or mb_strlen($name) <= 1 or 30 < mb_strlen($name)) {
            $this->session['flash'] = 'パラメータが正しくありません。';
            return $response->withRedirect($this->router->pathFor('settings'));
        }
        $user['name'] = $name;
        $this->userManager->updateUser($user);
        $this->session['flash'] = 'プロフィールを変更しました。';

        return $response->withRedirect($this->router->pathFor('settings'));
    }

    public function account(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();
        if (!$user) {
            return $response->withRedirect($this->router->pathFor('home'));
        }

        return $this->view->render($response, 'settings/account.html.twig', [
            'user' => $user,
        ]);
    }

    public function deleteAccount(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();
        $this->userManager->deleteUser($user);
        $this->authService->signOut();
        $this->session['flash'] = 'アカウントを削除しました。';

        return $response->withRedirect($this->router->pathFor('home'));
    }
}