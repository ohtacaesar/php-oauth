<?php

namespace Dao;


class UserSessionDao extends BaseDao
{
    public function findOneByUserId($userId)
    {
        $stmt = $this->pdo->prepare("select * from user_sessions where user_id = :userId");
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $stmt->closeCursor();
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

    public function update(array $userSession)
    {
        $sql = <<<EOS
insert into user_sessions(user_id, session_id) values (:userId, :sessionId)
    on conflict
    on constraint user_sessions_pkey
    do update set session_id = :sessionId
EOS;

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue('userId', $userSession['user_id']);
        $stmt->bindValue('sessionId', $userSession['session_id']);
        $r = $stmt->execute();
        $stmt->closeCursor();

        return $r;
    }
}