<?php

sleep(3);

require_once __DIR__ . '/src/php/app.php';

$app = createApp(false);

$c = $app->getContainer();

/** @var \PDO $pdo */
$pdo = $c['pdo'];
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);
