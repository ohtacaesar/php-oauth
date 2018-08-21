<?php

namespace Controller;

use Slim\Http\Request;
use Slim\Http\Response;

class AdminController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'admin/index.html.twig');
    }
}
