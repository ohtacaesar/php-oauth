<?php

$sessionStartOptions = [];

foreach(['cookie_domain', 'cookie_secure'] as $key) {
    $KEY = 'SESSION_' . strtoupper($key);
    if (isset($_ENV[$KEY])) {
        $sessionStartOptions[$key] = $_ENV[$KEY];
    }
}

$redis = new Redis();
$redis->connect('redis', 6379);
var_dump($redis->get('test'));
$redis->close();
