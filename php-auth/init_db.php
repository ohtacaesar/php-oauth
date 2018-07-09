<?php

sleep(3);

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/src/app.php';

$pdo = $app->getContainer()->get('pdo');
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);

$dao = new \Dao\UserRoleDao($pdo);

$userId = 1635983;

$userRole = $dao->findByUserId($userId);
if (!$dao->findByUserId($userId)) {
    $dao = new \Dao\UserRoleDao($pdo);
    $dao->create($userId, 'ADMIN');
}
