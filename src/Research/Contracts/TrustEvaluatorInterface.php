<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Trust and evidence quality evaluation contract.
 *
 * Enforces strict separation between Source Authority (publisher weight)
 * and Claim Confidence (evidential support, version alignment, freshness, corroboration).
 */
interface TrustEvaluatorInterface
{
    /**
     * Calculates the normalized source authority score based on the Six-Tier Hierarchy.
     *
     * Tier weights: L1 = 1.00, L2 = 0.85, L3 = 0.70, L4 = 0.55, L5 = 0.30, L6 = 0.05.
     *
     * @param SourceDefinition $source Authoritative source definition
     * @return float Authority score bounded in [0.0, 1.0]
     */
    public function calculateAuthorityScore(SourceDefinition $source): float;

    /**
     * Calculates the normalized claim confidence score using the approved linear weighting model:
     * clamp_{[0.0, 1.0]}(w1*A_source + w2*V_match + w3*F_freshness + w4*C_corroboration - P_conflict)
     * with weights w1=0.35, w2=0.30, w3=0.20, w4=0.15.
     *
     * @param ExtractedClaim $claim Extracted claim to evaluate
     * @param SourceDefinition $source Primary source definition providing the claim
     * @param array<string, mixed> $context Operational context (active profile, corroborating sources, conflicts)
     * @return float Confidence score bounded strictly in [0.0, 1.0]
     */
    public function evaluateClaimConfidence(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): float;

    /**
     * Determines the canonical evidential state for an extracted claim:
     * VERIFIED_AUTHORITATIVE, CORROBORATED_PRACTICE, UNRESOLVED_DISPUTE,
     * VERSION_MISMATCH, OUTDATED_SUPERSEDED, or UNVERIFIED_SPECULATION.
     *
     * @param ExtractedClaim $claim Extracted claim
     * @param SourceDefinition $source Primary source definition
     * @param array<string, mixed> $context Operational context
     * @return string Canonical evidential state identifier
     */
    public function classifyEvidentialState(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): string;

    /**
     * Performs a complete multi-dimensional trust assessment for an extracted claim.
     *
     * @param ExtractedClaim $claim Extracted claim
     * @param SourceDefinition $source Primary source definition
     * @param array<string, mixed> $context Operational context
     * @return array<string, mixed> Comprehensive assessment details (authority_score, confidence_score, status, metrics)
     */
    public function assessTrust(
        ExtractedClaim $claim,
        SourceDefinition $source,
        array $context = []
    ): array;
}
