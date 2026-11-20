<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable value object holding the explainable breakdown of a trust evaluation.
 */
readonly class TrustEvaluationResult implements JsonSerializable
{
    /**
     * @param string $sourceId Evaluated source identity
     * @param float $authorityScore Weighted source authority component [0.0, 1.0]
     * @param float $evidenceQualityScore Weighted evidence quality / context match [0.0, 1.0]
     * @param float $freshnessScore Weighted freshness score [0.0, 1.0]
     * @param float $verificationStatusScore Weighted verification status [0.0, 1.0]
     * @param float $conflictPenalty Penalty deducted for disputes or conflicts [0.0, 0.50]
     * @param float $compositeScore Final clamped trust score [0.0, 1.0]
     * @param string $confidenceTier High, Moderate, Low, or Insufficient
     * @param string $policyVersion Policy version applied
     * @param string $evaluatedAt ISO-8601 evaluation timestamp
     * @param array<string, string> $factorExplanations Detailed reasoning per factor
     * @param array<int, string> $missingFactors List of factors where evidence was absent
     * @param array<int, string> $warnings Any diagnostic warnings or caveats
     */
    public function __construct(
        public string $sourceId,
        public float $authorityScore,
        public float $evidenceQualityScore,
        public float $freshnessScore,
        public float $verificationStatusScore,
        public float $conflictPenalty,
        public float $compositeScore,
        public string $confidenceTier,
        public string $policyVersion,
        public string $evaluatedAt,
        public array $factorExplanations = [],
        public array $missingFactors = [],
        public array $warnings = []
    ) {}

    public function isHighConfidence(): bool
    {
        return $this->confidenceTier === 'HIGH';
    }

    public function isModerateConfidence(): bool
    {
        return $this->confidenceTier === 'MODERATE';
    }

    public function isInsufficient(): bool
    {
        return $this->confidenceTier === 'INSUFFICIENT';
    }

    public function toArray(): array
    {
        return [
            'source_id' => $this->sourceId,
            'authority_score' => $this->authorityScore,
            'evidence_quality_score' => $this->evidenceQualityScore,
            'freshness_score' => $this->freshnessScore,
            'verification_status_score' => $this->verificationStatusScore,
            'conflict_penalty' => $this->conflictPenalty,
            'composite_score' => $this->compositeScore,
            'confidence_tier' => $this->confidenceTier,
            'policy_version' => $this->policyVersion,
            'evaluated_at' => $this->evaluatedAt,
            'factor_explanations' => $this->factorExplanations,
            'missing_factors' => $this->missingFactors,
            'warnings' => $this->warnings,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
