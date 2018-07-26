<?php

namespace Twig;

use Middleware\Csrf;

class CsrfExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /** @var Csrf */
    protected $csrf;

    public function __construct(Csrf $csrf)
    {
        $this->csrf = $csrf;
    }

    public function getGlobals()
    {
        return [
            'csrf' => [
                'key' => $this->csrf->getKey(),
                'token' => $this->csrf->getToken(),
            ]
        ];
    }
}
