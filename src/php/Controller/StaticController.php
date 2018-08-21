<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class StaticController
 * @package Controller
 */
class StaticController extends BaseController
{
    const PUBLIC_DIR = __DIR__ . '/../../../public';

    const MIME_MAP = [
        'js' => 'text/javascript',
        'css' => 'text/css',
        'png' => 'mage/png',
    ];

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Slim\Exception\NotFoundException
     */
    public function dist(Request $request, Response $response, $args = [])
    {
        $path = static::PUBLIC_DIR . '/dist/' . $args['params'] ?? '';

        return $this->staticFile($request, $response, $path);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     * @throws \Slim\Exception\NotFoundException
     */
    public function images(Request $request, Response $response, $args = [])
    {
        $path = static::PUBLIC_DIR . '/images/' . $args['params'] ?? '';

        return $this->staticFile($request, $response, $path);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $path
     * @return Response
     * @throws \Slim\Exception\NotFoundException
     */
    private function staticFile(Request $request, Response $response, $path)
    {
        if (!is_file($path)) {
            $this->logger->info(sprintf('File not found.(%s)', $path));
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $ext = substr($path, strrpos($path, '.') + 1);
        if (!isset(static::MIME_MAP[$ext])) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $contentType = static::MIME_MAP[$ext];
        $response = $response->withHeader('content-type', $contentType);
        $response->getBody()->write(file_get_contents($path));

        return $response;
    }
}
