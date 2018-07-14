<?php

namespace Service;

use Dao\UserDao;
use Dao\UserGithubDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Psr\Log\LoggerInterface;

/**
 * Class LoginService
 * @package Service
 */
class LoginService
{

    /** @var UserDao */
    private $userDao;

    /** @var UserGithubDao */
    private $userGithubDao;

    /** @var UserRoleDao */
    private $userRoleDao;

    /** @var UserSessionDao */
    private $userSessionDao;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UserDao $userDao,
        UserRoleDao $userRoleDao,
        UserSessionDao $userSessionDao,
        UserGithubDao $userGithubDao,
        string $clientId,
        string $clientSecret,
        LoggerInterface $logger
    )
    {
        $this->userDao = $userDao;
        $this->userRoleDao = $userRoleDao;
        $this->userSessionDao = $userSessionDao;
        $this->userGithubDao = $userGithubDao;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->logger = $logger;
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
            $this->logger->error('Failed to fetch access token.');
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

        return $data;
    }

    public function loadUser($data)
    {
        // TODO: Transaction
        // ログイン状態の判定
        $user = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $user = $this->userDao->findOneByUserId($userId);
            if (!$user) {
                // セッションに問題あるよ例外
                session_destroy();
                return false;
            }
        }

        // GitHubアカウントの確認
        if (!$user and $ghUser = $this->userGithubDao->findOneById($data['id'])) {
            $user = $this->userDao->findOneByUserId($ghUser['user_id']);
            if (!$user) {
                $this->logger->warning(sprintf('User not found: user_id=%s, github_id=%s', $ghUser['user_id'], $ghUser['id']));
            }
        }

        try {
            $this->userDao->transaction(function () use ($user, $data) {
                if (!$user) {
                    $userId = bin2hex(random_bytes(10));
                    while ($user = $this->userDao->findOneByUserId($userId)) {
                        $userId = bin2hex(random_bytes(10));
                    }
                    $user = ['user_id' => $userId, 'name' => $data['login']];
                    $this->logger->info('create new user: ' . json_encode($user));
                    $this->userDao->create($user);
                }

                $data['user_id'] = $user['user_id'];
                $this->userGithubDao->update($data);

                $user['session_id'] = session_id();
                $this->userSessionDao->update($user);
                unset($user['session_id']);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user'] = $user;

                $this->loadRolesByUserId($user['user_id']);
            });
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }

        return true;
    }

    public function loadRolesByUserId($userId)
    {
        $userRoles = $this->userRoleDao->findByUserId($userId);
        $roles = array_map(function ($e) {
            return $e['role'];
        }, $userRoles);

        $_SESSION['roles'] = $roles;

        return true;
    }
}


