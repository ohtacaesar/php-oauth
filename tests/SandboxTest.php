<?php

namespace Tests\Unit\Util;

use PHPUnit\Framework\TestCase;

class SandboxTest extends TestCase
{
    public function testNullCoalescingOperator()
    {
        $tmp = error_reporting();
        error_reporting(E_ALL);
        $a = [];
        $this->assertNull($a[0] ?? null);
        error_reporting($tmp);
    }

    public function testBytes()
    {
        $this->assertSame(40, mb_strlen(bin2hex(random_bytes(20))));
    }
}
