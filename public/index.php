<pre><?php

$sessionStartOptions = [];

foreach(['cookie_domain'] as $key) {
    $KEY = 'SESSION_' . strtoupper($key);
    if (isset($_ENV[$KEY])) {
        $sessionStartOptions[$key] = $_ENV[$KEY];
    }
}
