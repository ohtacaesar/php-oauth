<?php

namespace Controller;

use Dao\UserGithubDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Service\AuthService;
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
    /** @var AuthService */
    private $authService;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container->get('authService');
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
            $this->logger->info($rd);
            return $response->withStatus(400);
        }

        if (isset($this->session['access_token'])) {
            if ($this->authService->signUpByGithub($this->session['access_token'])) {
                return $response->withRedirect($rd);
            } else {
                unset($this->session['access_token']);
                return $response->withRedirect('/');
            }
        }

        $this->session['rd'] = $rd;
        $url = $this->authService->getAuthUrl();
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

        if (!isset($this->session['rd'])) {
            error_log('redirect url is not set.');
            return $response->withStatus(400);
        }

        $rd = $this->session['rd'];
        unset($this->session['rd']);

        $accessToken = $this->authService->fetchAccessToken($code);
        if ($this->authService->signUpByGithub($accessToken)) {
            $this->session['access_token'] = $accessToken;
            return $response->withRedirect($rd);
        } else {
            // エラーページに飛ばす, DB等に問題あり
            return $response->withRedirect('/');
        }
    }
}
