<?php

namespace Dao;

class UserRoleDao extends BaseDao
{
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
     * @param array $userRole
     * @return bool
     */
    public function update(array $userRole)
    {
        $sql = <<<EOS
insert into user_roles(user_id, role) values (:userId, :role)
    on conflict
    on constraint user_roles_pkey
    do update set user_id = :userId, role = :role
EOS;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('userId', $userRole['user_id']);
        $stmt->bindValue('role', $userRole['role']);
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
