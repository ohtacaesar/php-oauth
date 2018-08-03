<?php

namespace Twig;

use Util\Session;

class FlashExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /** @var Session */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function getGlobals()
    {
        return [
            'flash' => $this->session->getUnset('flash')
        ];
    }
}
