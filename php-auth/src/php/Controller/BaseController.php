<?php

namespace Controller;

use Psr\Log\LoggerInterface;
use Slim\Container;
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
    }
}