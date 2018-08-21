<?php

namespace Controller\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Google;
use Slim\Container;
use Util\Providers;

/**
 * Class GoogleController
 * @package Controller
 */
class GoogleController extends OAuthController
{
    /** @var Google */
    private $provider;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->provider = $container['googleProvider'];
    }

    public function getProviderId(): int
    {
        return Providers::GOOGLE;
    }

    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }
}
