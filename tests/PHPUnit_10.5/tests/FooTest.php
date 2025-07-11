<?php

declare(strict_types=1);

namespace App\Test;

use App\Foo;
use PHPUnit\Framework\Attributes\DataProvider;
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

    // Can't test PHPUnit deprecations, because the only functionality that
    // triggers them in 10.5 is related to data providers, which can't be safely
    // handled for reasons described in README.md.
}
