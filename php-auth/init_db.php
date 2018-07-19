<?php

sleep(3);

/** @var \Slim\App $app */
$app = require_once __DIR__ . '/src/app.php';
$c = $app->getContainer();

/** @var \PDO $pdo */
$pdo = $c['pdo'];
$sql = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($sql);

$githubOwnerId = 1635983;

/** @var \Dao\UserProviderDao $userProviderDao */
$userProviderDao = $c['userProviderDao'];

$userProvider = $userProviderDao->findOneByProviderIdAndOwnerId(
    \Util\Providers::GITHUB,
    $githubOwnerId
);

if ($userProvider === null) {
    /**
     * @var \Manager\UserManager $userManager
     * @var \Dao\UserRoleDao $userRoleDao
     */
    $userManager = $c['userManager'];
    $userRoleDao = $c['userRoleDao'];

    $user = $userManager->createUser();

    $userProviderDao->create([
        'user_id' => $user['user_id'],
        'provider_id' => \Util\Providers::GITHUB,
        'owner_id' => $githubOwnerId,
    ]);

    $userRoleDao->update([
        'user_id' => $user['user_id'],
        'role' => 'ADMIN',
    ]);
}
