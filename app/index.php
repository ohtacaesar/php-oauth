<?php

$sessionStartOptions = [];

foreach(['cookie_domain', 'cookie_secure'] as $key) {
    $KEY = 'SESSION_' . strtoupper($key);
    if (isset($_ENV[$KEY])) {
        $sessionStartOptions[$key] = $_ENV[$KEY];
    }
}

