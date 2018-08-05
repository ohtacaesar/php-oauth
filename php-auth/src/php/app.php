<?php

require_once __DIR__ . '/../../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

/**
 * @param bool $withMiddleware
 * @return \Slim\App
 */
function createApp(bool $withMiddleware = true): \Slim\App
{
    $app = new \Slim\App(require __DIR__ . '/settings.php');

    require __DIR__ . '/dependencies.php';

    if ($withMiddleware) {
        require __DIR__ . '/middleware.php';
    }

    require __DIR__ . '/routes.php';

    return $app;
}
