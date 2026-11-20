<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use UniversalAgentOS\Research\Contracts\FreshnessEvaluatorInterface;
use UniversalAgentOS\Research\Contracts\TrustEvaluatorInterface;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TrustEvaluationResult;
use UniversalAgentOS\Research\Models\TrustPolicyConfiguration;

/**
 * Explainable, deterministic trust evaluation engine implementing approved linear scoring models.
 */
class TrustIntelligenceEngine implements TrustEvaluatorInterface
{
    private TrustPolicyConfiguration $policy;
    private FreshnessEvaluatorInterface $freshnessEngine;

    public function __construct(
        ?TrustPolicyConfiguration $policy = null,
        ?FreshnessEvaluatorInterface $freshnessEngine = null
    ) {
        $this->policy = $policy ?? TrustPolicyConfiguration::createDefault();
        $this->freshnessEngine = $freshnessEngine ?? new FreshnessIntelligenceEngine($this->policy);
    }

    public function calculateAuthorityScore(SourceDefinition $source): float
    {
        $tier = $source->authorityTier;
        return TrustPolicyConfiguration::AUTHORITY_TIER_SCORES[$tier] ?? 0.05;
    }

    public function evaluateClaimConfidence(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): float {
        $assessment = $this->assessTrust($claim, $source, $context);
        return (float)$assessment['confidence_score'];
    }

    public function classifyEvidentialState(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): string {
        $score = $this->evaluateClaimConfidence($claim, $source, $context);
        $conflicts = $context['conflicts'] ?? [];
        if (count($conflicts) > 0) {
            return 'UNRESOLVED_DISPUTE';
        }

        if ($score >= $this->policy->thresholdHigh) {
            return ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE;
        }
        if ($score >= $this->policy->thresholdModerate) {
            return ExtractedClaim::STATUS_CORROBORATED_PRACTICE;
        }
        if ($score >= $this->policy->thresholdLow) {
            return 'CONDITIONALLY_VERIFIED';
        }

        return ExtractedClaim::STATUS_UNVERIFIED_SPECULATION;
    }

    public function assessTrust(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): array {
        $authScore = $this->calculateAuthorityScore($source);

        // Context / Version match
        $versionMatch = isset($context['version_match']) && is_numeric($context['version_match'])
            ? (float)$context['version_match']
            : (count($claim->applicableVersions) > 0 ? 1.00 : 0.50);

        // Freshness
        $freshResult = $this->freshnessEngine->evaluateFreshness($source->lastVerifiedAt, $source, $context);
        $freshScore = $freshResult->freshnessScore;

        // Corroboration / Verification
        $corroboratingSources = (array)($context['corroborating_sources'] ?? []);
        $nCorr = count($corroboratingSources);
        $corrScore = min(1.0, 0.50 * $nCorr);

        // Dispute penalty
        $conflicts = (array)($context['conflicts'] ?? []);
        $penalty = count($conflicts) > 0 ? 0.25 : 0.00;

        $rawScore = ($this->policy->weightAuthority * $authScore)
            + ($this->policy->weightEvidenceQuality * $versionMatch)
            + ($this->policy->weightFreshness * $freshScore)
            + ($this->policy->weightVerificationStatus * $corrScore)
            - $penalty;

        $clampedScore = max(0.0, min(1.0, round($rawScore, 4)));
        $tier = $this->policy->resolveConfidenceTier($clampedScore);

        return [
            'authority_score' => $authScore,
            'evidence_quality_score' => $versionMatch,
            'freshness_score' => $freshScore,
            'verification_status_score' => $corrScore,
            'conflict_penalty' => $penalty,
            'confidence_score' => $clampedScore,
            'confidence_tier' => $tier,
            'policy_version' => $this->policy->policyVersion,
        ];
    }

