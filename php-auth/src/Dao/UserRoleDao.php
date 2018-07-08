<?php

namespace Dao;


class UserRoleDao
{

    /** @var \PDO */
    private $pdo;


    /**
     * UserRoleDao constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $userId
     * @return array
     */
    public function findByUserId($userId)
    {
        $stmt = $this->pdo->prepare('select trim(role) as role from users_roles where user_id = :user_id');
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        $roles = array_map(function ($e) {
            return $e['role'];
        }, $stmt->fetchAll());
        $stmt->closeCursor();

        return $roles;
    }

    /**
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function add(int $userId, string $role)
    {
        $stmt = $this->pdo->prepare('insert into users_roles(user_id, role) values(:userId, :role)');
        $stmt->bindValue('userId', $userId);
        $stmt->bindValue('role', $role);
        $n = $stmt->execute();
        $stmt->closeCursor();
        return $n;
    }
}