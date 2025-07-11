<?php

declare(strict_types=1);

namespace PawelSlowik\PhpunitPhpstormIssueLogging;

use Iterator;
use PHPUnit\Event\Subscriber;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\NoticeTriggeredSubscriber;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggeredSubscriber;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggeredSubscriber;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggeredSubscriber;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggeredSubscriber;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use RuntimeException;

final class PhpstormIssueLoggingExtension implements Extension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if (!$configuration->outputIsTeamCity()) {
            return;
        }

        // this logger doesn't support writing to a file
        if ($configuration->hasLogfileTeamcity()) {
            return;
        }

        $issueLoggingConfiguration = $this->createIssueLoggingConfiguration($parameters, $configuration);
        $issueLogger = new PhpstormIssueLogger();
        $subscribers = iterator_to_array($this->createSubscribers($issueLoggingConfiguration, $issueLogger));
        $facade->registerSubscribers(...$subscribers);
    }

    /**
     * @return Iterator<int, Subscriber>
     */
    private function createSubscribers(
        PhpstormIssueLoggingConfiguration $configuration,
        PhpstormIssueLogger $issueLogger,
    ): Iterator {
        // warnings
        if ($configuration->logWarning) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements WarningTriggeredSubscriber {
                public function notify(WarningTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if ($configuration->logPhpWarning) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpWarningTriggeredSubscriber {
                public function notify(PhpWarningTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if ($configuration->logPhpunitWarning) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpunitWarningTriggeredSubscriber {
                public function notify(PhpunitWarningTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }

        // notices
        if ($configuration->logNotice) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements NoticeTriggeredSubscriber {
                public function notify(NoticeTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if ($configuration->logPhpNotice) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpNoticeTriggeredSubscriber {
                public function notify(PhpNoticeTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if (interface_exists(PhpunitNoticeTriggeredSubscriber::class)) {
            if ($configuration->logPhpunitNotice) {
                yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpunitNoticeTriggeredSubscriber {
                    public function notify(PhpunitNoticeTriggered $event): void
                    {
                        $this->logEventOnce($event);
                    }
                };
            }
        }

        // deprecations
        if ($configuration->logDeprecation) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements DeprecationTriggeredSubscriber {
                public function notify(DeprecationTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if ($configuration->logPhpDeprecation) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpDeprecationTriggeredSubscriber {
                public function notify(PhpDeprecationTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
        if ($configuration->logPhpunitDeprecation) {
            yield new class ($issueLogger) extends PhpstormIssueLoggingSubscriber implements PhpunitDeprecationTriggeredSubscriber {
                public function notify(PhpunitDeprecationTriggered $event): void
                {
                    $this->logEventOnce($event);
                }
            };
        }
    }

    private function createIssueLoggingConfiguration(
        ParameterCollection $parameters,
        Configuration $configuration,
    ): PhpstormIssueLoggingConfiguration {
        if ($parameters->has('followExitCodeLogic') && $parameters->has('issueTypes')) {
            throw new RuntimeException('conflicting configuration parameters');
        }

        if (
            $parameters->has('followExitCodeLogic')
            && filter_var($parameters->get('followExitCodeLogic'), FILTER_VALIDATE_BOOL)
        ) {
            return PhpstormIssueLoggingConfiguration::fromExitCode($configuration);
        }

        if ($parameters->has('issueTypes')) {
            $issueTypes = array_filter(array_map(trim(...), explode(',', $parameters->get('issueTypes'))));
            return PhpstormIssueLoggingConfiguration::fromIssueTypes($issueTypes);
        }

        return PhpstormIssueLoggingConfiguration::noisy();
    }
}
