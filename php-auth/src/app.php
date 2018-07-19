<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

$app = new \Slim\App(require __DIR__ . '/../src/settings.php');

require __DIR__ . '/dependencies.php';

require __DIR__ . '/routes.php';

$app->get('/', function (Request $request, Response $response) {
    /** @var \Util\Session $session */
    $session = $this->get('session');
    $name = $session->get('name');
    $roles = $session->get('roles', []);

    return $this->view->render($response, 'index.html.twig', [
        'name' => $name,
        'roles' => $roles,
        'session' => $session->getArray(),
    ]);
});

return $app;
