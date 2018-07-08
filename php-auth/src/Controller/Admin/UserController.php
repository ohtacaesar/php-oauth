<?php

namespace Controller\Admin;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class UserController
 * @package Controller\Admin
 */
class UserController
{
    private $container;
    private $userDao;
    private $view;

    /**
     * UserController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->userDao = $container->get('userDao');
        $this->view = $container->get('view');
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
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