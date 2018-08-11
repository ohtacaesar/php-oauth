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

        if ($rd = $request->getParam('rd', null)) {
            $rd = filter_var($rd, FILTER_VALIDATE_URL);
            $rd = filter_var($rd, FILTER_SANITIZE_URL);
            if ($rd) {
                $this->session['rd'] = $rd;
            }
        }

        return $this->view->render($response, 'index.html.twig', [
            'user' => $user,
        ]);
    }

    public function userUpdate(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();

        $name = $request->getParam('name', null);

        if ($name === null or mb_strlen($name) <= 1 or 255 < mb_strlen($name)) {
            $this->session['message'] = '401: パラメータが正しくありません';
            return $response->withRedirect($this->router->pathFor('home'));
        }
        $user['name'] = $name;
        $this->userManager->updateUser($user);

        return $response->withRedirect($this->router->pathFor('home'));
    }

    public function auth(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();

        // 認証
        if ($user === null) {
            return $response->withStatus(401);
        }

        $roles = $request->getServerParam('HTTP_ROLE');
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
}
