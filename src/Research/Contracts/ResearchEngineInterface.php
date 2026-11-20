<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;

/**
 * Primary subsystem facade contract for Research-Driven Engineering Intelligence (RDEI).
 *
 * Governs coordinated multi-source technical research, dossier retrieval,
 * and lifecycle clearance verification under Gate 0.
 */
interface ResearchEngineInterface
{
    /**
     * Executes coordinated technical research for a research request.
     *
     * @param ResearchRequest $request Authoritative research request parameters
     * @return ResearchDossier Cryptographically sealed research dossier
     */
    public function conductResearch(ResearchRequest $request): ResearchDossier;

    /**
     * Retrieves an existing valid dossier by ID or returns null if expired or missing.
     *
     * @param string $researchId Canonical dossier identifier (e.g. DOS-20261115-4a7b9c)
     * @return ResearchDossier|null Active valid dossier or null
     */
    public function getValidDossier(string $researchId): ?ResearchDossier;

    /**
     * Verifies whether a task satisfies Gate 0 (Research Clearance) prerequisites.
     *
     * @param string $taskId Unique authoritative task identifier
     * @return Gate0ClearanceResult Deterministic Gate 0 clearance evaluation outcome
     */
    public function verifyClearance(string $taskId): Gate0ClearanceResult;
}
