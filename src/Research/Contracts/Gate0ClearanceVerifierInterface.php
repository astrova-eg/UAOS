<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;

/**
 * Authoritative contract governing Gate 0 (Research Clearance) verification.
 *
 * Enforces mandatory research depth compliance, cryptographic dossier integrity,
 * source eligibility, freshness, and unresolved contradiction blocking.
 */
interface Gate0ClearanceVerifierInterface
{
    /**
     * Authoritatively verifies whether a task satisfies Gate 0 clearance prerequisites.
     *
     * @param string $taskId Unique authoritative task identifier
     * @param int $stage Workflow stage (1-14)
     * @param DepthProfile|null $requiredDepth Calibrated depth requirement (or null to evaluate against task)
     * @param ResearchDossier|null $dossier Optional explicit dossier (or null to load from repository)
     * @return Gate0ClearanceResult Deterministic Gate 0 evaluation outcome
     */
    public function verifyClearance(
        string $taskId,
        int $stage = 1,
        ?DepthProfile $requiredDepth = null,
        ?ResearchDossier $dossier = null
    ): Gate0ClearanceResult;

    /**
     * Directly evaluates a research dossier against required depth and policy rules.
     *
     * @param ResearchDossier $dossier The sealed research dossier
     * @param DepthProfile $requiredDepth The required depth profile
     * @return Gate0ClearanceResult Deterministic Gate 0 evaluation outcome
     */
    public function evaluateDossier(
        ResearchDossier $dossier,
        DepthProfile $requiredDepth
    ): Gate0ClearanceResult;
}
