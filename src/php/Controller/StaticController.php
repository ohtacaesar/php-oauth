<?php

namespace Controller;

use Slim\Exception\NotFoundException;
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
     * @throws NotFoundException
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
     * @throws NotFoundException
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
     * @throws NotFoundException
     */
    private function staticFile(Request $request, Response $response, $path)
    {
        if (!is_file($path)) {
            $this->logger->info(sprintf('File not found.(%s)', $path));
            throw new NotFoundException($request, $response);
        }

        $ext = substr($path, strrpos($path, '.') + 1);
        if (!isset(static::MIME_MAP[$ext])) {
            throw new NotFoundException($request, $response);
        }

        $contentType = static::MIME_MAP[$ext];
        $response = $response
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Cache-Control', 'public, max-age=31536000');
        $response->getBody()->write(file_get_contents($path));

        return $response;
    }
}
