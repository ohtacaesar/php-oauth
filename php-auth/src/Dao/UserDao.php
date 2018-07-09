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
insert into users(login, user_id, name) values (:login, :userId, :name)
    on conflict
    on constraint users_pkey
    do update set login = :login, name = :name
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('login', $user['login']);
        $stmt->bindValue('userId', $user['user_id']);
        $stmt->bindValue('name', $user['name']);
        $val = $stmt->execute();
        $stmt->closeCursor();

        return $val;
    }

    /**
     * @param int $userId
     * @return array|false
     */
    public function findOneByUserId(int $userId)
    {
        $stmt = $this->pdo->prepare('select * from users where user_id = :userId');
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $user = $stmt->fetch();
        $stmt->closeCursor();

        return $user;
    }

    /**
     * @param int $userId
     * @return mixed
     */
    public function getByUserId(int $userId)
    {
        return $this->findByUserId($userId);
    }

    /**
     * @return array|false
     */
    public function getAll()
    {
        $sql = <<<EOS
select
  a.*
, b.roles
from users as a

left join (
  select
    user_id
  , array_to_string(array_agg(trim(role)), ',') as roles
  from user_roles
  group by user_id
) as b
  on b.user_id = a.user_id

order by a.user_id
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $users;
    }
}

