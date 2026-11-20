<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests;

/**
 * Zero-dependency assertion library providing rigorous test assertions and failure tracking.
 */
class Assert
{
    private static int $passCount = 0;
    private static int $failCount = 0;
    /** @var array<int, string> */
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

    public static function assertCount(int $expectedCount, array|\Countable $countable, string $message): void
    {
        $actual = count($countable);
        self::assertTrue($actual === $expectedCount, "{$message} (Expected count {$expectedCount}, got {$actual})");
    }

    /**
     * @param class-string<\Throwable> $expectedExceptionClass
     */
    public static function assertThrows(string $expectedExceptionClass, callable $callback, string $message): void
    {
        try {
            $callback();
            self::assertTrue(false, "{$message} (Expected exception {$expectedExceptionClass} was not thrown)");
        } catch (\Throwable $e) {
            $isInstanceOf = is_a($e, $expectedExceptionClass);
            self::assertTrue($isInstanceOf, "{$message} (Caught " . get_class($e) . " instead of {$expectedExceptionClass}: " . $e->getMessage() . ")");
        }
    }

    public static function reset(): void
    {
        self::$passCount = 0;
        self::$failCount = 0;
        self::$failures = [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getStats(): array
    {
        return [
            'passed' => self::$passCount,
            'failed' => self::$failCount,
            'failures' => self::$failures,
        ];
    }
}
