<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing the deterministic outcome of a conflict resolution.
 */
readonly class ResolutionDecision implements JsonSerializable
{
    public const STATUS_RESOLVED = 'RESOLVED';
    public const STATUS_RESOLVED_WITH_LIMITATIONS = 'RESOLVED_WITH_LIMITATIONS';
    public const STATUS_UNRESOLVED_CRITICAL = 'UNRESOLVED_CRITICAL';
    public const STATUS_UNRESOLVED_NONCRITICAL = 'UNRESOLVED_NONCRITICAL';

    public const VALID_STATUSES = [
        self::STATUS_RESOLVED,
        self::STATUS_RESOLVED_WITH_LIMITATIONS,
        self::STATUS_UNRESOLVED_CRITICAL,
        self::STATUS_UNRESOLVED_NONCRITICAL,
    ];

    /**
     * @param string $conflictId Conflict ID resolved
     * @param string $status Resolution outcome enum
     * @param string $rationale Detailed explanation of the resolution or escalation decision
     * @param bool $blocksGate0 Whether this outcome halts Gate 0 clearance
     * @param ExtractedClaim|null $prevailingClaim Winning authoritative claim (if resolved)
     * @param ExtractedClaim|null $supersededClaim Losing or qualified claim (preserved for provenance)
     * @param bool $requiresFollowUpEvidence Whether further research or human review is needed
     * @param string|null $followUpRequirement Specific missing evidence needed
     * @param array<int, array<string, string>> $citationsPreserved Preserved provenance citations
     */
    public function __construct(
        public string $conflictId,
        public string $status,
        public string $rationale,
        public bool $blocksGate0,
        public ?ExtractedClaim $prevailingClaim = null,
        public ?ExtractedClaim $supersededClaim = null,
        public bool $requiresFollowUpEvidence = false,
        public ?string $followUpRequirement = null,
        public array $citationsPreserved = []
    ) {
        if (trim($this->conflictId) === '') {
            throw new InvalidArgumentException('conflictId cannot be empty.');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid resolution status '{$this->status}'.");
        }

        if (trim($this->rationale) === '') {
            throw new InvalidArgumentException('rationale cannot be empty.');
        }
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED || $this->status === self::STATUS_RESOLVED_WITH_LIMITATIONS;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'conflict_id' => $this->conflictId,
            'status' => $this->status,
            'rationale' => $this->rationale,
            'blocks_gate_0' => $this->blocksGate0,
            'prevailing_claim_id' => $this->prevailingClaim?->claimId,
            'superseded_claim_id' => $this->supersededClaim?->claimId,
            'requires_follow_up_evidence' => $this->requiresFollowUpEvidence,
            'follow_up_requirement' => $this->followUpRequirement,
            'citations_preserved' => $this->citationsPreserved,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
