<?php

namespace Dao;

/**
 * Class UserDao
 * @package Dao
 */
class UserGithubDao extends BaseDao
{
    public function create(string $userId, string $id)
    {
        $stmt = $this->pdo->prepare("insert into user_github(user_id, id) values(:user_id, :id)");
        return $stmt->execute(['user_id' => $userId, 'id' => $id]);
    }

    public function update(array $userGithub)
    {
        $sql = <<<EOS
insert into user_github(user_id, id, login, name) values (:userId, :id, :login, :name)
    on conflict
    on constraint user_github_pkey
    do update set login = :login, name = :name
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('userId', $userGithub['user_id']);
        $stmt->bindValue('id', $userGithub['id']);
        $stmt->bindValue('login', $userGithub['login']);
        $stmt->bindValue('name', $userGithub['name']);
        $val = $stmt->execute();
        $stmt->closeCursor();

        return $val;
    }

    public function findOneByUserId(string $userId)
    {
        $stmt = $this->pdo->prepare('select * from user_github where user_id = :userId');
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        if ($rows = $stmt->fetchAll()) {
            return $rows[0];
        }
        return null;
    }

    public function findOneById(int $id)
    {
        $stmt = $this->pdo->prepare('select * from user_github where id = :id');
        $stmt->bindValue('id', $id);
        $stmt->execute();
        if ($users = $stmt->fetchAll()) {
            return $users[0];
        }
        return null;
    }
}
