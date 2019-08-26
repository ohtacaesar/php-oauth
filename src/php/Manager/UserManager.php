<?php

namespace Manager;

use Dao\UserDao;
use Dao\UserProviderDao;
use Dao\UserRoleDao;
use Monolog\Logger;
use Psr\Log\NullLogger;
use Util\Providers;

class UserManager
{
    /** @var Logger */
    private $logger;

    /** @var UserDao */
    private $userDao;

    /** @var UserRoleDao */
    private $userRoleDao;

    /** @var UserProviderDao */
    private $userProviderDao;

    public function __construct(
        UserDao $userDao,
        UserRoleDao $userRoleDao,
        UserProviderDao $userProviderDao
    )
    {
        $this->userDao = $userDao;
        $this->userRoleDao = $userRoleDao;
        $this->userProviderDao = $userProviderDao;
        $this->logger = new NullLogger();
    }

    public function getUserDao(): UserDao
    {
        return $this->userDao;
    }

    public function getUserProviderDao(): UserProviderDao
    {
        return $this->userProviderDao;
    }

    public function getUserRoleDao(): UserRoleDao
    {
        return $this->userRoleDao;
    }

    public function generateUserId(): string
    {
        $userId = bin2hex(random_bytes(10));
        while ($user = $this->userDao->findOneByUserId($userId)) {
            $userId = bin2hex(random_bytes(10));
        }

        return $userId;
    }

    public function generateSigninToken(): string
    {
        $signinToken = bin2hex(random_bytes(20));
        while ($user = $this->userDao->findOneBySigninToken($signinToken)) {
            $signinToken = bin2hex(random_bytes(20));
        }

        return $signinToken;
    }

    public function getUserByUserId(?string $userId): ?array
    {
        if ($userId === null) {
            return null;
        }

        if (!$user = $this->userDao->findOneByUserId($userId)) {
            return null;
        }

        $user['user_roles'] = $this->userRoleDao->findByUserId($userId);
        $user['roles'] = array_column($user['user_roles'], 'role');

        $user['user_providers'] = $this->userProviderDao->findByUserId($userId);
        $user['provider_ids'] = array_column($user['user_providers'], 'provider_id');

        $providers = [];
        foreach ($user['provider_ids'] as $id) {
            $providers[] = Providers::name($id);
        }
        $user['providers'] = array_filter($providers);

        return $user;
    }

    public function getUserBySigninToken(?string $signinToken): ?array
    {
        if ($signinToken == null) {
            return null;
        }

        return $this->userDao->findOneBySigninToken($signinToken);
    }

    public function getUserByProviderIdAndOwnerId(int $providerId, string $ownerId): ?array
    {
        $tmp = $this->userProviderDao->findOneByProviderIdAndOwnerId($providerId, $ownerId);
        if (!$tmp) {
            return null;
        }

        return $this->getUserByUserId($tmp['user_id']);
    }

    public function createUser(string $name = null): array
    {
        $user = ['user_id' => $this->generateUserId(), 'name' => $name];
        $this->userDao->insert($user);

        return $user;
    }

    public function updateUser(array $user)
    {
        $user = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
            'signin_token' => $user['signin_token'] ?? null,
        ];

        $this->userDao->update($user);
    }

    public function deleteUser(array $user)
    {
        $this->userDao->transaction(function () use ($user) {
            $userId = $user['user_id'];
            $this->userRoleDao->deleteByUserId($userId);
            $this->userProviderDao->deleteByUserId($userId);
            $this->userDao->delete($user);
        });
    }

    public function addRole(array $user, string $role)
    {
        $userRole = ['user_id' => $user['user_id'], 'role' => $role];
        $this->userRoleDao->update($userRole);
    }

    /**
     * @param array $user
     * @param int $providerId
     * @param $ownerId
     */
    public function addProvider(array $user, int $providerId, $ownerId)
    {
        $userProvider = [
            'user_id' => $user['user_id'],
            'provider_id' => $providerId,
            'owner_id' => $ownerId,
        ];

        $this->userProviderDao->create($userProvider);
    }
}
