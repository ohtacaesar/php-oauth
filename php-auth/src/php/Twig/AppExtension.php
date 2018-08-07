<?php

namespace Twig;

use Slim\Container;

class AppExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    private $app;

    public function __construct(Container $c)
    {
        $this->app = $c['settings']['app'];
    }

    public function getGlobals()
    {
        return [
            'app' => $this->app,
        ];
    }
}
