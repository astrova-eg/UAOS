<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing the outcome of a Gate 0 (Research Clearance) verification.
 *
 * Governs lifecycle gating before specifications, architecture baselines,
 * or code implementation can proceed.
 */
readonly class Gate0ClearanceResult implements JsonSerializable
{
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_BLOCKED = 'BLOCKED';
    public const STATUS_EXEMPT = 'EXEMPT';

    public const VALID_STATUSES = [
        self::STATUS_PASSED,
        self::STATUS_BLOCKED,
        self::STATUS_EXEMPT,
    ];

    public const CONDITION_CLEARED = 'CLEARED';
    public const CONDITION_EXEMPT_L0 = 'EXEMPT_L0';
    public const CONDITION_UNAVAILABLE_DEPENDENCY = 'UNAVAILABLE_DEPENDENCY';
    public const CONDITION_CRITICAL_CONTRADICTION = 'CRITICAL_CONTRADICTION';
    public const CONDITION_SECURITY_HOLD = 'SECURITY_HOLD';
    public const CONDITION_VERSION_MISMATCH = 'VERSION_MISMATCH';
    public const CONDITION_QUORUM_UNSATISFIED = 'QUORUM_UNSATISFIED';
    public const CONDITION_ACCEPTED_RISK = 'ACCEPTED_RISK';

    /**
     * @param string $status Clearance decision: 'PASSED', 'BLOCKED', or 'EXEMPT'
     * @param string $taskId Unique task identifier
     * @param string $reason Human-readable explanation of decision
     * @param string|null $dossierId Canonical dossier ID if evaluated
     * @param string|null $dossierHash Cryptographic SHA-256 seal of the dossier
     * @param array<int, string> $violations List of validation or policy violations if blocked
     * @param string $operationalCondition Diagnostic condition code
     * @param string $evaluatedAt ISO 8601 UTC evaluation timestamp
     * @param array<string, mixed> $auditDetails Additional metadata for EvidenceCollector telemetry
     */
    public function __construct(
        public string $status,
        public string $taskId,
        public string $reason,
        public ?string $dossierId = null,
        public ?string $dossierHash = null,
        public array $violations = [],
        public string $operationalCondition = self::CONDITION_CLEARED,
        public string $evaluatedAt = '',
        public array $auditDetails = []
    ) {
        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                "Invalid Gate 0 clearance status '{$this->status}'. Allowed: " . implode(', ', self::VALID_STATUSES)
            );
        }

        $trimmedTask = trim($this->taskId);
        if ($trimmedTask === '') {
            throw new InvalidArgumentException('taskId cannot be empty.');
        }

        if ($this->status === self::STATUS_PASSED) {
            if ($this->dossierId === null || trim($this->dossierId) === '') {
                throw new InvalidArgumentException('dossierId is required when Gate 0 clearance status is PASSED.');
            }
            if ($this->dossierHash === null || trim($this->dossierHash) === '') {
                throw new InvalidArgumentException('dossierHash is required when Gate 0 clearance status is PASSED.');
            }
        }

        if ($this->evaluatedAt !== '') {
            TimestampValidator::assertValid($this->evaluatedAt, 'evaluatedAt');
        }
    }

    public static function passed(
        string $taskId,
        string $dossierId,
        string $dossierHash,
        string $reason = 'Gate 0 research clearance verified successfully.',
        array $auditDetails = []
    ): self {
        return new self(
            status: self::STATUS_PASSED,
            taskId: $taskId,
            reason: $reason,
            dossierId: $dossierId,
            dossierHash: $dossierHash,
            violations: [],
            operationalCondition: self::CONDITION_CLEARED,
            evaluatedAt: gmdate('Y-m-d\TH:i:s\Z'),
            auditDetails: $auditDetails
        );
    }

    public static function blocked(
        string $taskId,
        string $reason,
        array $violations = [],
        ?string $dossierId = null,
        ?string $dossierHash = null,
        string $operationalCondition = self::CONDITION_QUORUM_UNSATISFIED,
        array $auditDetails = []
    ): self {
        return new self(
            status: self::STATUS_BLOCKED,
            taskId: $taskId,
            reason: $reason,
            dossierId: $dossierId,
            dossierHash: $dossierHash,
            violations: $violations,
            operationalCondition: $operationalCondition,
            evaluatedAt: gmdate('Y-m-d\TH:i:s\Z'),
            auditDetails: $auditDetails
        );
    }

    public static function exempt(
        string $taskId,
        string $reason = 'Task research depth L0 requires no external evidence.',
        ?string $dossierId = null,
        array $auditDetails = []
    ): self {
        return new self(
            status: self::STATUS_EXEMPT,
            taskId: $taskId,
            reason: $reason,
            dossierId: $dossierId,
            dossierHash: null,
            violations: [],
            operationalCondition: self::CONDITION_EXEMPT_L0,
            evaluatedAt: gmdate('Y-m-d\TH:i:s\Z'),
            auditDetails: $auditDetails
        );
    }

    /**
     * Whether the task is permitted to proceed (either PASSED or EXEMPT).
     */
    public function isCleared(): bool
    {
        return $this->status === self::STATUS_PASSED || $this->status === self::STATUS_EXEMPT;
    }

    public function isPassed(): bool
    {
        return $this->status === self::STATUS_PASSED;
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }

    public function isExempt(): bool
    {
        return $this->status === self::STATUS_EXEMPT;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'task_id' => $this->taskId,
            'reason' => $this->reason,
            'dossier_id' => $this->dossierId,
            'dossier_hash' => $this->dossierHash,
            'violations' => $this->violations,
            'operational_condition' => $this->operationalCondition,
            'evaluated_at' => $this->evaluatedAt !== '' ? $this->evaluatedAt : gmdate('Y-m-d\TH:i:s\Z'),
            'audit_details' => $this->auditDetails,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['status']) || !is_string($data['status'])) {
            throw new InvalidArgumentException("Missing or invalid 'status' in Gate0ClearanceResult data.");
        }

        if (!isset($data['task_id']) || !is_string($data['task_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'task_id' in Gate0ClearanceResult data.");
        }

        if (!isset($data['reason']) || !is_string($data['reason'])) {
            throw new InvalidArgumentException("Missing or invalid 'reason' in Gate0ClearanceResult data.");
        }

        return new self(
            status: (string)$data['status'],
            taskId: (string)$data['task_id'],
            reason: (string)$data['reason'],
            dossierId: isset($data['dossier_id']) ? (string)$data['dossier_id'] : null,
            dossierHash: isset($data['dossier_hash']) ? (string)$data['dossier_hash'] : null,
            violations: (array)($data['violations'] ?? []),
            operationalCondition: (string)($data['operational_condition'] ?? self::CONDITION_CLEARED),
            evaluatedAt: (string)($data['evaluated_at'] ?? gmdate('Y-m-d\TH:i:s\Z')),
            auditDetails: (array)($data['audit_details'] ?? [])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
