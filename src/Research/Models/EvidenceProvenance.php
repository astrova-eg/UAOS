<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable value object preserving evidence provenance and evaluation references.
 */
readonly class EvidenceProvenance implements JsonSerializable
{
    public function __construct(
        public string $evidenceReference,
        public string $sourceId,
        public string $mechanismType,
        public string $verifiedAt,
        public string $verificationStatus,
        public ?string $versionContext = null,
        public ?float $trustScore = null,
        public ?float $freshnessScore = null,
        public string $policyVersion = TrustPolicyConfiguration::DEFAULT_POLICY_VERSION,
        public array $warnings = []
    ) {}

    /**
     * Computes a deterministic tamper-evident cryptographic hash of the provenance tuple.
     */
    public function computeProvenanceHash(): string
    {
        $payload = [
            'evidence_reference' => $this->evidenceReference,
            'source_id' => $this->sourceId,
            'mechanism_type' => $this->mechanismType,
            'verified_at' => $this->verifiedAt,
            'verification_status' => $this->verificationStatus,
            'version_context' => $this->versionContext,
            'policy_version' => $this->policyVersion,
        ];
        ksort($payload);
        return hash('sha256', (string)json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    public function toArray(): array
    {
        return [
            'evidence_reference' => $this->evidenceReference,
            'source_id' => $this->sourceId,
            'mechanism_type' => $this->mechanismType,
            'verified_at' => $this->verifiedAt,
            'verification_status' => $this->verificationStatus,
            'version_context' => $this->versionContext,
            'trust_score' => $this->trustScore,
            'freshness_score' => $this->freshnessScore,
            'policy_version' => $this->policyVersion,
            'provenance_hash' => $this->computeProvenanceHash(),
            'warnings' => $this->warnings,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
