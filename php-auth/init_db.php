<?php

require_once __DIR__ . '/vendor/autoload.php';

sleep(3);

$config = yaml_parse_file(__DIR__ . '/config.yml');

$pdo = new PDO(
    $config['pdo']['dsn'],
    $config['pdo']['username'],
    $config['pdo']['passwd']
);

$sql = file_get_contents('schema.sql');

$pdo->exec($sql);


$dao = new \Dao\UserRoleDao($pdo);

$userId = 1635983;

if (!$dao->findByUserId($userId)) {
    $sql = "insert into users_roles(user_id, role) values (:user_id, 'ADMIN')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue('user_id', $userId);
    $stmt->execute();
    $stmt->closeCursor();
}
