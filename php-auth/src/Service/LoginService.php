<?php

namespace Service;

use Dao\UserDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;

/**
 * Class LoginService
 * @package Service
 */
class LoginService
{

    /** @var UserDao */
    private $userDao;

    /** @var UserRoleDao */
    private $userRoleDao;

    /** @var UserSessionDao */
    private $userSessionDao;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    public function __construct(UserDao $userDao, UserRoleDao $userRoleDao, UserSessionDao $userSessionDao, string $clientId, string $clientSecret)
    {
        $this->userDao = $userDao;
        $this->userRoleDao = $userRoleDao;
        $this->userSessionDao = $userSessionDao;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function getAuthUrl()
    {
        $query = http_build_query(['client_id' => $this->clientId, 'scope' => 'read:user']);
        $url = 'https://github.com/login/oauth/authorize?' . $query;

        return $url;
    }

    public function fetchAccessToken($code)
    {
        list($str, $httpStatus) = http_post('https://github.com/login/oauth/access_token', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
        ]);

        if ($httpStatus !== 200) {
            error_log('Failed to fetch access token.');
            return false;
        }

        parse_str($str, $data);
        if (!isset($data['access_token'])) {
            return false;
        }

        return $data['access_token'];
    }

    public function fetchUserInfo(string $accessToken)
    {
        list($str, $status) = http_get('https://api.github.com/user', ['access_token' => $accessToken]);
        if ($status !== 200 || !($data = json_decode($str, true))) {
            return false;
        }

        $user = [];
        foreach (['id', 'login', 'name'] as $key) {
            $user[$key] = $data[$key];
        }
        $user['user_id'] = $user['id'];

        return $user;
    }

    public function loadUser($user)
    {
        if (!$this->userDao->update($user)) {
            // データベースに格納失敗
            return false;
        }

        $user['session_id'] = session_id();
        if (!$this->userSessionDao->update($user)) {
            return false;
        }
        unset($user['session_id']);

        $_SESSION['user'] = $user;
        $this->loadRolesByUserId($user['user_id']);

        return true;
    }

    public function loadRolesByUserId($userId)
    {
        $userRoles = $this->userRoleDao->findByUserId(intval($userId));
        $roles = array_map(function ($e) {
            return $e['role'];
        }, $userRoles);


        $_SESSION['roles'] = $roles;

        return true;
    }
}


