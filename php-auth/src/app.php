<?php

use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/functions.php';

$app = new \Slim\App(require_once __DIR__ . '/../src/settings.php');

require_once __DIR__ . '/dependencies.php';

require_once __DIR__ . '/routes.php';

$app->get('/', function (Request $request, Response $response) {
    $login = null;
    if (isset($_SESSION['user']) && isset($_SESSION['user']['login'])) {
        $login = $_SESSION['user']['login'];
    }

    return $this->view->render($response, 'index.html.twig', [
        'login' => $login
    ]);
});

$app->get('/auth', function (Request $request, Response $response) {
    // 認証
    if (!isset($_SESSION['roles'])) {
        return $response->withStatus(401);
    }

    // 認可
    if (isset($_SERVER['HTTP_ROLE']) && !in_array($_SESSION['HTTP_ROLE'], $_SESSION['roles'], true)) {
        return $response->withStatus(403);
    }

    return $response->withStatus(200);
});

$app->get('/logout', function (Request $request, Response $response) {
    $rd = $request->getParam('rd');
    $rd = filter_var($rd, FILTER_VALIDATE_URL);
    $rd = filter_var($rd, FILTER_SANITIZE_URL);

    if (!$rd) {
        return $response->withStatus(400);
    }

    session_destroy();

    return $response->withRedirect($rd);

})->setName('logout');

return $app;
