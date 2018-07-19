<?php

namespace Service;

use Dao\UserDao;
use Dao\UserProviderDao;
use Dao\UserSessionDao;
use Manager\UserManager;
use Psr\Log\LoggerInterface;
use Util\Providers;

/**
 * Class AuthService
 * @package Service
 */
class AuthService
{
    /** @var UserManager */
    private $userManager;

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
        \Session $session,
        string $clientId,
        string $clientSecret,
        LoggerInterface $logger
    ) {
        $this->userManager = $userManager;
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
        $currentUser = null;
        if (isset($this->session['user_id'])) {
            if (!$currentUser = $this->userManager->getUserByUserId($this->session['user_id'])) {
                $this->signout();
                return false;
            }
        }

        try {
            $this->userManager->getUserDao()->transaction(function () use ($currentUser, $userInfo) {
                $userProvider = $this->userManager->getUserProviderDao()->findOneByProviderIdAndOwnerId(
                    Providers::GITHUB,
                    $userInfo['id']
                );

                $user = null;
                if ($userProvider) {
                    if ($user = $this->userManager->getUserDao()->findOneByUserId($userProvider['user_id'])) {
                        if ($currentUser['user_id'] !== $user['user_id']) {
                            $this->logger->error(sprintf(
                                'current_user:%s, user:%s',
                                $currentUser['user_id'],
                                $user['user_id']
                            ));
                        }
                    }
                }
                if (!$user) {
                    $user = $this->userManager->createUser($userInfo['login']);
                }

                if ($user['name'] === null) {
                    $user['name'] = $userInfo['login'];
                    $this->userManager->updateUser($user);
                }
                $userInfo['user_id'] = $user['user_id'];

                if (!$userProvider) {
                    $this->userManager->getUserProviderDao()->create([
                        'user_id' => $user['user_id'],
                        'provider_id' => Providers::GITHUB,
                        'owner_id' => $userInfo['id'],
                    ]);
                }

                $this->signIn($user['user_id']);
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
        $this->userManager->getUserSessionDao()->update([
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
