<?php

namespace Controller\OAuth;

use Controller\BaseController;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Psr\Http\Message\ResponseInterface;
use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

abstract class OAuthController extends BaseController
{
    /** @var AuthService */
    protected $authService;

    abstract public function getProviderId(): int;

    abstract public function getProvider(): AbstractProvider;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container['authService'];
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

        return $response->withRedirect($this->getProvider()->getAuthorizationUrl());
    }

    public function callback(Request $request, Response $response): ResponseInterface
    {
        if (null === ($code = $request->getParam('code'))) {
            return $response->withStatus(400);
        }

        $accessToken = $this->getProvider()->getAccessToken('authorization_code', [
            'code' => $code
        ]);

        /** @var ResourceOwnerInterface $owner */
        $owner = $this->getProvider()->getResourceOwner($accessToken);
        $this->authService->signUp($this->getProviderId(), $owner->getId(), $owner->getName());

        return $response->withRedirect($this->session->getUnset('rd', '/'));
    }
}
