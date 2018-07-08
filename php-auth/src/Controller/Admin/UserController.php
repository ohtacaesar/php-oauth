<?php

namespace Controller\Admin;

use Controller\BaseController;
use Dao\UserDao;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Class UserController
 * @package Controller\Admin
 */
class UserController extends BaseController
{
    /** @var UserDao */
    private $userDao;

    /**
     * UserController constructor.
     * @param Container $container
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userDao = $container->get('userDao');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function index(Request $request, Response $response)
    {
        if (!isset($_SESSION['roles'])) {
            return $response->withStatus(401);
        }

        if (!in_array('ADMIN', $_SESSION['roles'], true)) {
            return $response->withStatus(403);
        }

        $users = $this->userDao->getAll();
        return $this->view->render($response, 'admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     */
    public function update(Request $request, Response $response, array $args)
    {
        var_dump($args);
        exit(0);
    }
}