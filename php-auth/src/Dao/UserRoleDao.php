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
    public function __construct($pdo)
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

}