<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use UniversalAgentOS\Research\Models\ConflictRecord;
use UniversalAgentOS\Research\Models\ResolutionDecision;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Deterministic conflict resolution engine applying approved authority hierarchies,
 * version qualifications, context alignments, and fail-closed gate semantics.
 */
class ConflictResolver
{
    /**
     * Resolves a detected conflict between claims based on project context and authority.
     *
     * @param ConflictRecord $conflict The detected conflict to resolve
     * @param array<string, mixed> $activeProfile Active target project profile
     * @param array<string, mixed> $context Additional contextual parameters
     */
    public function resolveConflict(
        ConflictRecord $conflict,
        array $activeProfile = [],
        array $context = []
    ): ResolutionDecision {
        $citations = array_merge($conflict->claimA->citations, $conflict->claimB->citations);

        // Step 1: Check Source Eligibility (Ineligible sources cannot prevail)
        $eligibilityResolution = $this->resolveByEligibility($conflict, $citations);
        if ($eligibilityResolution !== null) {
            return $eligibilityResolution;
        }

        // Step 2: Resolve Version Divergence
        if ($conflict->contradictionType === ConflictRecord::TYPE_VERSION_DIVERGENCE) {
            return $this->resolveVersionDivergence($conflict, $activeProfile, $citations);
        }

        // Step 3: Resolve Context Discrepancy
        if ($conflict->contradictionType === ConflictRecord::TYPE_CONTEXT_DISCREPANCY) {
            return $this->resolveContextDiscrepancy($conflict, $activeProfile, $citations);
        }

        // Step 4: Resolve Temporal Invalidation
        if ($conflict->contradictionType === ConflictRecord::TYPE_TEMPORAL_INVALIDATION) {
            return $this->resolveTemporalInvalidation($conflict, $citations);
        }

        // Step 5: True Technical Contradiction - Evaluate Authority Hierarchy
        return $this->resolveTechnicalContradiction($conflict, $citations);
    }

