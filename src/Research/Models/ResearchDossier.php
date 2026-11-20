<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;
use RuntimeException;

/**
 * Entity representing an immutable, cryptographically sealed Research Dossier.
 *
 * Serves as the authoritative evidential foundation for architecture, specifications,
 * and downstream Gate 0 / Gate 4 audit provenance.
 */
readonly class ResearchDossier implements JsonSerializable
{
    public const GATE_STATUS_PASSED = 'PASSED';
    public const GATE_STATUS_BLOCKED = 'BLOCKED';
    public const GATE_STATUS_EXEMPT = 'EXEMPT';

    public const VALID_GATE_STATUSES = [
        self::GATE_STATUS_PASSED,
        self::GATE_STATUS_BLOCKED,
        self::GATE_STATUS_EXEMPT,
    ];

    /**
     * @param array<int, array<string, mixed>> $sourcesConsulted
     * @param array<int, ExtractedClaim> $claims
     * @param array<int, array<string, mixed>> $conflictsDetected
     * @param array<int, array<string, mixed>> $comparisonMatrices
     * @param array<int, array<string, mixed>> $securityFindings
     * @param array<int, array<string, mixed>> $recommendations
     */
    public function __construct(
        public string $dossierId,
        public string $taskId,
        public string $projectIdentity,
        public string $researchDepth,
        public string $researchedAt,
        public ?string $expiresAt = null,
        public array $sourcesConsulted = [],
        public array $claims = [],
        public array $conflictsDetected = [],
        public array $comparisonMatrices = [],
        public array $securityFindings = [],
        public array $recommendations = [],
        public string $gate0Status = self::GATE_STATUS_BLOCKED,
        public string $dossierHash = ''
    ) {
        if (trim($this->dossierId) === '') throw new InvalidArgumentException('dossierId cannot be empty.');
        if (trim($this->taskId) === '') throw new InvalidArgumentException('taskId cannot be empty.');
        if (!in_array($this->researchDepth, DepthProfile::VALID_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid researchDepth '{$this->researchDepth}'. Allowed: " . implode(', ', DepthProfile::VALID_LEVELS));
        }
        if (!in_array($this->gate0Status, self::VALID_GATE_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid gate0Status '{$this->gate0Status}'. Allowed: " . implode(', ', self::VALID_GATE_STATUSES));
        }

        TimestampValidator::assertValid($this->researchedAt, 'researchedAt');
        if ($this->expiresAt !== null && $this->expiresAt !== '') {
            TimestampValidator::assertValid($this->expiresAt, 'expiresAt');
        }

        foreach ($this->claims as $idx => $claim) {
            if (!$claim instanceof ExtractedClaim) {
                throw new InvalidArgumentException("Claim at index {$idx} must be an instance of " . ExtractedClaim::class);
            }
        }
    }

    /**
     * Computes the deterministic SHA-256 integrity hash of the canonicalized dossier payload.
     */
    public function computeHash(): string
    {
        $payload = [
            'dossier_id' => $this->dossierId,
            'task_id' => $this->taskId,
            'project_identity' => $this->projectIdentity,
            'research_depth' => $this->researchDepth,
            'researched_at' => $this->researchedAt,
            'expires_at' => $this->expiresAt,
            'sources_consulted' => $this->sourcesConsulted,
            'claims' => array_map(static fn (ExtractedClaim $c) => $c->toArray(), $this->claims),
            'conflicts_detected' => $this->conflictsDetected,
            'comparison_matrices' => $this->comparisonMatrices,
            'security_findings' => $this->securityFindings,
            'recommendations' => $this->recommendations,
            'gate_0_status' => $this->gate0Status,
        ];

        $canonical = CanonicalPayloadSorter::canonicalize($payload);

        try {
            $json = json_encode($canonical, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException("ResearchDossier hash computation failed: {$e->getMessage()}", 0, $e);
        }

        return hash('sha256', $json);
    }

    /**
     * Whether this dossier has been sealed with a cryptographic hash.
     */
    public function isSealed(): bool
    {
        return $this->dossierHash !== '';
    }

    /**
     * Verifies whether the recorded dossier_hash matches the recomputed physical content hash.
     */
    public function verifyIntegrity(): bool
    {
        if (!$this->isSealed()) {
            return false;
        }

        return hash_equals($this->computeHash(), $this->dossierHash);
    }

    /**
     * Seals the dossier by calculating and returning a new instance with the computed SHA-256 hash.
     */
    public function seal(): self
    {
        return new self(
            $this->dossierId, $this->taskId, $this->projectIdentity, $this->researchDepth,
            $this->researchedAt, $this->expiresAt, $this->sourcesConsulted, $this->claims,
            $this->conflictsDetected, $this->comparisonMatrices, $this->securityFindings,
            $this->recommendations, $this->gate0Status, $this->computeHash()
        );
    }

    /**
     * Returns a new instance with updated Gate 0 clearance status.
     */
    public function withGate0Status(string $status): self
    {
        return new self(
            $this->dossierId, $this->taskId, $this->projectIdentity, $this->researchDepth,
            $this->researchedAt, $this->expiresAt, $this->sourcesConsulted, $this->claims,
            $this->conflictsDetected, $this->comparisonMatrices, $this->securityFindings,
            $this->recommendations, $status, $this->dossierHash
        );
    }

    /**
     * Whether this dossier contains any unresolved critical contradictions.
     */
    public function hasCriticalConflicts(): bool
    {
        foreach ($this->conflictsDetected as $conflict) {
            $status = $conflict['resolution_status'] ?? '';
            if ($status === 'ESCALATED' || $status === 'UNRESOLVED') {
                return true;
            }
        }

        foreach ($this->claims as $claim) {
            if ($claim->hasDispute()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the dossier validity window has expired.
     */
    public function isExpired(?int $currentTime = null): bool
    {
        if ($this->expiresAt === null || $this->expiresAt === '') {
            return false;
        }

        $expireTimestamp = TimestampValidator::toTimestamp($this->expiresAt);
        if ($expireTimestamp === null) {
            return true; // Fail closed: unparseable expiration timestamps are treated as expired
        }

        return ($currentTime ?? time()) > $expireTimestamp;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dossier_id' => $this->dossierId,
            'task_id' => $this->taskId,
            'project_identity' => $this->projectIdentity,
            'research_depth' => $this->researchDepth,
            'researched_at' => $this->researchedAt,
            'expires_at' => $this->expiresAt,
            'sources_consulted' => $this->sourcesConsulted,
            'claims' => array_map(static fn (ExtractedClaim $c) => $c->toArray(), $this->claims),
            'conflicts_detected' => $this->conflictsDetected,
            'comparison_matrices' => $this->comparisonMatrices,
            'security_findings' => $this->securityFindings,
            'recommendations' => $this->recommendations,
            'gate_0_status' => $this->gate0Status,
            'dossier_hash' => $this->dossierHash,
        ];
    }

    /**
     * Restores a ResearchDossier from serialized array data.
     *
     * Invariant: If a sealed hash is present, verifies payload integrity immediately.
     * If payload does not match sealed hash, rejects restoration.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['dossier_id']) || !is_string($data['dossier_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'dossier_id' in ResearchDossier data.");
        }
        if (!isset($data['task_id']) || !is_string($data['task_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'task_id' in ResearchDossier data.");
        }

        $rawClaims = (array)($data['claims'] ?? []);
        $claims = [];
        foreach ($rawClaims as $idx => $claimData) {
            if ($claimData instanceof ExtractedClaim) {
                $claims[] = $claimData;
            } elseif (is_array($claimData)) {
                $claims[] = ExtractedClaim::fromArray($claimData);
            } else {
                throw new InvalidArgumentException("Invalid claim at index {$idx} in ResearchDossier.");
            }
        }

        $researchedAt = isset($data['researched_at']) && is_string($data['researched_at'])
            ? $data['researched_at']
            : gmdate('Y-m-d\TH:i:s\Z');
        $expiresAt = isset($data['expires_at']) && $data['expires_at'] !== null
            ? (string)$data['expires_at']
            : null;

        $instance = new self(
            dossierId: (string)$data['dossier_id'],
            taskId: (string)$data['task_id'],
            projectIdentity: (string)($data['project_identity'] ?? ''),
            researchDepth: (string)($data['research_depth'] ?? DepthProfile::LEVEL_L1),
            researchedAt: $researchedAt,
            expiresAt: $expiresAt,
            sourcesConsulted: (array)($data['sources_consulted'] ?? []),
            claims: $claims,
            conflictsDetected: (array)($data['conflicts_detected'] ?? []),
            comparisonMatrices: (array)($data['comparison_matrices'] ?? []),
            securityFindings: (array)($data['security_findings'] ?? []),
            recommendations: (array)($data['recommendations'] ?? []),
            gate0Status: (string)($data['gate_0_status'] ?? self::GATE_STATUS_BLOCKED),
            dossierHash: (string)($data['dossier_hash'] ?? '')
        );

        if ($instance->isSealed() && !$instance->verifyIntegrity()) {
            throw new InvalidArgumentException(
                "ResearchDossier restoration integrity verification failed: restored payload does not match sealed hash '{$instance->dossierHash}'."
            );
        }

        return $instance;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
