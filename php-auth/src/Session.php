<?php

class Session implements ArrayAccess
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
}
