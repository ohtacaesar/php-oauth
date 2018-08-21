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

    public function create(array $user): bool
    {
        $stmt = $this->pdo->prepare("insert into users(user_id, name) values (:user_id, :name)");
        return $stmt->execute($user);
    }

    const UPDATE = <<<EOS
insert into users(user_id, name) values (:user_id, :name)
    on conflict
    on constraint users_pkey
    do update set name = :name
EOS;

    public function update(array $user): bool
    {
        $stmt = $this->pdo->prepare(static::UPDATE);
        return $stmt->execute($user);
    }
}
