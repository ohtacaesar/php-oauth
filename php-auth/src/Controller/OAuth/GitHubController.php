<?php

namespace Controller\OAuth;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Github;
use Slim\Container;
use Util\Providers;

/**
 * Class GithubController
 * @package Controller
 */
class GitHubController extends OAuthController
{
    /** @var Github */
    private $provider;

    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->provider = $container['githubProvider'];
    }

    public function getProviderId(): int
    {
        return Providers::GITHUB;
    }

    public function getProvider(): AbstractProvider
    {
        return $this->provider;
    }
}
