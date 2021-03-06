<?php

namespace Tests\Functional;

class AuthTest extends BaseTestCase
{
    protected $withMiddleware = false;

    public function test1()
    {
        $r = $this->runApp('GET', '/auth', null, [], [
            'HTTP_X_AUTH_ENABLE' => 1,
        ]);
        $this->assertEquals(401, $r->getStatusCode());
    }

    public function test2()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'user'], [
            'HTTP_X_AUTH_ENABLE' => 1,
        ]);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test3()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'user'], [
            'HTTP_X_AUTH_ENABLE' => 1,
            'HTTP_X_AUTH_ROLES' => 'ADMIN',
        ]);
        $this->assertEquals(403, $r->getStatusCode());
    }

    public function test4()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'admin'], [
            'HTTP_X_AUTH_ENABLE' => 1,
            'HTTP_X_AUTH_ROLES' => 'ADMIN',
        ]);
        $this->assertEquals(200, $r->getStatusCode());
    }

    public function test5()
    {
        $r = $this->runApp('GET', '/auth', null, ['user_id' => 'admin']);
        $this->assertEquals(400, $r->getStatusCode());
    }
}
