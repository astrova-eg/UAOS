<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use UniversalAgentOS\Research\Contracts\FreshnessEvaluatorInterface;
use UniversalAgentOS\Research\Contracts\SourceEligibilityEvaluatorInterface;
use UniversalAgentOS\Research\Contracts\SourceValidatorInterface;
use UniversalAgentOS\Research\Contracts\TrustEvaluatorInterface;
use UniversalAgentOS\Research\Models\FreshnessEvaluationResult;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;
use UniversalAgentOS\Research\Models\SourceLifecycleState;
use UniversalAgentOS\Research\Models\TrustPolicyConfiguration;
use UniversalAgentOS\Research\Validation\SourceDefinitionValidator;

/**
 * Enforces governed source eligibility policies and hard security gates.
 */
class SourceEligibilityEvaluator implements SourceEligibilityEvaluatorInterface
{
    private TrustPolicyConfiguration $policy;
    private SourceValidatorInterface $validator;
    private TrustIntelligenceEngine $trustEngine;
    private FreshnessEvaluatorInterface $freshnessEngine;

    public function __construct(
        ?TrustPolicyConfiguration $policy = null,
        ?SourceValidatorInterface $validator = null,
        ?TrustIntelligenceEngine $trustEngine = null,
        ?FreshnessEvaluatorInterface $freshnessEngine = null
    ) {
        $this->policy = $policy ?? TrustPolicyConfiguration::createDefault();
        $this->validator = $validator ?? new SourceDefinitionValidator();
        $this->trustEngine = $trustEngine ?? new TrustIntelligenceEngine($this->policy);
        $this->freshnessEngine = $freshnessEngine ?? new FreshnessIntelligenceEngine($this->policy);
    }

    public function evaluateEligibility(SourceDefinition $source, array $context = []): SourceEligibilityDecision
    {
        $now = isset($context['reference_time']) && is_int($context['reference_time'])
            ? $context['reference_time']
            : time();
        $evaluatedAt = gmdate('Y-m-d\TH:i:s\Z', $now);

        $reasons = [];
        $violations = [];
        $conditions = [];

        // 1. Hard Check: Lifecycle status override
        $lifecycleState = $context['lifecycle_state'] ?? $source->status;

        if ($lifecycleState === SourceLifecycleState::STATE_REVOKED) {
            $violations[] = "Source '{$source->sourceId}' is in REVOKED lifecycle state.";
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_REVOKED,
                isEligible: false,
                trustScore: 0.0,
                confidenceTier: 'INSUFFICIENT',
                freshnessScore: 0.0,
                freshnessState: FreshnessEvaluationResult::STATE_UNVERIFIED,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: ['Source has been formally revoked due to security or governance breach.'],
                violations: $violations
            );
        }

