<?php

namespace Controller;

use Interop\Container\Exception\ContainerException;
use Manager\UserManager;
use Psr\Log\LoggerInterface;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Uri;
use Slim\Router;
use Slim\Views\Twig;
use Util\Session;

/**
 * Class BaseController
 * @package Controller
 */
class BaseController
{
    /** @var Container */
    protected $container;

    /** @var \PDO */
    protected $pdo;

    /** @var Twig */
    protected $view;

    /** @var Router */
    protected $router;

    /** @var Uri */
    protected $uri;

    /** @var Session */
    protected $session;

    /** @var LoggerInterface */
    protected $logger;

    /** @var UserManager */
    protected $userManager;

    /**
     * BaseController constructor.
     * @param Container $container
     * @throws ContainerException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->pdo = $container->get('pdo');
        $this->view = $container->get('view');
        $this->router = $container->get('router');
        $this->uri = $container->get('uri');
        $this->session = $container->get('session');
        $this->logger = $container->get('logger');
        $this->userManager = $container['userManager'];
    }

    protected function getLoginUser(): ?array
    {
        if (!$userId = $this->session->get('user_id')) {
            return null;
        }

        $user = $this->userManager->getUserByUserId($userId);
        if ($user === null) {
            unset($this->session['user_id']);
            return null;
        }

        return $user;
    }

    protected function validateRedirectUrl(Request $request, $redirectUrl)
    {
        $url = $redirectUrl;
        $url = filter_var($url, FILTER_VALIDATE_URL);
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $url = parse_url($url);
        if (!$url) {
            return false;
        }

        if (null === ($host = $url['host'] ?? null)) {
            return false;
        }

        $host = array_reverse(explode('.', $host));
        $serverName = $request->getServerParam('SERVER_NAME');
        $serverName = array_reverse(explode('.', $serverName));

        for ($i = 0, $l = max(2, count($serverName) - 1); $i < $l; $i++) {
            if ($host[$i] !== $serverName[$i]) {
                return false;
            }
        }

        return true;
    }
}
