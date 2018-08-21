<?php

namespace Tests\Functional;

class AdminTest extends BaseTestCase
{
    protected $withMiddleware = false;

    public function test1()
    {
        $r = $this->runApp('GET', '/admin');
        $this->assertEquals(302, $r->getStatusCode());
    }

    public function test2()
    {
        $r = $this->runApp('GET', '/admin', null, ['user_id' => 'user']);
        $this->assertEquals(302, $r->getStatusCode());
    }

    public function test3()
    {
        $r = $this->runApp('GET', '/admin', null, ['user_id' => 'admin']);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test4()
    {
        $r = $this->runApp('GET', '/admin/users', null, ['user_id' => 'admin']);
        $this->assertEquals(200, $r->getStatusCode());
    }
}
