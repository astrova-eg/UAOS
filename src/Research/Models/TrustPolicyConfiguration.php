<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

/**
 * Governed, versioned configuration holding approved weights, thresholds, and domain TTLs.
 */
readonly class TrustPolicyConfiguration
{
    public const DEFAULT_POLICY_VERSION = '1.0.0';

    // Approved mathematical scoring weights (sum = 1.00)
    public const WEIGHT_AUTHORITY = 0.35;
    public const WEIGHT_EVIDENCE_QUALITY = 0.30;
    public const WEIGHT_FRESHNESS = 0.20;
    public const WEIGHT_VERIFICATION_STATUS = 0.15;

    // Approved confidence thresholds
    public const THRESHOLD_HIGH = 0.85;
    public const THRESHOLD_MODERATE = 0.70;
    public const THRESHOLD_LOW = 0.50;

    // Six-tier authority hierarchy mapping
    public const AUTHORITY_TIER_SCORES = [
        1 => 1.00,
        2 => 0.85,
        3 => 0.70,
        4 => 0.55,
        5 => 0.30,
        6 => 0.05,
    ];

    // Verification status scores
    public const VERIFICATION_STATUS_SCORES = [
        'fully_tested' => 1.00,
        'connectivity_tested' => 0.75,
        'endpoint_verified' => 0.50,
        'docs_verified' => 0.25,
        'untested' => 0.00,
    ];

    // Default domain TTL matrix in hours (from Prompt 3 / Deliverable G)
    public const DOMAIN_DEFAULT_TTLS = [
        'C' => 6,     // Security Vulnerabilities & Advisories
        'B' => 6,     // Cybersecurity & Incident Response
        'E' => 72,    // AI Models & Framework APIs
        'A' => 168,   // Software Engineering & Framework Docs (Default)
        'J' => 168,   // Macroeconomics & Finance
        'N' => 168,   // Public Statistics
        'I' => 720,   // Statutory Law & Regulations
        'F' => 720,   // Natural Sciences
        'G' => 720,   // Bio / Life Sciences
        'H' => 720,   // Medicine & Healthcare
        'D' => 2160,  // International Standards & Hardware
        'L' => 8760,  // Pure Mathematics & Formal Methods
        'GLOBAL' => 168,
    ];

    public function __construct(
        public string $policyVersion = self::DEFAULT_POLICY_VERSION,
        public float $weightAuthority = self::WEIGHT_AUTHORITY,
        public float $weightEvidenceQuality = self::WEIGHT_EVIDENCE_QUALITY,
        public float $weightFreshness = self::WEIGHT_FRESHNESS,
        public float $weightVerificationStatus = self::WEIGHT_VERIFICATION_STATUS,
        public float $thresholdHigh = self::THRESHOLD_HIGH,
        public float $thresholdModerate = self::THRESHOLD_MODERATE,
        public float $thresholdLow = self::THRESHOLD_LOW,
        public array $domainTtls = self::DOMAIN_DEFAULT_TTLS
    ) {}

    public static function createDefault(): self
    {
        return new self();
    }

    public function resolveConfidenceTier(float $score): string
    {
        if ($score >= $this->thresholdHigh) {
            return 'HIGH';
        }
        if ($score >= $this->thresholdModerate) {
            return 'MODERATE';
        }
        if ($score >= $this->thresholdLow) {
            return 'LOW';
        }
        return 'INSUFFICIENT';
    }

    public function getDomainTtl(string $domainCode, int $fallbackHours = 168): int
    {
        $code = strtoupper(trim($domainCode));
        return $this->domainTtls[$code] ?? $fallbackHours;
    }
}
