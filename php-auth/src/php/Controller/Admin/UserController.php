<?php

namespace Controller\Admin;

use Controller\BaseController;
use Dao\UserDao;
use Dao\UserGithubDao;
use Dao\UserProviderDao;
use Dao\UserRoleDao;
use Dao\UserSessionDao;
use Manager\UserManager;
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

    /** @var UserSessionDao */
    private $userSessionDao;

    /** @var UserManager */
    private $userManager;

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
        $this->userSessionDao = $container->get('userSessionDao');
        $this->userManager = $container['userManager'];
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return ResponseInterface
     */
    public function index(Request $request, Response $response)
    {
        $users = $this->userDao->findAll();
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
        $user = $this->userManager->getUserByUserId($args['user_id']);
        if (!$user) {
            throw new NotFoundException($request, $response);
        }

        $message = null;
        if (isset($this->session['message'])) {
            $message = $this->session['message'];
            unset($this->session['message']);
        }

        return $this->view->render($response, 'admin/users/show.html.twig', [
            'user' => $user,
            'message' => $message,
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
        $user = $this->userDao->findOneByUserId($args['user_id']);
        if (!$user) {
            return $response->withRedirect($this->router->pathFor('users'));
        }

        $role = $request->getParam('role');
        $role = mb_strtoupper($role);
        if (!$role || !preg_match('/^[A-Z]{1,8}$/', $role)) {
            $this->session['flash'] = 'ロールの指定が正しくありません';
            return $response->withRedirect($this->router->pathFor('user', $user));
        }

        try {
            $this->userRoleDao->update([
                'user_id' => $user['user_id'],
                'role' => $role,
            ]);
        } catch (\PDOException $e) {
            $this->session['message'] = $e->getMessage();
        }

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
        $currentUserId = $this->session['user_id'];
        $role = $args['role'];

        // 作業者のADMINロールは削除できない
        if ($currentUserId === $args['user_id'] && $role === 'ADMIN') {
            $this->session['message'] = '自分のADMINロールは削除できません';
        } else {
            $this->userRoleDao->delete($args);
        }

        $user = $this->userDao->findOneByUserId($args['user_id']);
        if ($user) {
            $rd = $this->router->pathFor('user', $user);
        } else {
            $rd = $this->router->pathFor('user_index');
        }
        return $response->withRedirect($rd);
    }


    public function userRemoveProvider(Request $request, Response $response, array $args)
    {
        if (!$user = $this->userManager->getUserByUserId($args['user_id'])) {
            $this->session['message'] = 'ユーザーが見つかりませんでした';
            return $response->withRedirect($this->router->pathFor('user', $user));
        }

        if (count($user['user_providers']) <= 1) {
            $this->session['message'] = 'プロバイダが一つしか登録されていないため、削除できません';
            return $response->withRedirect($this->router->pathFor('user', $user));
        }

        $this->userManager->getUserProviderDao()->delete($args);

        return $response->withRedirect($this->router->pathFor('user', $user));
    }
}