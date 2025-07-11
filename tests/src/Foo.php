<?php

declare(strict_types=1);

namespace App;

class Foo
{
    public function get(): ?string
    {
        return $notSet;
    }

    public function convert(): string|bool
    {
        return iconv("utf-8", "us-ascii", "ą");
    }
}
