<?php

namespace Script;

require_once __DIR__ . '/../app.php';

class Main
{

    /** @return \PDO */
    private static function getPDO()
    {
        $app = createApp(false);
        $container = $app->getContainer();
        $settings = $container->get('settings')['pdo'];
        $dsn = $settings['dsn'];
        $start = mb_strpos($dsn, 'dbname=');
        $stop = mb_strpos($dsn, ';', $start + 1);
        $newDsn = mb_substr($dsn, 0, $start);
        if ($stop) {
            $newDsn .= mb_substr($dsn, $stop);
        }
        var_dump($newDsn);

        return new \PDO($newDsn, $settings['username'], $settings['password']);
    }

    public static function setupDB()
    {
        $pdo = self::getPDO();
        $sql = file_get_contents(__DIR__ . '/../../resouces/setup.sql');
        sleep(3);
        $pdo->exec($sql);
    }

    public static function dropDB()
    {
        $pdo = self::getPDO();
        $sql = file_get_contents(__DIR__ . '/../../resouces/drop.sql');
        sleep(3);
        $pdo->exec($sql);
    }

}
