<?php

namespace Controller\OAuth;

use Controller\BaseController;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Manager\UserManager;
use Psr\Http\Message\ResponseInterface;
use Service\AuthService;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Util\Providers;

abstract class OAuthController extends BaseController
{
    /** @var AuthService */
    protected $authService;

    /** @var UserManager */
    protected $userManager;

    /** @var array */
    protected $grantConfig;

    abstract public function getProviderId(): int;

    abstract public function getProvider(): AbstractProvider;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->authService = $container['authService'];
        $this->userManager = $container['userManager'];
        $settings = $container['settings'];

        if (isset($settings['oauth']) and isset($settings['oauth'][Providers::GITHUB])) {
            $this->grantConfig = $settings['oauth'][Providers::GITHUB];
            assert(is_array($this->grantConfig));
        }
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
        $user = $this->authService->signUp($this->getProviderId(), $owner);

        if (!$user) {
            $this->logger->error('ログインに失敗');
            return $response->withRedirect('/');
        }

        return $response->withRedirect($this->session->getUnset('rd', '/'));
    }
}
