<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

$app = new \Slim\App(require __DIR__ . '/../src/settings.php');

require __DIR__ . '/dependencies.php';

require __DIR__ . '/routes.php';

$app->get('/', function (Request $request, Response $response) {
    $session = $this->get('session');

    $name = null;
    if (isset($session['name'])) {
        $name = $session['name'];
    }
    $roles = null;
    if (isset($session['roles'])) {
        $roles = $session['roles'];
    }

    return $this->view->render($response, 'index.html.twig', [
        'name' => $name,
        'roles' => $roles,
    ]);
});

return $app;
