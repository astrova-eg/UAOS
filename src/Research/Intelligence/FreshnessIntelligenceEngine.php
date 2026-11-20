<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use UniversalAgentOS\Research\Contracts\FreshnessEvaluatorInterface;
use UniversalAgentOS\Research\Models\FreshnessEvaluationResult;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TimestampValidator;
use UniversalAgentOS\Research\Models\TrustPolicyConfiguration;

/**
 * Deterministic engine for evaluating evidence freshness and cache boundaries.
 */
class FreshnessIntelligenceEngine implements FreshnessEvaluatorInterface
{
    private TrustPolicyConfiguration $policy;

    public function __construct(?TrustPolicyConfiguration $policy = null)
    {
        $this->policy = $policy ?? TrustPolicyConfiguration::createDefault();
    }

    public function evaluateFreshness(
        ?string $timestamp,
        ?SourceDefinition $source = null,
        array $context = []
    ): FreshnessEvaluationResult {
        $now = isset($context['reference_time']) && is_int($context['reference_time'])
            ? $context['reference_time']
            : time();

        $evaluatedAt = gmdate('Y-m-d\TH:i:s\Z', $now);
        $effectiveTtl = $this->resolveEffectiveTtlHours($source, $context);

        // Case 1: Missing or empty timestamp
        if ($timestamp === null || trim($timestamp) === '') {
            return new FreshnessEvaluationResult(
                freshnessScore: 0.0,
                state: FreshnessEvaluationResult::STATE_UNVERIFIED,
                effectiveTtlHours: $effectiveTtl,
                ageHours: 0.0,
                evaluatedAt: $evaluatedAt,
                evidenceTimestamp: null,
                expiresAt: null,
                reasons: ['No verification timestamp provided (unverified evidence).']
            );
        }

        // Case 2: Malformed or calendar-invalid ISO-8601 timestamp
        if (!TimestampValidator::isValid($timestamp)) {
            return new FreshnessEvaluationResult(
                freshnessScore: 0.0,
                state: FreshnessEvaluationResult::STATE_INVALID,
                effectiveTtlHours: $effectiveTtl,
                ageHours: 0.0,
                evaluatedAt: $evaluatedAt,
                evidenceTimestamp: $timestamp,
                expiresAt: null,
                reasons: ["Timestamp '{$timestamp}' is not a valid ISO-8601 calendar date/time."]
            );
        }

        $timeEpoch = strtotime($timestamp);
        if ($timeEpoch === false) {
            return new FreshnessEvaluationResult(
                freshnessScore: 0.0,
                state: FreshnessEvaluationResult::STATE_INVALID,
                effectiveTtlHours: $effectiveTtl,
                ageHours: 0.0,
                evaluatedAt: $evaluatedAt,
                evidenceTimestamp: $timestamp,
                expiresAt: null,
                reasons: ["Timestamp '{$timestamp}' failed UNIX epoch conversion."]
            );
        }

        // Case 3: Future timestamp (beyond 5 min clock skew)
        if ($timeEpoch > $now + 300) {
            return new FreshnessEvaluationResult(
                freshnessScore: 0.0,
                state: FreshnessEvaluationResult::STATE_INVALID,
                effectiveTtlHours: $effectiveTtl,
                ageHours: 0.0,
                evaluatedAt: $evaluatedAt,
                evidenceTimestamp: $timestamp,
                expiresAt: null,
                reasons: ["Timestamp '{$timestamp}' is in the future relative to evaluation time."]
            );
        }

        $ageSeconds = max(0, $now - $timeEpoch);
        $ageHours = round($ageSeconds / 3600.0, 2);
        $expiresEpoch = $timeEpoch + ($effectiveTtl * 3600);
        $expiresAt = gmdate('Y-m-d\TH:i:s\Z', $expiresEpoch);

        // Calculate score and state based on age vs effective TTL
        $ratio = $effectiveTtl > 0 ? ($ageHours / (float)$effectiveTtl) : 999.0;

        if ($ratio <= 0.75) {
            $state = FreshnessEvaluationResult::STATE_CURRENT;
            $score = 1.00;
            $reasons = ["Evidence is current (age: {$ageHours}h, effective TTL: {$effectiveTtl}h)."];
        } elseif ($ratio <= 1.0) {
            $state = FreshnessEvaluationResult::STATE_AGING;
            $decayFraction = ($ratio - 0.75) / 0.25;
            $score = round(1.00 - (0.15 * $decayFraction), 4);
            $reasons = ["Evidence is aging towards TTL boundary (age: {$ageHours}h, effective TTL: {$effectiveTtl}h)."];
        } elseif ($ratio <= 2.0) {
            $state = FreshnessEvaluationResult::STATE_STALE;
            $decayFraction = ($ratio - 1.0) / 1.0;
            $score = round(0.85 - (0.35 * $decayFraction), 4);
            $reasons = ["Evidence is stale and past nominal TTL (age: {$ageHours}h exceeds TTL {$effectiveTtl}h)."];
        } else {
            $state = FreshnessEvaluationResult::STATE_EXPIRED;
            $multiplier = exp(-0.5 * ($ratio - 2.0));
            $score = max(0.05, round(0.50 * $multiplier, 4));
            $reasons = ["Evidence has expired (age: {$ageHours}h exceeds double TTL {$effectiveTtl}h)."];
        }

        return new FreshnessEvaluationResult(
            freshnessScore: $score,
            state: $state,
            effectiveTtlHours: $effectiveTtl,
            ageHours: $ageHours,
            evaluatedAt: $evaluatedAt,
            evidenceTimestamp: $timestamp,
            expiresAt: $expiresAt,
            reasons: $reasons
        );
    }

    public function resolveEffectiveTtlHours(?SourceDefinition $source = null, array $context = []): int
    {
        // 1. Task/Context override has highest priority
        if (isset($context['ttl_override']) && is_int($context['ttl_override'])) {
            return max(0, $context['ttl_override']);
        }

        $domain = $context['domain'] ?? ($source?->domains[0] ?? 'A');
        $domainTtl = $this->policy->getDomainTtl((string)$domain, 168);

        // 2. Source-level freshness policy override (cannot exceed domain ceiling for critical security domains C/B)
        if ($source !== null && isset($source->freshnessPolicy['max_cache_ttl_hours']) && is_int($source->freshnessPolicy['max_cache_ttl_hours'])) {
            $sourceTtl = max(1, $source->freshnessPolicy['max_cache_ttl_hours']);
            if (in_array(strtoupper((string)$domain), ['B', 'C'], true)) {
                return min($domainTtl, $sourceTtl);
            }
            return $sourceTtl;
        }

        // 3. Domain-specific default TTL
        return $domainTtl;
    }
}
