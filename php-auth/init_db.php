<?php

sleep(3);

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/src/app.php';
$c = $app->getContainer();

/** @var \PDO $pdo */
$pdo = $c['pdo'];
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);

/** @var \Manager\UserManager $userManager */
$userManager = $c['userManager'];
$userManager->addRolesByGithubId(1635983, ['ADMIN']);
