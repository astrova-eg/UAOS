<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Engine;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Contracts\Gate0ClearanceVerifierInterface;
use UniversalAgentOS\Research\Models\ComparisonReport;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\TimestampValidator;

/**
 * Authoritative Gate 0 (Research Clearance) verification engine.
 *
 * Enforces fail-closed verification over dossier completeness, cryptographic integrity,
 * source eligibility, freshness, and unresolved critical contradictions.
 */
class Gate0ClearanceVerifier implements Gate0ClearanceVerifierInterface
{
    private const DEPTH_HIERARCHY = [
        DepthProfile::LEVEL_L0 => 0,
        DepthProfile::LEVEL_L1 => 1,
        DepthProfile::LEVEL_L2 => 2,
        DepthProfile::LEVEL_L3 => 3,
        DepthProfile::LEVEL_L4 => 4,
    ];

    public function __construct(
        private DossierRepositoryInterface $dossierRepository,
        private PathResolverInterface $pathResolver,
        private ?EvidenceCollectorInterface $evidenceCollector = null
    ) {
    }

    public function verifyClearance(
        string $taskId,
        int $stage = 1,
        ?DepthProfile $requiredDepth = null,
        ?ResearchDossier $dossier = null
    ): Gate0ClearanceResult {
        $depth = $requiredDepth ?? DepthProfile::l1();

        // 1. Level 0 tasks are strictly exempt
        if ($depth->level === DepthProfile::LEVEL_L0) {
            $exempt = Gate0ClearanceResult::exempt($taskId, 'Task research depth L0 requires no external evidence.');
            return $this->recordGate0Telemetry($exempt);
        }

        // 2. Check for human-authorized Gate 0 exception record
        $exception = $this->loadApprovedException($taskId);
        if ($exception !== null) {
            $reason = 'Gate 0 exception authorized: ' . (string)($exception['justification'] ?? 'Administrative override');
            $exempt = Gate0ClearanceResult::exempt($taskId, $reason, $dossier?->dossierId, ['exception' => $exception]);
            return $this->recordGate0Telemetry($exempt);
        }

        // 3. Resolve dossier
        $targetDossier = $dossier ?? $this->dossierRepository->findByTaskId($taskId);
        if ($targetDossier === null) {
            $blocked = Gate0ClearanceResult::blocked(
                taskId: $taskId,
                reason: "No research dossier found for task requiring depth {$depth->level}.",
                violations: ["Missing required research dossier for depth {$depth->level}."],
                operationalCondition: Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED
            );
            return $this->recordGate0Telemetry($blocked);
        }

        $result = $this->evaluateDossier($targetDossier, $depth);
        return $this->recordGate0Telemetry($result);
    }

    private function recordGate0Telemetry(Gate0ClearanceResult $decision): Gate0ClearanceResult
    {
        if ($this->evidenceCollector === null) {
            return $decision;
        }

        try {
            $this->evidenceCollector->recordGate0Decision($decision);
            return $decision;
        } catch (\Throwable $telemetryException) {
            $msg = $telemetryException->getMessage();
            if ($decision->isBlocked()) {
                return Gate0ClearanceResult::blocked(
                    taskId: $decision->taskId,
                    reason: $decision->reason . " [Telemetry failure: {$msg}]",
                    violations: array_merge($decision->violations, ["Telemetry recording failure: {$msg}"]),
                    dossierId: $decision->dossierId,
                    dossierHash: $decision->dossierHash,
                    operationalCondition: $decision->operationalCondition,
                    auditDetails: array_merge($decision->auditDetails, ['telemetry_exception' => $msg])
                );
            }

            return Gate0ClearanceResult::blocked(
                taskId: $decision->taskId,
                reason: "Gate 0 clearance blocked: Mandatory audit telemetry recording failed ({$msg}).",
                violations: ["Mandatory audit telemetry recording failed: {$msg}"],
                dossierId: $decision->dossierId,
                dossierHash: $decision->dossierHash,
                operationalCondition: Gate0ClearanceResult::CONDITION_SECURITY_HOLD,
                auditDetails: array_merge($decision->auditDetails, ['original_decision' => $decision->status, 'telemetry_exception' => $msg])
            );
        }
    }

