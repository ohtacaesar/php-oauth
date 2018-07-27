<?php

namespace Tests\Functional;

use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Container;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

require_once __DIR__ . '/../../src/app.php';


class BaseTestCase extends TestCase
{
    protected $withMiddleware = true;

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

        $app = createApp($this->withMiddleware);

        /**
         * @var App $app
         * @var Container $container
         */
        $container = $app->getContainer();
        $container['session'] = new \Util\Session($session);

        $response = $app->process($request, $response);

        return $response;
    }
}
