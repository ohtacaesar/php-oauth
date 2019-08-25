<?php

namespace Controller\Admin;

use Controller\BaseController;
use Dao\UserDao;
use Dao\UserRoleDao;
use Psr\Http\Message\ResponseInterface;
use Slim\Container;
use Slim\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Uri;

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

    /** @var Uri */
    private $uri;

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
        $this->uri = $container->get('uri');
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

        $signinToken = $user['signin_token'] ?? null;
        $tokenSigninUrl = null;
        if ($signinToken) {
            $tokenSigninPath = $this->router->pathFor('token_signin', [], ['token' => $signinToken]);
            $tokenSigninUrl = $this->uri->getBaseUrl() . $tokenSigninPath;
        }

        return $this->view->render($response, 'admin/users/show.html.twig', [
            'user' => $user,
            'token_signin_url' => $tokenSigninUrl,
        ]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function addRole(Request $request, Response $response, array $args)
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
            $this->session['flash'] = $e->getMessage();
        }

        return $response->withRedirect($this->router->pathFor('user', $user));
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function removeRole(Request $request, Response $response, array $args)
    {
        $currentUserId = $this->session['user_id'];
        $role = $args['role'];

        // 作業者のADMINロールは削除できない
        if ($currentUserId === $args['user_id'] && $role === 'ADMIN') {
            $this->session['flash'] = '自分のADMINロールは削除できません';
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

    public function removeProvider(Request $request, Response $response, array $args)
    {
        if (!$user = $this->userManager->getUserByUserId($args['user_id'])) {
            $this->session['flash'] = 'ユーザーが見つかりませんでした';
            return $response->withRedirect($this->router->pathFor('users'));
        }

        if (count($user['user_providers']) <= 1) {
            $this->session['flash'] = 'プロバイダが一つしか登録されていないため、削除できません';
            return $response->withRedirect($this->router->pathFor('user', $user));
        }

        $this->userManager->getUserProviderDao()->delete($args);

        return $response->withRedirect($this->router->pathFor('user', $user));
    }

    public function generateSigninToken(Request $request, Response $response, array $args)
    {
        if (!$user = $this->userManager->getUserByUserId($args['user_id'])) {
            $this->session['flash'] = 'ユーザーが見つかりませんでした';
            return $response->withRedirect($this->router->pathFor('users'));
        }

        $signinToken = $this->userManager->generateSigninToken();
        $user['signin_token'] = $signinToken;
        $this->userManager->updateUser($user);
        $this->session['flash'] = 'ログイントークンを生成しました。';

        return $response->withRedirect($this->router->pathFor('user', $user));
    }

    public function deleteSigninToken(Request $request, Response $response, array $args)
    {
        if (!$user = $this->userManager->getUserByUserId($args['user_id'])) {
            $this->session['flash'] = 'ユーザーが見つかりませんでした';
            return $response->withRedirect($this->router->pathFor('users'));
        }

        $user['signin_token'] = null;
        $this->userManager->updateUser($user);
        $this->session['flash'] = 'ログイントークンを削除しました。';

        return $response->withRedirect($this->router->pathFor('user', $user));
    }
}
