<?php

namespace Controller;

use League\OAuth2\Client\Provider\Google;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class GoogleController
 * @package Controller
 */
class GoogleController extends BaseController
{
    /** @var Google */
    private $googleProvider;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->googleProvider = $container['googleProvider'];
    }

    public function start(Request $request, Response $response): ResponseInterface
    {
        if ($rd = $request->getParam('rd', null)) {
            $rd = filter_var($rd, FILTER_VALIDATE_URL);
            $rd = filter_var($rd, FILTER_SANITIZE_URL);
            if ($rd) {
                $this->session['rd'] = $rd;
            }
        }

        return $response->withRedirect($this->googleProvider->getAuthorizationUrl());
    }

    public function callback(Request $request, Response $response): ResponseInterface
    {
        if (null === ($code = $request->getParam('code'))) {
            return $response->withStatus(400);
        }

        $accessToken = $this->googleProvider->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        $owner = $this->googleProvider->getResourceOwner($accessToken);
        $this->session['google_access_token'] = $accessToken;
        $this->session['google_owner'] = $owner;

        return $response->withRedirect($this->session->getUnset('rd', '/'));
    }
}
