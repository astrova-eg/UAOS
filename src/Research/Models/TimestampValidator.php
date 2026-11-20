<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Strict ISO-8601 Timestamp Validator for RDEI Contracts & Data Models.
 *
 * Enforces Schema Correction 003C temporal constraints:
 * - Requires explicit timezone (UTC 'Z' or numeric offset '[+-]HH:MM')
 * - Rejects permissive normalization, ambiguous text, date-only, and invalid calendar dates
 * - Verifies hour, minute, second, and timezone offset bounds
 */
final class TimestampValidator
{
    private const ISO8601_REGEX = '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:\.\d+)?(Z|[+-]\d{2}:\d{2})$/';

    public static function isValid(mixed $value): bool
    {
        if (!is_string($value) || $value === '') {
            return false;
        }

        if (!preg_match(self::ISO8601_REGEX, $value, $matches)) {
            return false;
        }

        $year = (int)$matches[1];
        $month = (int)$matches[2];
        $day = (int)$matches[3];
        $hour = (int)$matches[4];
        $minute = (int)$matches[5];
        $second = (int)$matches[6];

        if (!checkdate($month, $day, $year)) {
            return false;
        }

        if ($hour < 0 || $hour > 23 || $minute < 0 || $minute > 59 || $second < 0 || $second > 59) {
            return false;
        }

        if ($matches[7] !== 'Z') {
            $tzHours = (int)substr($matches[7], 1, 2);
            $tzMinutes = (int)substr($matches[7], 4, 2);
            if ($tzHours > 14 || $tzMinutes > 59) {
                return false;
            }
        }

        return true;
    }

    public static function assertValid(string $value, string $fieldName): void
    {
        if (!self::isValid($value)) {
            throw new InvalidArgumentException(
                "Invalid ISO-8601 timestamp for '{$fieldName}': '{$value}'. Must be a valid date with explicit timezone."
            );
        }
    }

    public static function toTimestamp(?string $value): ?int
    {
        if ($value === null || $value === '' || !self::isValid($value)) {
            return null;
        }

        try {
            return (new DateTimeImmutable($value))->getTimestamp();
        } catch (\Throwable) {
            return null;
        }
    }
}
