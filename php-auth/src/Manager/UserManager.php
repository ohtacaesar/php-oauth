<?php

namespace Manager;

use Dao\UserDao;
use Dao\UserProviderDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Monolog\Logger;
use Psr\Log\NullLogger;

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

    /** @var UserSessionDao */
    private $userSessionDao;

    public function __construct(
        UserDao $userDao,
        UserRoleDao $userRoleDao,
        UserProviderDao $userProviderDao,
        UserSessionDao $userSessionDao
    ) {
        $this->userDao = $userDao;
        $this->userRoleDao = $userRoleDao;
        $this->userProviderDao = $userProviderDao;
        $this->userSessionDao = $userSessionDao;
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

    public function getUserSessionDao(): UserSessionDao
    {
        return $this->userSessionDao;
    }

    public function generateUserId(): string
    {
        $userId = bin2hex(random_bytes(10));
        while ($user = $this->userDao->findOneByUserId($userId)) {
            $userId = bin2hex(random_bytes(10));
        }

        return $userId;
    }

    public function getUserByUserId($userId)
    {
        $user = $this->userDao->findOneByUserId($userId);

        $userRoles = $this->userRoleDao->findByUserId($userId);
        $user['roles'] = array_map(function ($e) {
            return $e['role'];
        }, $userRoles);

        $user['provider_ids'] = array_map(function ($e) {
            return $e['provider_id'];
        }, $this->userProviderDao->findByUserId($userId));

        return $user;
    }

    public function getUserByProviderIdAndOwnerId(int $providerId, string $ownerId)
    {
        $tmp = $this->userProviderDao->findOneByProviderIdAndOwnerId($providerId, $ownerId);
        if (!$tmp) {
            return null;
        }

        if (!$user = $this->userDao->findOneByUserId($tmp['user_id'])) {
            return null;
        }

        return $user;
    }

    public function createUser(string $name = null): array
    {
        $user = ['user_id' => $this->generateUserId(), 'name' => $name];
        $this->userDao->create($user);

        return $user;
    }

    public function updateUser(array $user)
    {
        $user = [
            'user_id' => $user['user_id'],
            'name' => $user['name'],
        ];

        $this->userDao->update($user);
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
