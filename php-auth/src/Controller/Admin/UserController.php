<?php

namespace Controller\Admin;

use Controller\BaseController;
use Dao\UserDao;
use Dao\UserRoleDao;
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

    /** @var UserRoleDao */
    private $userRoleDao;

    /**
     * UserController constructor.
     * @param Container $container
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);
        $this->userDao = $container->get('userDao');
        $this->userRoleDao = $container->get('userRoleDao');
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
        return $this->view->render($response, 'admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return ResponseInterface
     */
    public function show(Request $request, Response $response, array $args)
    {
        $user = $this->userDao->findByUserId(intval($args['user_id']));

        return $this->view->render($response, 'admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function userAddRole(Request $request, Response $response, array $args)
    {
        $user = $this->userDao->findByUserId(intval($args['user_id']));
        if (!$user) {
            return $response->withRedirect($this->router->pathFor('users'));
        }

        $role = $request->getParam('role');
        $role = mb_strtoupper($role);
        if (!$role || !preg_match('/^[A-Z]{1,8}$/', $role)) return $response->withStatus(400);
        $v = $this->userRoleDao->add($user['user_id'], $role);
        var_dump($v);

        return $response->withRedirect($this->router->pathFor('user', $user));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function userRemoveRole(Request $request, Response $response, array $args)
    {
    }
}
