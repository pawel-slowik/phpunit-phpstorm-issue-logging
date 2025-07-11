<?php

declare(strict_types=1);

namespace App\Test;

use App\Foo;
use PHPUnit\Framework\TestCase;

class FooTest extends TestCase
{
    public function testPhpWarning(): void
    {
        self::assertNull((new Foo())->get());
    }

    public function testPhpNotice(): void
    {
        self::assertFalse((new Foo())->convert());
    }

    public function testPhpunitDeprecation(): void
    {
        $foo = $this->getMockBuilder(Foo::class)
            ->enableAutoload()
            ->getMock();
        self::assertTrue(true);
    }
}
