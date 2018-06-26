<?php

$sessionStartOptions = [];

foreach (['cookie_domain', 'cookie_secure', 'save_handler', 'save_path', 'serialize_handler'] as $key) {
    $KEY = 'SESSION_' . strtoupper($key);
    if (isset($_ENV[$KEY])) {
        $sessionStartOptions[$key] = $_ENV[$KEY];
    }
}

session_start($sessionStartOptions);

?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>SESSION DUMP</title>
</head>

<body>
<pre><?php var_dump($_SESSION) ?></pre>
</body>

</html>
