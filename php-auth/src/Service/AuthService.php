<?php

namespace Service;

use Dao\UserSessionDao;
use Manager\UserManager;
use Psr\Log\LoggerInterface;

/**
 * Class AuthService
 * @package Service
 */
class AuthService
{
    /** @var UserManager */
    private $userManager;

    /** @var UserSessionDao */
    private $userSessionDao;

    /** @var \Session */
    private $session;

    /** @var string */
    private $clientId;

    /** @var string */
    private $clientSecret;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        UserManager $userManager,
        UserSessionDao $userSessionDao,
        \Session $session,
        string $clientId,
        string $clientSecret,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
        $this->userSessionDao = $userSessionDao;
        $this->session = $session;
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

    public function signUpByGithub(string $accessToken): bool
    {
        if (!$userInfo = $this->fetchUserInfo($accessToken)) {
            return false;
        }

        // ログイン状態の判定
        $user = null;
        if (isset($this->session['user_id'])) {
            if (!$user = $this->userManager->getUserByUserId($this->session['user_id'])) {
                $this->signout();
                return false;
            }
        }

        try {
            $this->userSessionDao->transaction(function () use ($user, $userInfo) {
                if (!$user) {
                    $user = $this->userManager->getUserByGithubId($userInfo['id']);
                }
                if (!$user) {
                    $user = $this->userManager->createUser($userInfo['login']);
                }

                if ($user['name'] === null) {
                    $user['name'] = $userInfo['login'];
                    $this->userManager->updateUser($user);
                }
                $userId = $user['user_id'];
                $userInfo['user_id'] = $user['user_id'];
                $this->userManager->getUserGithubDao()->update($userInfo);

                $this->signIn($userId);
            });

            return true;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    public function signIn($userId): bool
    {
        if (!$user = $this->userManager->getUserByUserId($userId)) {
            return false;
        }

        $this->session['user_id'] = $user['user_id'];
        $this->session['name'] = $user['name'];
        $this->session['roles'] = $user['roles'];
        $this->userSessionDao->update([
            'user_id' => $userId,
            'session_id' => session_id(),
        ]);

        return true;
    }

    public function signOut(): bool
    {
        unset(
            $this->session['user_id'],
            $this->session['name'],
            $this->session['roles']
        );

        return true;
    }
}
