<?php

namespace Controller;

use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController extends BaseController
{
    /** @var AuthService */
    private $authService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container['authService'];
    }

    public function auth(Request $request, Response $response)
    {
        // 認証
        if (!isset($this->session['roles'])) {
            return $response->withStatus(401);
        }

        $request->getServerParam("HTTP_ROLE");

        // 認可
        $role = $request->getServerParam('HTTP_ROLE');
        if ($role && !in_array($role, $this->session['roles'], true)) {
            return $response->withStatus(403);
        }

        return $response->withStatus(200);
    }


    public function signOut(Request $request, Response $response)
    {
        $rd = $request->getParam('rd');
        $rd = filter_var($rd, FILTER_VALIDATE_URL);
        $rd = filter_var($rd, FILTER_SANITIZE_URL);

        if (!$rd) {
            return $response->withStatus(400);
        }

        $this->authService->signOut();

        return $response->withRedirect($rd);
    }
}
