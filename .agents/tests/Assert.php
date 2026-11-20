<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

final class Assert
{
    private static int $passCount = 0;
    private static int $failCount = 0;
    /** @var list<string> */
    private static array $failures = [];

    public static function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            self::$passCount++;
        } else {
            self::$failCount++;
            self::$failures[] = "FAILED: {$message}";
            echo "  [FAIL] {$message}\n";
        }
    }

    public static function assertFalse(bool $condition, string $message): void
    {
        self::assertTrue(!$condition, $message);
    }

    public static function assertEquals(mixed $expected, mixed $actual, string $message): void
    {
        $match = ($expected === $actual);
        self::assertTrue($match, "{$message} (Expected: " . json_encode($expected) . ", Actual: " . json_encode($actual) . ")");
    }

    public static function assertStringContains(string $needle, string $haystack, string $message): void
    {
        $match = str_contains($haystack, $needle);
        self::assertTrue($match, "{$message} (Needle '{$needle}' not found in haystack)");
    }

    public static function reset(): void
    {
        self::$passCount = 0;
        self::$failCount = 0;
        self::$failures = [];
    }

    public static function getStats(): array
    {
        return [
            'passed' => self::$passCount,
            'failed' => self::$failCount,
            'failures' => self::$failures
        ];
    }
}
