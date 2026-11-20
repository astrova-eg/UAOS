<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;

/**
 * Governs permissible source lifecycle states and deterministic transition rules.
 */
final class SourceLifecycleState
{
    public const STATE_ACTIVE = 'active';
    public const STATE_SUSPENDED = 'suspended';
    public const STATE_DEPRECATED = 'deprecated';
    public const STATE_REVOKED = 'revoked';
    public const STATE_ARCHIVED = 'archived';

    public const ALL_STATES = [
        self::STATE_ACTIVE,
        self::STATE_SUSPENDED,
        self::STATE_DEPRECATED,
        self::STATE_REVOKED,
        self::STATE_ARCHIVED,
    ];

    /**
     * Permitted state transitions matrix.
     * Revoked and Archived are terminal states.
     */
    private const TRANSITIONS = [
        self::STATE_ACTIVE => [
            self::STATE_SUSPENDED,
            self::STATE_DEPRECATED,
            self::STATE_REVOKED,
            self::STATE_ARCHIVED,
        ],
        self::STATE_SUSPENDED => [
            self::STATE_ACTIVE,
            self::STATE_DEPRECATED,
            self::STATE_REVOKED,
            self::STATE_ARCHIVED,
        ],
        self::STATE_DEPRECATED => [
            self::STATE_ACTIVE,
            self::STATE_REVOKED,
            self::STATE_ARCHIVED,
        ],
        self::STATE_REVOKED => [],
        self::STATE_ARCHIVED => [],
    ];

    public static function isValid(string $state): bool
    {
        return in_array($state, self::ALL_STATES, true);
    }

    public static function isTerminal(string $state): bool
    {
        return in_array($state, [self::STATE_REVOKED, self::STATE_ARCHIVED], true);
    }

    public static function canTransition(string $fromState, string $toState): bool
    {
        if ($fromState === $toState) {
            return true; // No-op transition is permitted
        }

        if (!isset(self::TRANSITIONS[$fromState])) {
            return false;
        }

        return in_array($toState, self::TRANSITIONS[$fromState], true);
    }

    public static function assertValidTransition(string $fromState, string $toState): void
    {
        if (!self::isValid($fromState)) {
            throw new InvalidArgumentException("Invalid origin lifecycle state: '{$fromState}'.");
        }
        if (!self::isValid($toState)) {
            throw new InvalidArgumentException("Invalid target lifecycle state: '{$toState}'.");
        }
        if (!self::canTransition($fromState, $toState)) {
            throw new InvalidArgumentException(
                "Illegal lifecycle transition from '{$fromState}' to '{$toState}'. "
                . (self::isTerminal($fromState) ? "State '{$fromState}' is terminal." : "")
            );
        }
    }
}
