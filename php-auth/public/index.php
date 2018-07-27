<?php

require_once __DIR__ . '/../src/app.php';

$app = createApp();

$uri = $app->getContainer()->get('uri');
$host = $uri->getHost();
$cookieDomain = explode('.', $host);

if (count($cookieDomain) > 2) {
    array_shift($cookieDomain);
    $cookieDomain = "." . join(".", $cookieDomain);
} else {
    $cookieDomain = $host;
}

session_start([
    'cookie_domain' => $cookieDomain
]);

$app->run();
