<?php

namespace Controller;

use Manager\UserManager;
use Psr\Http\Message\ResponseInterface;
use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class HomeController extends BaseController
{
    /** @var AuthService */
    private $authService;

    /** @var UserManager */
    private $userManager;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container['authService'];
        $this->userManager = $container['userManager'];
    }

    private function getLoginUser(): ?array
    {
        if (!$userId = $this->session->get('user_id')) {
            return null;
        }

        return $this->userManager->getUserByUserId($userId);
    }

    public function home(Request $request, Response $response): ResponseInterface
    {
        $user = $this->getLoginUser();

        return $this->view->render($response, 'index.html.twig', [
            'user' => $user
        ]);
    }

    public function auth(Request $request, Response $response): ResponseInterface
    {
        // 認証
        if (($userRoles = $this->session->get('roles')) === null) {
            return $response->withStatus(401);
        }

        $roles = $request->getServerParam('HTTP_ROLE');
        if (!$roles) {
            return $response->withStatus(200);
        }

        // 認可
        foreach (explode(',', $roles) as $role) {
            if (in_array($role, $userRoles, true)) {
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
