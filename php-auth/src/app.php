<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

$app = new \Slim\App(require_once __DIR__ . '/../src/settings.php');

require_once __DIR__ . '/dependencies.php';

require_once __DIR__ . '/routes.php';

$app->get('/', function (Request $request, Response $response) {
    $name = null;
    if (isset($_SESSION['name'])) {
        $name = $_SESSION['name'];
    }

    return $this->view->render($response, 'index.html.twig', [
        'name' => $name
    ]);
});

return $app;
