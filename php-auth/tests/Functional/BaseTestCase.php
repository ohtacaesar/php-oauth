<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class BaseTestCase extends TestCase
{
    public function runApp($method, $uri, $requestData = null, array $session = [], array $env = [])
    {
        $environment = Environment::mock(array_merge([
            'REQUEST_METHOD' => $method,
            'REQUEST_URI' => $uri,
        ], $env));

        $request = Request::createFromEnvironment($environment);

        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        $response = new Response();

        /**
         * @var App $app
         * @var Container $container
         */
        $app = require __DIR__ . '/../../src/app.php';
        $container = $app->getContainer();
        $container['session'] = new \Session($session);

        $response = $app->process($request, $response);

        return $response;
    }
}
