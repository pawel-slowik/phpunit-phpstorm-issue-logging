<?php

declare(strict_types=1);

namespace PawelSlowik\PhpunitPhpstormIssueLogging;

use PHPUnit\TextUI\Configuration\Configuration;

final class PhpstormIssueLoggingConfiguration
{
    private function __construct(
        public readonly bool $logWarning,
        public readonly bool $logPhpWarning,
        public readonly bool $logPhpunitWarning,
        public readonly bool $logNotice,
        public readonly bool $logPhpNotice,
        public readonly bool $logPhpunitNotice,
        public readonly bool $logDeprecation,
        public readonly bool $logPhpDeprecation,
        public readonly bool $logPhpunitDeprecation,
    ) {
    }

    public static function noisy(): self
    {
        return new self(true, true, true, true, true, true, true, true, true);
    }

    public static function fromExitCode(Configuration $configuration): self
    {
        // PHPUnit configuration method availability:
        //
        // failOnWarning             |  10.0.0   |  11.0.0   |  12.0.0
        // failOnNotice              |  10.1.0   |  11.0.0   |  12.0.0
        // failOnDeprecation         |  10.1.0   |  11.0.0   |  12.0.0
        // failOnPhpunitWarning      |  10.5.47  |  11.5.24  |  12.2.3
        // failOnPhpunitNotice       |    N/A    |    N/A    |  12.1.0
        // failOnPhpunitDeprecation  |  10.5.32  |  11.3.3   |  12.0.0
        // failOnAllIssues           |  10.5.46  |  11.5.19  |  12.1.4

        $failOnWarning = $configuration->failOnWarning();
        $failOnNotice = method_exists($configuration, 'failOnNotice')
            ? $configuration->failOnNotice()
            : false;
        $failOnDeprecation = method_exists($configuration, 'failOnDeprecation')
            ? $configuration->failOnDeprecation()
            : false;

        // do not copy failOnPhpunit* settings from their failOn* counterparts,
        // they are different by design
        // https://github.com/sebastianbergmann/phpunit/issues/6236#issuecomment-2969632187

        // default to true to mimic the behaviour of older versions
        $failOnPhpunitWarning = method_exists($configuration, 'failOnPhpunitWarning')
            ? $configuration->failOnPhpunitWarning()
            : true;

        // default to false because PHPUnit notices didn't exists in older
        // versions
        $failOnPhpunitNotice = method_exists($configuration, 'failOnPhpunitNotice')
            ? $configuration->failOnPhpunitNotice()
            : false;

        // default to true to mimic the behaviour of older versions
        $failOnPhpunitDeprecation = method_exists($configuration, 'failOnPhpunitDeprecation')
            ? $configuration->failOnPhpunitDeprecation()
            : true;

        if (method_exists($configuration, 'failOnAllIssues') && $configuration->failOnAllIssues()) {
            $failOnWarning = true;
            $failOnNotice = true;
            $failOnDeprecation = true;
            $failOnPhpunitWarning = true;
            $failOnPhpunitNotice = true;
            $failOnPhpunitDeprecation = true;
        }

        return new self(
            $failOnWarning,
            $failOnWarning,
            $failOnPhpunitWarning,
            $failOnNotice,
            $failOnNotice,
            $failOnPhpunitNotice,
            $failOnDeprecation,
            $failOnDeprecation,
            $failOnPhpunitDeprecation,
        );
    }

    /**
     * @param string[] $issueTypes
     */
    public static function fromIssueTypes(array $issueTypes): self
    {
        $issueTypes = array_map(strtolower(...), $issueTypes);

        return new self(
            self::keywordExists('warning', $issueTypes),
            self::keywordExists('php_warning', $issueTypes),
            self::keywordExists('phpunit_warning', $issueTypes),
            self::keywordExists('notice', $issueTypes),
            self::keywordExists('php_notice', $issueTypes),
            self::keywordExists('phpunit_notice', $issueTypes),
            self::keywordExists('deprecation', $issueTypes),
            self::keywordExists('php_deprecation', $issueTypes),
            self::keywordExists('phpunit_deprecation', $issueTypes),
        );
    }

    /**
     * @param string[] $haystack
     */
    private static function keywordExists(string $needle, array $haystack): bool
    {
        return in_array($needle, $haystack, true) || in_array(str_replace('_', '', $needle), $haystack, true);
    }
}
