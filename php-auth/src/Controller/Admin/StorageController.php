<?php

namespace Controller\Admin;


use Controller\BaseController;
use Slim\Http\Request;
use Slim\Http\Response;

class StorageController extends BaseController
{
    /**
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function redis(Request $request, Response $response)
    {
        /** @var \Redis $redis */
        $redis = $this->container->get('redis');
        $keys = $redis->keys('*');

        return $this->view->render($response, 'admin/storage/redis.html.twig', [
            'keys' => $keys,
        ]);
    }
}