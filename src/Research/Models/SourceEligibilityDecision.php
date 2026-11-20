<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable value object holding the governed eligibility decision for a knowledge source.
 */
readonly class SourceEligibilityDecision implements JsonSerializable
{
    public const STATUS_ELIGIBLE = 'eligible';
    public const STATUS_CONDITIONALLY_ELIGIBLE = 'conditionally_eligible';
    public const STATUS_INELIGIBLE = 'ineligible';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_DEPRECATED = 'deprecated';
    public const STATUS_REVOKED = 'revoked';

    public const ALL_STATUSES = [
        self::STATUS_ELIGIBLE,
        self::STATUS_CONDITIONALLY_ELIGIBLE,
        self::STATUS_INELIGIBLE,
        self::STATUS_SUSPENDED,
        self::STATUS_DEPRECATED,
        self::STATUS_REVOKED,
    ];

    /**
     * @param string $sourceId Source identifier
     * @param string $status Final eligibility status
     * @param bool $isEligible Whether source can be used in active tasks
     * @param float $trustScore Numerical trust score [0.0, 1.0]
     * @param string $confidenceTier High, Moderate, Low, or Insufficient
     * @param float $freshnessScore Freshness score [0.0, 1.0]
     * @param string $freshnessState Current, aging, stale, expired, or unverified
     * @param string $policyVersion Policy version governing evaluation
     * @param string $evaluatedAt ISO-8601 evaluation timestamp
     * @param array<int, string> $reasons Primary factors determining eligibility
     * @param array<int, string> $violations Any hard policy or security violations
     * @param array<int, string> $conditions Specific requirements if conditionally eligible
     */
    public function __construct(
        public string $sourceId,
        public string $status,
        public bool $isEligible,
        public float $trustScore,
        public string $confidenceTier,
        public float $freshnessScore,
        public string $freshnessState,
        public string $policyVersion,
        public string $evaluatedAt,
        public array $reasons = [],
        public array $violations = [],
        public array $conditions = []
    ) {}

    public function isFullyEligible(): bool
    {
        return $this->status === self::STATUS_ELIGIBLE;
    }

    public function isConditionallyEligible(): bool
    {
        return $this->status === self::STATUS_CONDITIONALLY_ELIGIBLE;
    }

    public function isRevoked(): bool
    {
        return $this->status === self::STATUS_REVOKED;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    public function isDeprecated(): bool
    {
        return $this->status === self::STATUS_DEPRECATED;
    }

    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'status' => $this->status,
            'is_eligible' => $this->isEligible,
            'trust_score' => $this->trustScore,
            'confidence_tier' => $this->confidenceTier,
            'freshness_score' => $this->freshnessScore,
            'freshness_state' => $this->freshnessState,
            'policy_version' => $this->policyVersion,
            'evaluated_at' => $this->evaluatedAt,
            'reasons' => $this->reasons,
            'violations' => $this->violations,
            'conditions' => $this->conditions,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
