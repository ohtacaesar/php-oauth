<?php

namespace Controller\Admin;

use Controller\BaseController;
use Dao\UserDao;
use Dao\UserRoleDao;
use Slim\Container;
use Slim\Exception\NotFoundException;
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
     * @throws NotFoundException
     */
    public function show(Request $request, Response $response, array $args)
    {
        $user = $this->userDao->findOneByUserId(intval($args['user_id']));
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        $userRoles = $this->userRoleDao->findByUserId($user['user_id']);
        $user['user_roles'] = $userRoles;

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
        $user = $this->userDao->findOneByUserId(intval($args['user_id']));
        if (!$user) {
            return $response->withRedirect($this->router->pathFor('users'));
        }

        $role = $request->getParam('role');
        $role = mb_strtoupper($role);
        if (!$role || !preg_match('/^[A-Z]{1,8}$/', $role)) return $response->withStatus(400);
        $v = $this->userRoleDao->create($user['user_id'], $role);

        return $response->withRedirect($this->router->pathFor('user', $user));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function userRemoveRole(Request $request, Response $response, array $args)
    {
        $user = $this->userDao->findOneByUserId($args['user_id']);
        $this->userRoleDao->delete($args);

        if ($user) {
            $rd = $this->router->pathFor('user', $user);
        } else {
            $rd = $this->router->pathFor('user_index');
        }
        return $response->withRedirect($rd);
    }
}
