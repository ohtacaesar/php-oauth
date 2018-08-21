<?php

namespace Tests\Functional;

class StaticTest extends BaseTestCase
{
    protected $withMiddleware = false;

    public function test1()
    {
        $r = $this->runApp('GET', '/images/GitHub-Mark-32px.png');
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test2()
    {
        $r = $this->runApp('GET', '/images/notfound.png');
        $this->assertEquals(404, $r->getStatusCode());
    }
}
