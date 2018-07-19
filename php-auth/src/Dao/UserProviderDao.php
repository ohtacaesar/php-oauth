<?php

namespace Dao;

class UserProviderDao extends BaseDao
{
    const INSERT_SQL = <<<EOS
insert into user_providers(user_id, provider_id, owner_id)
values(:user_id, :provider_id, :owner_id)
EOS;

    public function create(array $data): bool
    {
        $stmt = $this->pdo->prepare(self::INSERT_SQL);
        return $stmt->execute($data);
    }

    public function findByUserId(string $userId)
    {
        $stmt = $this->pdo->prepare("select * from user_providers where user_id = :user_id");
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findOneByUserIdAndProvider(string $userId, int $providerId): array
    {
        $sql = 'select * from user_providers where user_id = :user_id and provider = :provider_id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':provider_id', $providerId);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if ($rows) {
            return $rows[0];
        }

        return null;
    }

    public function findOneByProviderIdAndOwnerId(int $provider_id, string $ownerId)
    {
        $sql = "select * from user_providers where provider_id = :provider_id and owner_id = :owner_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':provider_id', $provider_id);
        $stmt->bindValue(':owner_id', $ownerId);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if ($rows) {
            return $rows[0];
        }

        return null;
    }
}
