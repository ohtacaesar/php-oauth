<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

$app = new \Slim\App(require __DIR__ . '/../src/settings.php');

require __DIR__ . '/dependencies.php';

require __DIR__ . '/middleware.php';

require __DIR__ . '/routes.php';

return $app;
