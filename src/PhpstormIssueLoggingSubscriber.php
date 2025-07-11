<?php

declare(strict_types=1);

namespace PawelSlowik\PhpunitPhpstormIssueLogging;

use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\WarningTriggered;

abstract class PhpstormIssueLoggingSubscriber
{
    private PhpstormIssueLogger $issueLogger;

    /**
     * @var string[]
     */
    private array $loggedEventIds = [];

    public function __construct(PhpstormIssueLogger $issueLogger)
    {
        $this->issueLogger = $issueLogger;
    }

    protected function logEventOnce(
        PhpunitWarningTriggered|PhpWarningTriggered|WarningTriggered|
        PhpunitNoticeTriggered|PhpNoticeTriggered|NoticeTriggered|
        PhpunitDeprecationTriggered|PhpDeprecationTriggered|DeprecationTriggered
        $event
    ): void {
        $eventId = $event->asString();

        if (in_array($eventId, $this->loggedEventIds, true)) {
            return;
        }
        $this->loggedEventIds[] = $eventId;

        $this->issueLogger->logIssue($event->test(), $event);
    }
}