        if ($lifecycleState === SourceLifecycleState::STATE_SUSPENDED) {
            $violations[] = "Source '{$source->sourceId}' is currently SUSPENDED.";
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_SUSPENDED,
                isEligible: false,
                trustScore: 0.0,
                confidenceTier: 'INSUFFICIENT',
                freshnessScore: 0.0,
                freshnessState: FreshnessEvaluationResult::STATE_UNVERIFIED,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: ['Source is temporarily suspended pending administrative review.'],
                violations: $violations
            );
        }

        if ($lifecycleState === SourceLifecycleState::STATE_DEPRECATED || $source->status === SourceDefinition::STATUS_DEPRECATED) {
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_DEPRECATED,
                isEligible: false,
                trustScore: 0.20,
                confidenceTier: 'INSUFFICIENT',
                freshnessScore: 0.10,
                freshnessState: FreshnessEvaluationResult::STATE_STALE,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: ['Source is deprecated; permitted only for historical audit, ineligible for new tasks.'],
                violations: ['Source marked deprecated.']
            );
        }

        // 2. Hard Check: Definition validation
        $valErrors = $this->validator->validate($source);
        if (count($valErrors) > 0) {
            foreach ($valErrors as $err) {
                $violations[] = "Validation defect: {$err}";
            }
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_INELIGIBLE,
                isEligible: false,
                trustScore: 0.0,
                confidenceTier: 'INSUFFICIENT',
                freshnessScore: 0.0,
                freshnessState: FreshnessEvaluationResult::STATE_INVALID,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: ['Source definition failed canonical schema validation.'],
                violations: $violations
            );
        }

        // 3. Evaluate Freshness
        $domain = $source->domains[0] ?? 'A';
        $freshContext = array_merge(['domain' => $domain], $context);
        $freshResult = $this->freshnessEngine->evaluateFreshness($source->lastVerifiedAt, $source, $freshContext);
        $freshScore = $freshResult->freshnessScore;
        $freshState = $freshResult->state;

        if ($freshResult->isExpired()) {
            $violations[] = "Source evidence has expired (age: {$freshResult->ageHours}h exceeds limit).";
        }

        // 4. Evaluate Trust
        $trustResult = $this->trustEngine->evaluateSourceTrust($source, $freshContext);
        $trustScore = $trustResult->compositeScore;
        $tier = $trustResult->confidenceTier;

        // 5. Hard Policy: Secret Hygiene check
        if (($source->authProfile['requires_auth'] ?? false) === true) {
            $envKey = (string)($source->authProfile['env_var_key'] ?? '');
            if (ctype_xdigit($envKey) && strlen($envKey) >= 24) {
                $violations[] = 'Hard policy violation: raw credential detected in auth_profile.';
            }
        }

        // 6. Hard Check: Expired or stale evidence in critical security domains (C, B)
        if (in_array($domain, ['B', 'C'], true) && ($freshResult->isExpired() || $freshResult->isStale())) {
            $violations[] = "Critical security domain '{$domain}' forbids stale or expired evidence (age: {$freshResult->ageHours}h exceeds TTL {$freshResult->effectiveTtlHours}h).";
        }

        if (count($violations) > 0) {
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_INELIGIBLE,
                isEligible: false,
                trustScore: $trustScore,
                confidenceTier: $tier,
                freshnessScore: $freshScore,
                freshnessState: $freshState,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: ['Hard policy or freshness violations prevent source eligibility.'],
                violations: $violations
            );
        }

        // 7. Tier-based Eligibility Determination
        if ($tier === 'HIGH' || $tier === 'MODERATE') {
            $reasons[] = "Trust score {$trustScore} ({$tier}) meets eligibility threshold with {$freshState} evidence.";
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_ELIGIBLE,
                isEligible: true,
                trustScore: $trustScore,
                confidenceTier: $tier,
                freshnessScore: $freshScore,
                freshnessState: $freshState,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: $reasons
            );
        }

        if ($tier === 'LOW') {
            $conditions[] = 'Mandatory secondary corroboration required before accepting claims.';
            if ($freshResult->isAging()) {
                $conditions[] = 'Evidence is aging; upstream revalidation recommended.';
            }
            $reasons[] = "Trust score {$trustScore} (LOW) qualifies for conditional eligibility only.";
            return new SourceEligibilityDecision(
                sourceId: $source->sourceId,
                status: SourceEligibilityDecision::STATUS_CONDITIONALLY_ELIGIBLE,
                isEligible: true,
                trustScore: $trustScore,
                confidenceTier: $tier,
                freshnessScore: $freshScore,
                freshnessState: $freshState,
                policyVersion: $this->policy->policyVersion,
                evaluatedAt: $evaluatedAt,
                reasons: $reasons,
                conditions: $conditions
            );
        }

        // INSUFFICIENT
        $reasons[] = "Trust score {$trustScore} is INSUFFICIENT (< {$this->policy->thresholdLow}).";
        return new SourceEligibilityDecision(
            sourceId: $source->sourceId,
            status: SourceEligibilityDecision::STATUS_INELIGIBLE,
            isEligible: false,
            trustScore: $trustScore,
            confidenceTier: $tier,
            freshnessScore: $freshScore,
            freshnessState: $freshState,
            policyVersion: $this->policy->policyVersion,
            evaluatedAt: $evaluatedAt,
            reasons: $reasons
        );
    }
}
