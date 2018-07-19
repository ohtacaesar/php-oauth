<?php

namespace Util;

class Session implements \ArrayAccess
{
    private $session;

    public function __construct(array &$session)
    {
        $this->session = &$session;
    }

    public function offsetExists($offset)
    {
        return isset($this->session[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->session[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->session[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->session[$offset]);
    }

    public function get($offset, $default = null)
    {
        if (isset($this->session[$offset])) {
            $default = $this->session[$offset];
        }

        return $default;
    }

    public function getUnset($offset, $default = null)
    {
        $default = $this->get($offset, $default);
        unset($this[$offset]);

        return $default;
    }

    public function getArray()
    {
        return $this->session;
    }
}
