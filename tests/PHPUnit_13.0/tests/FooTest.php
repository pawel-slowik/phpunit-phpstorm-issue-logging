<?php

declare(strict_types=1);

namespace App\Test;

use App\Foo;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
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

    #[AllowMockObjectsWithoutExpectations]
    public function testPhpunitDeprecation(): void
    {
        $foo = self::createMock(Foo::class);
        $foo
            ->expects($this->any())
            ->method('get');
        self::assertTrue(true);
    }
}
