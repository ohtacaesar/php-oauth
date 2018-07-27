<?php

namespace Tests\Functional;

class AdminTest extends BaseTestCase
{
    protected $withMiddleware = false;

    public function test1()
    {
        $r = $this->runApp('GET', '/admin');
        $this->assertEquals(401, $r->getStatusCode());
    }

    public function test2()
    {
        $r = $this->runApp('GET', '/admin', null, ['roles' => ['USER']]);
        $this->assertEquals(403, $r->getStatusCode());
    }

    public function test3()
    {
        $r = $this->runApp('GET', '/admin', null, ['roles' => ['ADMIN']]);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test4()
    {
        $r = $this->runApp('GET', '/admin/users', null, ['roles' => ['ADMIN']]);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test5()
    {
        $r = $this->runApp('GET', '/admin/storage/redis', null, ['roles' => ['ADMIN']]);
        $this->assertEquals(200, $r->getStatusCode());
    }
}
