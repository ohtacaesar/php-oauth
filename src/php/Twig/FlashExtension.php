<?php

namespace Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Util\Session;

class FlashExtension extends AbstractExtension implements GlobalsInterface
{
    /** @var Session */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getGlobals(): array
    {
        return [
            'flash' => $this->session->getUnset('flash')
        ];
    }
}
