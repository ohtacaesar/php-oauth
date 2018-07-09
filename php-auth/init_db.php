<?php

sleep(5);

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/src/app.php';

$pdo = $app->getContainer()->get('pdo');
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);

$dao = new \Dao\UserRoleDao($pdo);
$userId = 1635983;
$dao->update([
    'user_id' => $userId,
    'role' => 'ADMIN',
]);
