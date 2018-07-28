<?php

namespace Tests\Unit\Util;

use PHPUnit\Framework\TestCase;
use Util\ProviderException;
use Util\Providers;

class ProvidersTest extends TestCase
{
    public function testValues()
    {
        $values = Providers::values();
        $this->assertArrayHasKey('GITHUB', $values);
        $this->assertArrayHasKey('GOOGLE', $values);
    }

    public function testValueOf()
    {
        $this->assertEquals(1, Providers::valueOf('GITHUB'));
        $this->assertEquals(1, Providers::valueOf('GitHub'));
        $this->assertEquals(2, Providers::valueOf('GOOGLE'));
        $this->assertEquals(2, Providers::valueOf('Google'));
        try {
            Providers::valueOf('GIFHUB');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertEquals('Util\Providers::valueOf(\'GIFHUB\')', $e->getMessage());
        }
        try {
            Providers::valueOf('Goggle');
            $this->fail();
        } catch (\LogicException $e) {
            $this->assertEquals('Util\Providers::valueOf(\'GOGGLE\')', $e->getMessage());
        }
    }
}
