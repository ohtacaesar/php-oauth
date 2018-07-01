<?php

namespace Dao;

/**
 * Class UserDao
 * @package Dao
 */
class UserDao
{
    /** @var \PDO */
    private $pdo;

    /**
     * UserDao constructor.
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param array $user
     * @return bool
     */
    public function update(array $user)
    {
        $sql = <<<EOS
insert into users(login, id, name) values (:login, :id, :name)
    on conflict
    on constraint users_pkey
    do update set id = :id, login = :login, name = :name
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('login', $user['login']);
        $stmt->bindValue('id', $user['id']);
        $stmt->bindValue('name', $user['name']);
        $val = $stmt->execute();
        $stmt->closeCursor();

        return $val;
    }

    /**
     * @param int $id
     * @return array|false
     */
    public function getById(int $id)
    {
        $stmt = $this->pdo->prepare('select * from users where id = :id');
        $stmt->bindValue('id', $_SESSION['id']);
        $stmt->execute();
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $user;
    }

    /**
     * @return array|false
     */
    public function getAll()
    {
        $stmt = $this->pdo->prepare('select * from users');
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $users;
    }
}

