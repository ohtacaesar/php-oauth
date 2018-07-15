<?php

namespace Manager;

use Dao\UserDao;
use Dao\UserGithubDao;
use Dao\UserRoleDao;
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

    /** @var UserGithubDao */
    private $userGithubDao;

    public function __construct(UserDao $userDao, UserRoleDao $userRoleDao, UserGithubDao $userGithubDao)
    {
        $this->userDao = $userDao;
        $this->userRoleDao = $userRoleDao;
        $this->userGithubDao = $userGithubDao;
        $this->logger = new NullLogger();
    }

    public function getUserGithubDao(): UserGithubDao
    {
        return $this->userGithubDao;
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

        return $user;
    }

    public function getUserByGithubId($id)
    {
        if (!$ghUser = $this->userGithubDao->findOneById($id)) {
            return null;
        }

        if (!$user = $this->userDao->findOneByUserId($ghUser['user_id'])) {
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

    public function addRolesByGithubId($id, array $roles)
    {
        try {
            $this->userDao->transaction(function () use ($id, $roles) {
                if ($ghUser = $this->userGithubDao->findOneById($id)) {
                    $userId = $ghUser['user_id'];
                    if (!$user = $this->userDao->findOneByUserId($userId)) {
                        return false;
                    }
                } else {
                    $user = $this->createUser();
                    $this->userGithubDao->create($user['user_id'], $id);
                }

                foreach ($roles as $role) {
                    $this->userRoleDao->update(['user_id' => $user['user_id'], 'role' => $role]);
                }
                return true;
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


}

