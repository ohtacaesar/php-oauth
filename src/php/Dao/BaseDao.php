<?php

namespace Dao;

class BaseDao
{
    /** @var \PDO */
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * @param callable $callback
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        $this->pdo->beginTransaction();
        try {
            $callback();
            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
