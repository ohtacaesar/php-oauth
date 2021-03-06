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
        if ($redirectUrl = $request->getParam('rd', null)) {
            if ($this->validateRedirectUrl($request, $redirectUrl)) {
                $this->session['rd'] = $redirectUrl;
            } else {
                return $response->withStatus(400);
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
            $this->logger->error('ログインに失敗しました。');
            $this->session['flash'] = 'ログインに失敗しました。';
            return $response->withRedirect($this->router->pathFor('home'));
        }

        $this->session['flash'] = 'ログインしました。';

        return $response->withRedirect($this->session->getUnset('rd', $this->router->pathFor('home')));
    }
}
