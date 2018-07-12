<?php

namespace Controller;

use Dao\UserGithubDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Service\LoginService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GithubController
 * @package Controller
 */
class GitHubController extends BaseController
{
    /** @var LoginService */
    private $loginService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->loginService = $container->get('loginService');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function start(Request $request, Response $response)
    {
        $rd = $request->getParam('rd');
        $rd = filter_var($rd, FILTER_VALIDATE_URL);
        $rd = filter_var($rd, FILTER_SANITIZE_URL);

        if (!$rd) {
            return $response->withStatus(400);
        }

        if (isset($_SESSION['access_token'])) {
            $user = $this->loginService->fetchUserInfo($_SESSION['access_token']);
            if (!$user) {
                unset($_SESSION['access_token']);
            } else {
                if ($this->loginService->loadUser($user)) {
                    return $response->withRedirect($rd);
                } else {
                    // エラーページに飛ばす, DB等に問題あり
                    return $response->withRedirect('/');
                }
            }
        }

        $_SESSION['rd'] = $rd;
        $url = $this->loginService->getAuthUrl();
        return $response->withRedirect($url);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function callback(Request $request, Response $response)
    {
        if (($code = $request->getQueryParam('code')) === null) {
            return $response->withStatus(400);
        }

        if (!isset($_SESSION['rd'])) {
            error_log('redirect url is not set.');
            return $response->withStatus(400);
        }

        $rd = $_SESSION['rd'];
        unset($_SESSION['rd']);

        $accessToken = $this->loginService->fetchAccessToken($code);
        $githubUser = $this->loginService->fetchUserInfo($accessToken);
        if (!$githubUser) {
            return $response->withRedirect('/');
        }
        $_SESSION['access_token'] = $accessToken;

        if ($this->loginService->loadUser($githubUser)) {
            return $response->withRedirect($rd);
        } else {
            // エラーページに飛ばす, DB等に問題あり
            return $response->withRedirect('/');
        }
    }
}

