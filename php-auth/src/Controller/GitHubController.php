<?php

namespace Controller;

use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Util\Providers;

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
        $this->authService = $container['authService'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function start(Request $request, Response $response)
    {
        if ($rd = $request->getParam('rd')) {
            $rd = filter_var($rd, FILTER_VALIDATE_URL);
            $rd = filter_var($rd, FILTER_SANITIZE_URL);
            if ($rd) {
                $this->session['rd'] = $rd;
            }
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
        if (null === ($code = $request->getQueryParam('code'))) {
            return $response->withStatus(400);
        }

        $accessToken = $this->authService->fetchAccessToken($code);
        $userInfo = $this->authService->fetchUserInfo($accessToken);
        if ($this->authService->signUp(Providers::GITHUB, $userInfo['id'], $userInfo['login'])) {
            $this->session['access_token'] = $accessToken;
            return $response->withRedirect($this->session->getUnset('rd'));
        } else {
            // エラーページに飛ばす, DB等に問題あり
            return $response->withRedirect('/');
        }
    }
}