    public function evaluateDossier(
        ResearchDossier $dossier,
        DepthProfile $requiredDepth
    ): Gate0ClearanceResult {
        $violations = [];
        $condition = Gate0ClearanceResult::CONDITION_CLEARED;

        // 1. Cryptographic Sealing & Integrity Verification
        if (!$dossier->isSealed()) {
            $violations[] = 'Research dossier is not cryptographically sealed.';
            $condition = Gate0ClearanceResult::CONDITION_SECURITY_HOLD;
        } elseif (!$dossier->verifyIntegrity()) {
            $violations[] = 'Research dossier cryptographic integrity hash mismatch (tampered or corrupted).';
            $condition = Gate0ClearanceResult::CONDITION_SECURITY_HOLD;
        }

        // 2. Validity Window & Expiration
        if ($dossier->isExpired()) {
            $violations[] = 'Research dossier validity window has expired.';
            $condition = Gate0ClearanceResult::CONDITION_VERSION_MISMATCH;
        }

        // 3. Depth Compliance
        $dossierLevelWeight = self::DEPTH_HIERARCHY[$dossier->researchDepth] ?? -1;
        $requiredLevelWeight = self::DEPTH_HIERARCHY[$requiredDepth->level] ?? 1;
        if ($dossierLevelWeight < $requiredLevelWeight) {
            $violations[] = "Dossier research depth ({$dossier->researchDepth}) does not satisfy required depth ({$requiredDepth->level}).";
            $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
        }

        // 4. Source Quorum & Eligibility
        $validSourcesCount = 0;
        foreach ($dossier->sourcesConsulted as $source) {
            $isEligible = $source['is_eligible'] ?? true;
            $lifecycle = strtoupper((string)($source['lifecycle_state'] ?? 'ACTIVE'));
            $sourceId = (string)($source['source_id'] ?? 'unknown');

            if (!$isEligible || $lifecycle === 'REVOKED' || $lifecycle === 'DEPRECATED') {
                $violations[] = "Source '{$sourceId}' is ineligible or revoked.";
                $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
            } else {
                $validSourcesCount++;
            }
        }

        if ($validSourcesCount < $requiredDepth->minSources) {
            $violations[] = "Insufficient source quorum: found {$validSourcesCount} eligible sources, required {$requiredDepth->minSources}.";
            $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
        }

        // 5. Claims Extraction & Confidence Governance
        if (empty($dossier->claims) && $requiredDepth->requiresExternalResearch()) {
            $violations[] = "No claims extracted in research dossier for task requiring depth {$requiredDepth->level}.";
            $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
        }

        foreach ($dossier->claims as $claim) {
            if ($claim->hasDispute()) {
                $violations[] = "Claim '{$claim->claimId}' has an unresolved dispute.";
                $condition = Gate0ClearanceResult::CONDITION_CRITICAL_CONTRADICTION;
            }
            if ($claim->status === ExtractedClaim::STATUS_VERSION_MISMATCH) {
                $violations[] = "Claim '{$claim->claimId}' has an unresolved version mismatch with active stack.";
                $condition = Gate0ClearanceResult::CONDITION_VERSION_MISMATCH;
            }
            if ($claim->status === ExtractedClaim::STATUS_UNVERIFIED_SPECULATION && in_array($requiredDepth->level, ['L2', 'L3', 'L4'], true)) {
                $violations[] = "Claim '{$claim->claimId}' is unverified speculation without required corroboration.";
                $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
            }
            if (empty($claim->citations)) {
                $violations[] = "Claim '{$claim->claimId}' lacks verifiable source citations.";
                $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
            }
        }

        // 6. Critical Technical Contradictions
        if ($dossier->hasCriticalConflicts()) {
            $violations[] = 'Research dossier contains unresolved critical contradictions.';
            $condition = Gate0ClearanceResult::CONDITION_CRITICAL_CONTRADICTION;
        }

        foreach ($dossier->conflictsDetected as $conflict) {
            $isCritical = (bool)($conflict['is_critical'] ?? false);
            $blocksGate0 = (bool)($conflict['blocks_gate_0'] ?? false);
            $resStatus = (string)($conflict['resolution_status'] ?? '');

            if ($isCritical || $blocksGate0 || $resStatus === 'UNRESOLVED_CRITICAL' || $resStatus === 'ESCALATED') {
                $desc = (string)($conflict['description'] ?? 'Critical conflict unresolved');
                $violations[] = "Critical contradiction remains unresolved: {$desc}";
                $condition = Gate0ClearanceResult::CONDITION_CRITICAL_CONTRADICTION;
            }
        }

        // 7. Technology Comparison Trade-Off Matrices
        if ($requiredDepth->requiresTradeoffMatrix) {
            if (empty($dossier->comparisonMatrices)) {
                $violations[] = "Mandatory technology trade-off matrix missing for depth {$requiredDepth->level}.";
                $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
            } else {
                foreach ($dossier->comparisonMatrices as $matrix) {
                    $mStatus = (string)($matrix['status'] ?? '');
                    if ($mStatus === ComparisonReport::STATUS_INSUFFICIENT_EVIDENCE) {
                        $domain = (string)($matrix['domain'] ?? 'general');
                        $violations[] = "Technology trade-off matrix for '{$domain}' has insufficient evidence.";
                        $condition = Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED;
                    }
                }
            }
        }

        // 8. Security Findings & CVEs (CVSS >= 7.0)
        foreach ($dossier->securityFindings as $finding) {
            $cvss = (float)($finding['cvss'] ?? $finding['cvss_score'] ?? 0.0);
            $status = strtoupper((string)($finding['status'] ?? ''));
            if ($cvss >= 7.0 || $status === 'CONFIRMED_VULNERABLE') {
                $id = (string)($finding['cve_id'] ?? $finding['id'] ?? 'CVE');
                $violations[] = "Unmitigated critical security vulnerability ({$id}, CVSS: {$cvss}) detected.";
                $condition = Gate0ClearanceResult::CONDITION_SECURITY_HOLD;
            }
        }

        if (!empty($violations)) {
            return Gate0ClearanceResult::blocked(
                taskId: $dossier->taskId,
                reason: 'Gate 0 clearance blocked: ' . $violations[0],
                violations: array_values(array_unique($violations)),
                dossierId: $dossier->dossierId,
                dossierHash: $dossier->dossierHash,
                operationalCondition: $condition,
                auditDetails: ['dossier_depth' => $dossier->researchDepth, 'required_depth' => $requiredDepth->level, 'evaluated_sources' => count($dossier->sourcesConsulted)]
            );
        }

        return Gate0ClearanceResult::passed(
            taskId: $dossier->taskId,
            dossierId: $dossier->dossierId,
            dossierHash: $dossier->dossierHash,
            reason: 'Gate 0 research clearance verified successfully.',
            auditDetails: ['dossier_depth' => $dossier->researchDepth, 'claims_count' => count($dossier->claims), 'sources_count' => count($dossier->sourcesConsulted)]
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    private function loadApprovedException(string $taskId): ?array
    {
        $file = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'authorizations' . DIRECTORY_SEPARATOR . 'gate-0-exceptions.json';
        if (!file_exists($file)) {
            return null;
        }
        $raw = file_get_contents($file);
        $data = is_string($raw) ? json_decode($raw, true) : null;
        if (!is_array($data)) {
            return null;
        }
        foreach ((array)($data['exceptions'] ?? $data) as $ex) {
            if (!is_array($ex)) continue;
            if (strcasecmp((string)($ex['task_id'] ?? ''), $taskId) === 0) {
                if (trim((string)($ex['authorized_by'] ?? '')) === '') {
                    return null;
                }
                $exp = (string)($ex['expiration_timestamp'] ?? '');
                if ($exp !== '') {
                    $expTs = TimestampValidator::toTimestamp($exp);
                    if ($expTs !== null && time() > $expTs) {
                        return null;
                    }
                }
                return $ex;
            }
        }
        return null;
    }
}
