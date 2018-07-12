<?php

namespace Dao;

class UserDao
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll()
    {
        return $this->pdo->query("select * from users")->fetchAll();
    }

    public function findOneByUserId(string $userId)
    {
        $stmt = $this->pdo->prepare("select * from users where user_id = :userId");
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        if ($rows) {
            return $rows[0];
        } else {
            return null;
        }
    }

    public function update(array $user)
    {
        $sql = <<<EOS
insert into users(user_id, name) values (:userId, :name)
    on conflict
    on constraint users_pkey
    do update set name = :name
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('userId', $user['user_id']);
        $stmt->bindValue('name', $user['name']);
        $val = $stmt->execute();
        $stmt->closeCursor();

        return $val;
    }
}