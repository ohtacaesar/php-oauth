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
        $stmt = $this->pdo->prepare('select user_id, trim(role) as role from user_roles where user_id = :userId');
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();

        return $rows;
    }

    /**
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function create(int $userId, string $role)
    {
        $stmt = $this->pdo->prepare('insert into user_roles(user_id, role) values(:userId, :role)');
        $stmt->bindValue('userId', $userId);
        $stmt->bindValue('role', $role);
        $r = $stmt->execute();
        $stmt->closeCursor();

        return $r;
    }

    /**
     * @param array $userRole
     * @return bool
     */
    public function delete(array $userRole)
    {
        if (isset($userRole['user_id']) && isset($userRole['role'])) {
            return $this->deleteByUserIdAndRole($userRole['user_id'], $userRole['role']);
        }

        return false;
    }

    /**
     * @param int $userId
     * @return bool
     */
    public function deleteByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('delete from user_roles where user_id = :userId');
        $stmt->bindValue('userId', $userId);
        $r = $stmt->execute();
        $stmt->closeCursor();

        return $r;
    }

    /**
     * @param int $userId
     * @param string $role
     * @return bool
     */
    public function deleteByUserIdAndRole(int $userId, string $role)
    {
        $stmt = $this->pdo->prepare('delete from user_roles where user_id = :userId and role = :role');
        $stmt->bindValue('userId', $userId);
        $stmt->bindValue('role', $role);
        $r = $stmt->execute();
        $stmt->closeCursor();

        return $r;
    }
}