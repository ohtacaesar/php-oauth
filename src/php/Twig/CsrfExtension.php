<?php

namespace Twig;

use Middleware\Csrf;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class CsrfExtension extends AbstractExtension implements GlobalsInterface
{
    /** @var Csrf */
    protected $csrf;

    public function __construct(Csrf $csrf)
    {
        $this->csrf = $csrf;
    }

    public function getGlobals(): array
    {
        return [
            'csrf' => [
                'key' => $this->csrf->getKey(),
                'token' => $this->csrf->getToken(),
            ]
        ];
    }
}
