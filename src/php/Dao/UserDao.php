<?php

namespace Dao;

class UserDao extends BaseDao
{
    public function count()
    {
        return $this->pdo->query("select count(1) as n from users")->fetchAll()[0]['n'];
    }

    public function findAll()
    {
        return $this->pdo->query("select * from users")->fetchAll();
    }

    public function findOneByUserId(string $userId)
    {
        $stmt = $this->pdo->prepare("select * from users where user_id = :user_id");
        $stmt->bindValue('user_id', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows[0] ?? null;
    }

    public function findOneBySigninToken(string $signinToken)
    {
        $stmt = $this->pdo->prepare('select * from users where signin_token = :signin_token');
        $stmt->bindValue('signin_token', $signinToken);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        return $rows[0] ?? null;
    }

    /**
     * @param array $user
     * @return bool
     * @throws \PDOException
     */
    public function insert(array $user): bool
    {
        $stmt = $this->pdo->prepare("insert into users(user_id, name) values (:user_id, :name)");

        return $stmt->execute($user);
    }

    const UPDATE = <<<EOS
insert into users(user_id, name, signin_token) values (:user_id, :name, :signin_token)
    on duplicate key update name = :name, signin_token = :signin_token
EOS;

    public function update(array $user): bool
    {
        $stmt = $this->pdo->prepare(static::UPDATE);
        return $stmt->execute($user);
    }

    public function delete(array $user): bool
    {
        $stmt = $this->pdo->prepare('delete from users where user_id = :user_id');
        $stmt->bindValue('user_id', $user['user_id']);
        return $stmt->execute();
    }
}
