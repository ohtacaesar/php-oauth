<?php

sleep(3);

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/src/app.php';

$pdo = $app->getContainer()->get('pdo');

$sql = file_get_contents(__DIR__ . '/schema.sql');

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
