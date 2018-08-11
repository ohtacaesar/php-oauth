<?php

namespace Manager;

use Dao\UserDao;
use Dao\UserProviderDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
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

    public function getUserByUserId(string $userId): ?array
    {
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

        $user['user_session'] = $this->userSessionDao->findOneByUserId($userId);
        $user['session_id'] = null;
        if ($user['user_session']) {
            $user['session_id'] = $user['user_session']['session_id'];
        }

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
