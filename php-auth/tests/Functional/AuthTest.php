<?php

namespace Tests\Functional;

class AuthTest extends BaseTestCase
{
    protected $withMiddleware = false;

    public function test1()
    {
        $r = $this->runApp('GET', '/auth');
        $this->assertEquals(401, $r->getStatusCode());
    }

    public function test2()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'user']);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test3()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'user'], ['HTTP_ROLE' => 'ADMIN']);
        $this->assertEquals(403, $r->getStatusCode());
    }

    public function test4()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'admin'], ['HTTP_ROLE' => 'ADMIN']);
        $this->assertEquals(200, $r->getStatusCode());
    }
}
