<?php

declare(strict_types=1);

namespace PawelSlowik\PhpunitPhpstormIssueLogging;

use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Event;

final class PhpstormIssueLogger
{
    public function logIssue(Test $test, Event $event): void
    {
        fwrite(STDOUT, $this->formatFailureMessage($test->name(), $event::class, $event->asString()));
    }

    private function formatFailureMessage(string $name, string $message, string $description): string
    {
        return sprintf(
            "##teamcity[testFailed name='%s' message='%s' details='%s']\n",
            $this->escape($name),
            $this->escape($message),
            $this->escape($description),
        );
    }

    private function escape(string $string): string
    {
        return str_replace(
            ['|', "'", "\n", "\r", ']', '['],
            ['||', "|'", '|n', '|r', '|]', '|['],
            $string,
        );
    }
}
