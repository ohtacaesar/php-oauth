<?php

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

$container = $app->getContainer();

$container['notFoundHandler'] = function (Container $c) {
    /** @var \Slim\Views\Twig $view */
    $view = $c['view'];
    return function (Request $request, Response $response) use ($view) {
        return $view->render($response->withStatus(404), 'error.html.twig', [
            'message' => '404 Not Found'
        ]);
    };
};

$container['notAllowedHandler'] = function (Container $c) {
    /** @var \Slim\Views\Twig $view */
    $view = $c['view'];
    return function (Request $request, Response $response) use ($view) {
        return $view->render($response->withStatus(405), 'error.html.twig', [
            'message' => '405 Method Not Allowed'
        ]);
    };
};
