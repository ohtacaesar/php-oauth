<?php

namespace Middleware;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Slim\Http\Request;
use Slim\Http\Response;
use Util\Session;

class Csrf
{
    const TOKEN_KEY = '_csrf_token';

    /** @var Session */
    private $session;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(Session $session, LoggerInterface $logger = null)
    {
        $this->session = $session;
        $this->logger = $logger;
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            if (!$token = $request->getParam(self::TOKEN_KEY)) {
                $this->logger->info('Token is empty.');
                return $response->withRedirect('/');
            }

            if ($token !== $this->getToken(true)) {
                $this->logger->info('Invalid token.');
                return $response->withRedirect('/');
            }
        }

        return $next($request, $response);
    }

    public function getKey()
    {
        return self::TOKEN_KEY;
    }

    public function getToken($unset = false)
    {
        $token = $this->session->get(self::TOKEN_KEY);

        if ($token === null) {
            $token = bin2hex(random_bytes(20));
            $this->session[self::TOKEN_KEY] = $token;
        } elseif ($unset) {
            $this->session[self::TOKEN_KEY] = bin2hex(random_bytes(20));
        }

        return $token;
    }
}
