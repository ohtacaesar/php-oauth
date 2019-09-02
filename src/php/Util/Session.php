<?php

namespace Util;

class Session implements \ArrayAccess
{
    private $config;

    private $session;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * session_startするとキャッシュ効かないようにヘッダ返すため、利用するまでsession_startしないようにする
     */
    private function init()
    {
        if ($this->session === null) {
            session_start($this->config);
            $this->session = &$_SESSION;
        }
    }

    public function offsetExists($offset)
    {
        $this->init();
        return isset($this->session[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->init();
        return $this->session[$offset];
    }

    public function offsetSet($offset, $value): void
    {
        $this->init();
        $this->session[$offset] = $value;
    }

    public function offsetUnset($offset): void
    {
        $this->init();
        unset($this->session[$offset]);
    }

    public function get($offset, $default = null)
    {
        $this->init();
        if (isset($this->session[$offset])) {
            $default = $this->session[$offset];
        }

        return $default;
    }

    public function getUnset($offset, $default = null)
    {
        $this->init();
        $default = $this->get($offset, $default);
        unset($this[$offset]);

        return $default;
    }

    public function getArray()
    {
        $this->init();
        return $this->session;
    }
}
