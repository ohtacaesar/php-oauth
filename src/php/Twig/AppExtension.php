<?php

namespace Twig;

use Slim\Container;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppExtension extends AbstractExtension implements GlobalsInterface
{
    private $app;

    public function __construct(Container $c)
    {
        $this->app = $c['settings']['app'];
    }

    public function getGlobals(): array
    {
        return [
            'app' => $this->app,
        ];
    }
}
