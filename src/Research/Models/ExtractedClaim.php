<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing an individual technical claim extracted from external research.
 *
 * Encapsulates evidential status, orthogonal confidence score, version applicability,
 * and cryptographic citation provenance.
 */
readonly class ExtractedClaim implements JsonSerializable
{
    public const STATUS_VERIFIED_AUTHORITATIVE = 'VERIFIED_AUTHORITATIVE';
    public const STATUS_CORROBORATED_PRACTICE = 'CORROBORATED_PRACTICE';
    public const STATUS_UNRESOLVED_DISPUTE = 'UNRESOLVED_DISPUTE';
    public const STATUS_VERSION_MISMATCH = 'VERSION_MISMATCH';
    public const STATUS_OUTDATED_SUPERSEDED = 'OUTDATED_SUPERSEDED';
    public const STATUS_UNVERIFIED_SPECULATION = 'UNVERIFIED_SPECULATION';

    // Canonical final evidence verification status (Schema Correction 003C)
    public const STATUS_EVIDENCE_VERIFIED = 'EVIDENCE VERIFIED';
    public const STATUS_EVIDENCE_VERIFIED_UNDERSCORE = 'EVIDENCE_VERIFIED';

    public const VALID_STATUSES = [
        self::STATUS_VERIFIED_AUTHORITATIVE,
        self::STATUS_CORROBORATED_PRACTICE,
        self::STATUS_UNRESOLVED_DISPUTE,
        self::STATUS_VERSION_MISMATCH,
        self::STATUS_OUTDATED_SUPERSEDED,
        self::STATUS_UNVERIFIED_SPECULATION,
        self::STATUS_EVIDENCE_VERIFIED,
        self::STATUS_EVIDENCE_VERIFIED_UNDERSCORE,
    ];

    /**
     * @param string $claimId Unique claim identifier (e.g. CLAIM-01 or CLM-20261115-01)
     * @param string $statement Factual technical statement or proposition
     * @param string $status Evidential status enum
     * @param float $confidenceScore Normalized confidence score in [0.0, 1.0]
     * @param array<int, string> $applicableVersions Technology versions where claim holds (e.g. ['8.2', '8.3'])
     * @param array<int, array<string, string>> $citations Provenance citations (source_id, content_hash, quote_segment)
     */
    public function __construct(
        public string $claimId,
        public string $statement,
        public string $status,
        public float $confidenceScore,
        public array $applicableVersions = [],
        public array $citations = []
    ) {
        $trimmedId = trim($this->claimId);
        if ($trimmedId === '') {
            throw new InvalidArgumentException('claimId cannot be empty.');
        }

        $trimmedStatement = trim($this->statement);
        if ($trimmedStatement === '') {
            throw new InvalidArgumentException('statement cannot be empty.');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException(
                "Invalid claim status '{$this->status}'. Allowed: " . implode(', ', self::VALID_STATUSES)
            );
        }

        if ($this->confidenceScore < 0.0 || $this->confidenceScore > 1.0) {
            throw new InvalidArgumentException(
                "confidenceScore must be bounded between 0.0 and 1.0, got: {$this->confidenceScore}"
            );
        }

        foreach ($this->citations as $idx => $citation) {
            if (!is_array($citation)) {
                throw new InvalidArgumentException("Citation at index {$idx} must be an associative array.");
            }
            foreach (['source_id', 'content_hash', 'quote_segment'] as $reqField) {
                if (!isset($citation[$reqField]) || !is_string($citation[$reqField]) || trim($citation[$reqField]) === '') {
                    throw new InvalidArgumentException(
                        "Citation at index {$idx} missing mandatory non-empty field '{$reqField}'."
                    );
                }
            }
        }
    }

    /**
     * Whether this claim meets high confidence threshold (>= 0.85) required for architectural decisions.
     */
    public function isHighConfidence(): bool
    {
        return $this->confidenceScore >= 0.85;
    }

    /**
     * Whether this claim has authoritative verification status.
     */
    public function isAuthoritative(): bool
    {
        return in_array($this->status, [
            self::STATUS_VERIFIED_AUTHORITATIVE,
            self::STATUS_EVIDENCE_VERIFIED,
            self::STATUS_EVIDENCE_VERIFIED_UNDERSCORE,
        ], true);
    }

    /**
     * Whether this claim is marked as an unresolved contradiction/dispute.
     */
    public function hasDispute(): bool
    {
        return $this->status === self::STATUS_UNRESOLVED_DISPUTE;
    }

    /**
     * Returns a new instance with updated confidence score.
     */
    public function withConfidenceScore(float $newScore): self
    {
        return new self(
            $this->claimId,
            $this->statement,
            $this->status,
            $newScore,
            $this->applicableVersions,
            $this->citations
        );
    }

    /**
     * Returns a new instance with updated evidential status.
     */
    public function withStatus(string $newStatus): self
    {
        return new self(
            $this->claimId,
            $this->statement,
            $newStatus,
            $this->confidenceScore,
            $this->applicableVersions,
            $this->citations
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'claim_id' => $this->claimId,
            'statement' => $this->statement,
            'status' => $this->status,
            'confidence_score' => $this->confidenceScore,
            'applicable_versions' => $this->applicableVersions,
            'citations' => $this->citations,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['claim_id']) || !is_string($data['claim_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'claim_id' in ExtractedClaim data.");
        }

        if (!isset($data['statement']) || !is_string($data['statement'])) {
            throw new InvalidArgumentException("Missing or invalid 'statement' in ExtractedClaim data.");
        }

        if (!isset($data['status']) || !is_string($data['status'])) {
            throw new InvalidArgumentException("Missing or invalid 'status' in ExtractedClaim data.");
        }

        if (!isset($data['confidence_score']) || !is_numeric($data['confidence_score'])) {
            throw new InvalidArgumentException("Missing or invalid 'confidence_score' in ExtractedClaim data.");
        }

        return new self(
            claimId: (string)$data['claim_id'],
            statement: (string)$data['statement'],
            status: (string)$data['status'],
            confidenceScore: (float)$data['confidence_score'],
            applicableVersions: (array)($data['applicable_versions'] ?? []),
            citations: (array)($data['citations'] ?? [])
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
