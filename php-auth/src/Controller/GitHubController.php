<?php

namespace Controller;

use Dao\UserDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GithubController
 * @package Controller
 */
class GitHubController extends BaseController
{
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
            $user = fetchUserInfo($_SESSION['access_token']);
            if ($user) {
                return $response->withRedirect($rd);
            }
        }

        $_SESSION['rd'] = $rd;

        $query = http_build_query(['client_id' => CLIENT_ID, 'scope' => 'read:user']);
        $url = 'https://github.com/login/oauth/authorize?' . $query;

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

        list($str, $httpStatus) = http_post('https://github.com/login/oauth/access_token', [
            'client_id' => CLIENT_ID,
            'client_secret' => CLIENT_SECRET,
            'code' => $code,
        ]);

        if ($httpStatus !== 200) {
            error_log('Failed to fetch access token.');
            return $response->withRedirect('/');
        }

        parse_str($str, $data);
        $accessToken = $data['access_token'];

        $user = fetchUserInfo($accessToken);
        if (!$user) {
            error_log('ユーザー情報の取得に失敗.');
            return $response->withRedirect('/');
        }

        $userRoleDao = new UserRoleDao($this->pdo);
        $userRoles = $userRoleDao->findByUserId($user['user_id']);
        $roles = array_map(function ($e) {
            return $e['role'];
        }, $userRoles);

        $_SESSION['access_token'] = $accessToken;
        $_SESSION['user'] = $user;
        $_SESSION['roles'] = $roles;

        $userDao = new UserDao($this->pdo);
        if (!$userDao->update($user)) {
            error_log('ユーザー情報のセーブに失敗.');
        }

        $userSessionDao = new UserSessionDao($this->pdo);
        $user['session_id'] = session_id();
        $userSessionDao->update($user);

        return $response->withRedirect($rd);
    }
}