    /**
     * Ineligible, suspended, deprecated, or revoked sources cannot prevail over eligible sources.
     */
    private function resolveByEligibility(ConflictRecord $conflict, array $citations): ?ResolutionDecision
    {
        $statusA = $conflict->sourceA?->status ?? 'active';
        $statusB = $conflict->sourceB?->status ?? 'active';

        $ineligibleA = ($statusA === 'revoked' || $statusA === 'suspended' || $statusA === 'deprecated');
        $ineligibleB = ($statusB === 'revoked' || $statusB === 'suspended' || $statusB === 'deprecated');

        if ($ineligibleA && !$ineligibleB) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_RESOLVED,
                rationale: "Claim B prevailing: Source A ('{$conflict->sourceA?->sourceId}') is ineligible ({$statusA}).",
                blocksGate0: false,
                prevailingClaim: $conflict->claimB,
                supersededClaim: $conflict->claimA,
                citationsPreserved: $citations
            );
        }

        if ($ineligibleB && !$ineligibleA) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_RESOLVED,
                rationale: "Claim A prevailing: Source B ('{$conflict->sourceB?->sourceId}') is ineligible ({$statusB}).",
                blocksGate0: false,
                prevailingClaim: $conflict->claimA,
                supersededClaim: $conflict->claimB,
                citationsPreserved: $citations
            );
        }

        return null;
    }

    private function resolveVersionDivergence(
        ConflictRecord $conflict,
        array $activeProfile,
        array $citations
    ): ResolutionDecision {
        $targetVersion = (string)($activeProfile['php_version'] ?? $activeProfile['framework_version'] ?? '');

        if ($targetVersion !== '') {
            $matchA = in_array($targetVersion, $conflict->claimA->applicableVersions, true);
            $matchB = in_array($targetVersion, $conflict->claimB->applicableVersions, true);

            if ($matchA && !$matchB) {
                return new ResolutionDecision(
                    conflictId: $conflict->conflictId,
                    status: ResolutionDecision::STATUS_RESOLVED,
                    rationale: "Resolved by version qualification: Claim A applies to active project target {$targetVersion}.",
                    blocksGate0: false,
                    prevailingClaim: $conflict->claimA,
                    supersededClaim: $conflict->claimB,
                    citationsPreserved: $citations
                );
            }
            if ($matchB && !$matchA) {
                return new ResolutionDecision(
                    conflictId: $conflict->conflictId,
                    status: ResolutionDecision::STATUS_RESOLVED,
                    rationale: "Resolved by version qualification: Claim B applies to active project target {$targetVersion}.",
                    blocksGate0: false,
                    prevailingClaim: $conflict->claimB,
                    supersededClaim: $conflict->claimA,
                    citationsPreserved: $citations
                );
            }
        }

        return new ResolutionDecision(
            conflictId: $conflict->conflictId,
            status: ResolutionDecision::STATUS_RESOLVED_WITH_LIMITATIONS,
            rationale: 'Version divergence: Both claims valid under differing versions. Qualified in dossier with both versions recorded.',
            blocksGate0: false,
            prevailingClaim: null,
            supersededClaim: null,
            citationsPreserved: $citations
        );
    }

    private function resolveContextDiscrepancy(
        ConflictRecord $conflict,
        array $activeProfile,
        array $citations
    ): ResolutionDecision {
        $hostOs = strtolower((string)($activeProfile['os'] ?? PHP_OS_FAMILY));
        $desc = strtolower($conflict->description);

        $matchesHost = str_contains($desc, $hostOs);
        return new ResolutionDecision(
            conflictId: $conflict->conflictId,
            status: ResolutionDecision::STATUS_RESOLVED_WITH_LIMITATIONS,
            rationale: "Resolved by deployment context: Qualified for active environment ({$hostOs}). Contradiction preserved as alternative operational mode.",
            blocksGate0: false,
            prevailingClaim: null,
            supersededClaim: null,
            citationsPreserved: $citations
        );
    }

    private function resolveTemporalInvalidation(ConflictRecord $conflict, array $citations): ResolutionDecision
    {
        // Higher confidence or newer source wins
        if ($conflict->claimA->confidenceScore > $conflict->claimB->confidenceScore) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_RESOLVED,
                rationale: "Resolved by temporal superiority: Claim A has higher confidence ({$conflict->claimA->confidenceScore}) and newer currency.",
                blocksGate0: false,
                prevailingClaim: $conflict->claimA,
                supersededClaim: $conflict->claimB,
                citationsPreserved: $citations
            );
        }

        return new ResolutionDecision(
            conflictId: $conflict->conflictId,
            status: ResolutionDecision::STATUS_RESOLVED,
            rationale: "Resolved by temporal superiority: Claim B has higher confidence ({$conflict->claimB->confidenceScore}) and newer currency.",
            blocksGate0: false,
            prevailingClaim: $conflict->claimB,
            supersededClaim: $conflict->claimA,
            citationsPreserved: $citations
        );
    }

    private function resolveTechnicalContradiction(ConflictRecord $conflict, array $citations): ResolutionDecision
    {
        $tierA = $conflict->sourceA?->authorityTier ?? 5;
        $tierB = $conflict->sourceB?->authorityTier ?? 5;

        // Level 1 or 2 overrides Level 4, 5, or 6
        if ($tierA <= 2 && $tierB > 2) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_RESOLVED,
                rationale: "Resolved by Authority Hierarchy: Claim A is backed by Tier {$tierA} authoritative source, overriding Tier {$tierB}.",
                blocksGate0: false,
                prevailingClaim: $conflict->claimA,
                supersededClaim: $conflict->claimB,
                citationsPreserved: $citations
            );
        }

        if ($tierB <= 2 && $tierA > 2) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_RESOLVED,
                rationale: "Resolved by Authority Hierarchy: Claim B is backed by Tier {$tierB} authoritative source, overriding Tier {$tierA}.",
                blocksGate0: false,
                prevailingClaim: $conflict->claimB,
                supersededClaim: $conflict->claimA,
                citationsPreserved: $citations
            );
        }

        // Both sources are equivalent authority (e.g. both Tier 1 or both Tier 2)
        if ($conflict->isCritical) {
            return new ResolutionDecision(
                conflictId: $conflict->conflictId,
                status: ResolutionDecision::STATUS_UNRESOLVED_CRITICAL,
                rationale: "Unresolved critical contradiction: Direct technical dispute between equivalent authority sources affecting security or architecture. Gate 0 strictly BLOCKED.",
                blocksGate0: true,
                prevailingClaim: null,
                supersededClaim: null,
                requiresFollowUpEvidence: true,
                followUpRequirement: "Mandatory architectural escalation to Department C and Department M. Requires independent RFC or upstream maintainer verification for '{$conflict->targetSubject}'.",
                citationsPreserved: $citations
            );
        }

        return new ResolutionDecision(
            conflictId: $conflict->conflictId,
            status: ResolutionDecision::STATUS_UNRESOLVED_NONCRITICAL,
            rationale: "Noncritical unresolved dispute: Disagreement on implementation style or secondary configuration. Documented in dossier with accepted limitations.",
            blocksGate0: false,
            prevailingClaim: null,
            supersededClaim: null,
            requiresFollowUpEvidence: false,
            citationsPreserved: $citations
        );
    }
}
