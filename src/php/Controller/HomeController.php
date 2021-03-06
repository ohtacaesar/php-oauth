<?php

namespace Controller;

use Psr\Http\Message\ResponseInterface;
use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends BaseController
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
        $user = $this->getLoginUser();

        if ($redirectUrl = $request->getParam('rd', null)) {
            if ($this->validateRedirectUrl($request, $redirectUrl)) {
                $this->session['rd'] = $redirectUrl;
            } else {
                return $response->withStatus(400);
            }
        }

        return $this->view->render($response, 'index.html.twig', [
            'user' => $user,
        ]);
    }


    public function auth(Request $request, Response $response): ResponseInterface
    {
        if (!$request->getServerParam('HTTP_X_AUTH_ENABLE')) {
            $this->logger->error("X-AUTH-ENABLE is not set");
            return $response->withStatus(400);
        }
        $user = $this->getLoginUser();

        // 認証
        if ($user === null) {
            return $response->withStatus(401);
        }

        $roles = $request->getServerParam('HTTP_X_AUTH_ROLES');
        $this->logger->info($roles);
        if (!$roles) {
            $response->getBody()->write($user['user_id']);
            return $response->withStatus(200);
        }

        // 認可
        foreach (explode(',', $roles) as $role) {
            if (in_array($role, $user['roles'], true)) {
                $response->getBody()->write($user['user_id']);
                return $response->withStatus(200);
            }
        }

        return $response->withStatus(403);
    }

    public function signOut(Request $request, Response $response): ResponseInterface
    {
        $rd = $request->getParam('rd');
        $rd = filter_var($rd, FILTER_VALIDATE_URL);
        $rd = filter_var($rd, FILTER_SANITIZE_URL);
        if (!$rd) {
            $rd = '/';
        }

        $this->authService->signOut();

        return $response->withRedirect($rd);
    }

    public function signinWithToken(Request $request, Response $response): ResponseInterface
    {
        $signinToken = $request->getParam('token');
        if (!$signinToken) {
            return $response->withStatus(400);
        }

        $user = $this->authService->signinWithToken($signinToken);
        if (!$user) {
            return $response->withStatus(400);
        }

        $this->session['flash'] = 'サインインしました。';
        return $response->withRedirect($this->router->pathFor('home'));
    }
}
