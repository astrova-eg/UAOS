<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable value object holding evidence freshness and temporal state.
 */
readonly class FreshnessEvaluationResult implements JsonSerializable
{
    public const STATE_CURRENT = 'current';
    public const STATE_AGING = 'aging';
    public const STATE_STALE = 'stale';
    public const STATE_EXPIRED = 'expired';
    public const STATE_UNVERIFIED = 'unverified';
    public const STATE_INVALID = 'invalid';

    public function __construct(
        public float $freshnessScore,
        public string $state,
        public int $effectiveTtlHours,
        public float $ageHours,
        public string $evaluatedAt,
        public ?string $evidenceTimestamp = null,
        public ?string $expiresAt = null,
        public array $reasons = []
    ) {}

    public function isCurrent(): bool
    {
        return $this->state === self::STATE_CURRENT;
    }

    public function isAging(): bool
    {
        return $this->state === self::STATE_AGING;
    }

    public function isStale(): bool
    {
        return $this->state === self::STATE_STALE;
    }

    public function isExpired(): bool
    {
        return $this->state === self::STATE_EXPIRED;
    }

    public function isUnverified(): bool
    {
        return $this->state === self::STATE_UNVERIFIED;
    }

    public function isUsable(): bool
    {
        return in_array($this->state, [self::STATE_CURRENT, self::STATE_AGING], true);
    }

    public function toArray(): array
    {
        return [
            'freshness_score' => $this->freshnessScore,
            'state' => $this->state,
            'effective_ttl_hours' => $this->effectiveTtlHours,
            'age_hours' => $this->ageHours,
            'evaluated_at' => $this->evaluatedAt,
            'evidence_timestamp' => $this->evidenceTimestamp,
            'expires_at' => $this->expiresAt,
            'reasons' => $this->reasons,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