    /**
     * Evaluates trust of a SourceDefinition directly with full explainability.
     */
    public function evaluateSourceTrust(SourceDefinition $source, array $context = []): TrustEvaluationResult
    {
        $authScore = $this->calculateAuthorityScore($source);
        $explanations = [
            'authority' => "Tier {$source->authorityTier} authority provides base authority score of {$authScore}.",
        ];
        $missingFactors = [];
        $warnings = [];

        // Evidence quality based on documentation and classification evidence
        $evidenceQuality = 0.0;
        if (trim($source->classificationEvidence) !== '') {
            $evidenceQuality += 0.50;
        } else {
            $missingFactors[] = 'classification_evidence';
            $warnings[] = 'Source lacks documentary classification evidence.';
        }
        if (!empty($source->owner['institution_name'])) {
            $evidenceQuality += 0.50;
        } else {
            $missingFactors[] = 'owner_institution';
        }
        $explanations['evidence_quality'] = "Evidence quality scored {$evidenceQuality} based on institutional and classification backing.";

        // Freshness
        $freshResult = $this->freshnessEngine->evaluateFreshness($source->lastVerifiedAt, $source, $context);
        $freshScore = $freshResult->freshnessScore;
        $explanations['freshness'] = "Freshness state is '{$freshResult->state}' (score {$freshScore}) based on age {$freshResult->ageHours}h vs TTL {$freshResult->effectiveTtlHours}h.";
        if ($freshResult->isExpired() || $freshResult->isStale()) {
            $warnings[] = "Source evidence is {$freshResult->state}.";
        }

        // Verification status across access mechanisms
        $verificationScore = $this->calculateMechanismsVerificationScore($source, $missingFactors, $warnings);
        $explanations['verification_status'] = "Access mechanisms verification status aggregate score: {$verificationScore}.";

        // Dispute penalty
        $penalty = 0.0;
        if ($source->status === SourceDefinition::STATUS_UNREACHABLE) {
            $penalty = 0.50;
            $warnings[] = 'Source is marked unreachable.';
        } elseif ($source->status === SourceDefinition::STATUS_CREDENTIALS_MISSING) {
            $penalty = 0.25;
            $warnings[] = 'Source authentication credentials missing from environment.';
        }

        $rawComposite = ($this->policy->weightAuthority * $authScore)
            + ($this->policy->weightEvidenceQuality * $evidenceQuality)
            + ($this->policy->weightFreshness * $freshScore)
            + ($this->policy->weightVerificationStatus * $verificationScore)
            - $penalty;

        $clampedComposite = max(0.0, min(1.0, round($rawComposite, 4)));
        $tier = $this->policy->resolveConfidenceTier($clampedComposite);

        $now = isset($context['reference_time']) && is_int($context['reference_time'])
            ? $context['reference_time']
            : time();

        return new TrustEvaluationResult(
            sourceId: $source->sourceId,
            authorityScore: $authScore,
            evidenceQualityScore: $evidenceQuality,
            freshnessScore: $freshScore,
            verificationStatusScore: $verificationScore,
            conflictPenalty: $penalty,
            compositeScore: $clampedComposite,
            confidenceTier: $tier,
            policyVersion: $this->policy->policyVersion,
            evaluatedAt: gmdate('Y-m-d\TH:i:s\Z', $now),
            factorExplanations: $explanations,
            missingFactors: $missingFactors,
            warnings: $warnings
        );
    }

    private function calculateMechanismsVerificationScore(
        SourceDefinition $source,
        array &$missingFactors,
        array &$warnings
    ): float {
        $mechs = $source->accessMechanisms;
        if (count($mechs) === 0) {
            $missingFactors[] = 'access_mechanisms';
            $warnings[] = 'Source has zero access mechanisms defined.';
            return 0.0;
        }

        $totalScore = 0.0;
        foreach ($mechs as $m) {
            $st = $m['verification_status'] ?? 'untested';
            $totalScore += TrustPolicyConfiguration::VERIFICATION_STATUS_SCORES[$st] ?? 0.00;
        }

        return round($totalScore / count($mechs), 4);
    }
}
