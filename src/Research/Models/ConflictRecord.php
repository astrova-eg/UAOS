<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing a detected contradiction between two technical claims.
 */
readonly class ConflictRecord implements JsonSerializable
{
    public const TYPE_VERSION_DIVERGENCE = 'VERSION_DIVERGENCE';
    public const TYPE_CONTEXT_DISCREPANCY = 'CONTEXT_DISCREPANCY';
    public const TYPE_TEMPORAL_INVALIDATION = 'TEMPORAL_INVALIDATION';
    public const TYPE_TRUE_TECHNICAL_CONTRADICTION = 'TRUE_TECHNICAL_CONTRADICTION';

    public const VALID_TYPES = [
        self::TYPE_VERSION_DIVERGENCE,
        self::TYPE_CONTEXT_DISCREPANCY,
        self::TYPE_TEMPORAL_INVALIDATION,
        self::TYPE_TRUE_TECHNICAL_CONTRADICTION,
    ];

    /**
     * @param string $conflictId Unique conflict identifier (e.g. CNF-20261115-01)
     * @param ExtractedClaim $claimA First conflicting claim
     * @param ExtractedClaim $claimB Second conflicting claim
     * @param string $contradictionType Contradiction taxonomy classification
     * @param string $targetSubject Technical topic or symbol in dispute
     * @param bool $isCritical Whether dispute affects security, isolation, or core architecture
     * @param string $description Explanation of the detected disagreement
     * @param string $detectedAt ISO 8601 UTC timestamp of detection
     * @param SourceDefinition|null $sourceA Optional SourceDefinition for Claim A
     * @param SourceDefinition|null $sourceB Optional SourceDefinition for Claim B
     */
    public function __construct(
        public string $conflictId,
        public ExtractedClaim $claimA,
        public ExtractedClaim $claimB,
        public string $contradictionType,
        public string $targetSubject,
        public bool $isCritical,
        public string $description,
        public string $detectedAt,
        public ?SourceDefinition $sourceA = null,
        public ?SourceDefinition $sourceB = null
    ) {
        if (trim($this->conflictId) === '') {
            throw new InvalidArgumentException('conflictId cannot be empty.');
        }

        if (!in_array($this->contradictionType, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("Invalid contradictionType '{$this->contradictionType}'.");
        }

        if (trim($this->targetSubject) === '') {
            throw new InvalidArgumentException('targetSubject cannot be empty.');
        }

        TimestampValidator::assertValid($this->detectedAt, 'detectedAt');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'conflict_id' => $this->conflictId,
            'claim_a_id' => $this->claimA->claimId,
            'claim_b_id' => $this->claimB->claimId,
            'contradiction_type' => $this->contradictionType,
            'target_subject' => $this->targetSubject,
            'is_critical' => $this->isCritical,
            'description' => $this->description,
            'detected_at' => $this->detectedAt,
            'source_a_id' => $this->sourceA?->sourceId,
            'source_b_id' => $this->sourceB?->sourceId,
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
