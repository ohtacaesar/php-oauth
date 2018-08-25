<?php

namespace Tests\Functional;

class Home extends BaseTestCase
{
    protected $withMiddleware = false;

    /**
     * @test
     */
    public function home()
    {
        $r = $this->runApp('GET', '/');
        $this->assertEquals(200, $r->getStatusCode());
    }

    /**
     * @test
     */
    public function homeWithRd()
    {
        $r = $this->runApp('GET', '/?rd=http://example.com');
        $this->assertEquals(200, $r->getStatusCode());
        $r = $this->runApp('GET', '/?rd=http://test.example.com');
        $this->assertEquals(200, $r->getStatusCode());
    }

    /**
     * @test
     */
    public function homeWithWrongRd()
    {
        $r = $this->runApp('GET', '/?rd=test');
        $this->assertEquals(400, $r->getStatusCode());
    }

    /**
     * @test
     */
    public function homeWithOtherDomainRd()
    {
        $r = $this->runApp('GET', '/?rd=http://google.com');
        $this->assertEquals(400, $r->getStatusCode());
    }
}
