<?php

namespace Controller;

use Manager\UserManager;
use Psr\Log\LoggerInterface;
use Slim\Container;
use Slim\Http\Request;
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

    /** @var Session */
    protected $session;

    /** @var LoggerInterface */
    protected $logger;

    /** @var UserManager */
    protected $userManager;

    /**
     * BaseController constructor.
     * @param Container $container
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->pdo = $container->get('pdo');
        $this->view = $container->get('view');
        $this->router = $container->get('router');
        $this->session = $container->get('session');
        $this->logger = $container->get('logger');
        $this->userManager = $container['userManager'];
    }

    protected function getLoginUser(): ?array
    {
        if (!$userId = $this->session->get('user_id')) {
            return null;
        }

        return $this->userManager->getUserByUserId($userId);
    }

    protected function validateRedirectUrl(Request $request, $redirectUrl)
    {
        $redirectUrl = filter_var($redirectUrl, FILTER_VALIDATE_URL);
        $redirectUrl = filter_var($redirectUrl, FILTER_SANITIZE_URL);

        $url = parse_url($redirectUrl);
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
