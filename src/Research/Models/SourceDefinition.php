<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Entity representing an authoritative research source definition.
 *
 * Aligned with UAOS-RDEI-GLOBAL-SOURCES-003 canonical 26-field specification,
 * governing authority tiers, access mechanisms, freshness, and strict secret hygiene.
 */
readonly class SourceDefinition implements JsonSerializable
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DEPRECATED = 'deprecated';
    public const STATUS_RATE_LIMITED = 'rate_limited';
    public const STATUS_UNREACHABLE = 'unreachable';
    public const STATUS_CREDENTIALS_MISSING = 'credentials_missing';

    public const VALID_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_DEPRECATED,
        self::STATUS_RATE_LIMITED,
        self::STATUS_UNREACHABLE,
        self::STATUS_CREDENTIALS_MISSING,
    ];

    public const DESIGNATION_PRIMARY = 'primary_authority';
    public const DESIGNATION_CURATED = 'curated_secondary';
    public const DESIGNATION_STATISTICAL = 'statistical_registry';
    public const DESIGNATION_AGGREGATOR = 'aggregator';
    public const DESIGNATION_PRACTITIONER = 'practitioner_commentary';

    public const CATEGORY_KNOWLEDGE_SOURCE = 'knowledge_source';
    public const CATEGORY_DISCOVERY_PROVIDER = 'discovery_provider';
    public const CATEGORY_RETRIEVAL_MECHANISM = 'retrieval_mechanism';
    public const CATEGORY_TOOLING_MCP = 'tooling_mcp';

    private const ALLOWED_AUTH_KEYS = ['requires_auth', 'auth_type', 'env_var_key'];

    /**
     * @param string $sourceId Stable identifier (e.g. src-soft-packagist, src-sec-nvd)
     * @param string $officialName Full formal name of the source
     * @param string $description Comprehensive summary of the source
     * @param array<string, mixed> $owner Institutional owner details
     * @param string $canonicalUrl Authoritative web endpoint (HTTPS remote)
     * @param array<int, string> $domains Knowledge domains (e.g. ['A', 'B'])
     * @param array<int, string> $subdomains Subdomains governed (e.g. ['A.01', 'B.02'])
     * @param int $authorityTier Authority level from 1 (Highest) to 6 (Unverified)
     * @param string $sourceDesignation Source designation category
     * @param string $categoryType Conceptual category
     * @param array<int, string> $supportedLanguages ISO language codes
     * @param array<int, string> $supportedFormats Supported MIME types
     * @param array<int, array<string, mixed>> $accessMechanisms Technical retrieval interfaces
     * @param array<string, mixed> $authProfile Authentication configuration
     * @param string $costTier Cost model tier
     * @param array<string, mixed> $licensing Licensing and attribution rules
     * @param array<string, mixed> $rateLimits Rate limits and quotas
     * @param array<string, mixed> $freshnessPolicy Cache lifetimes and revalidation triggers
     * @param string $status Operational status
     * @param string $lastVerifiedAt ISO 8601 evaluation timestamp
     * @param string $classificationEvidence Documentary justification for authority tier
     * @param array<string, mixed> $verificationProfile Verification rigor details
     * @param array<string, mixed> $independenceProfile Institutional independence details
     * @param array<string, mixed> $applicabilityProfile Technical applicability matching rules
     * @param array<string, mixed> $provenanceRules Provenance preservation rules
     * @param array<int, string> $fallbackSourceIds Fallback source IDs
     */
    public function __construct(
        public string $sourceId,
        public string $officialName,
        public string $description,
        public array $owner,
        public string $canonicalUrl,
        public array $domains,
        public array $subdomains,
        public int $authorityTier,
        public string $sourceDesignation = self::DESIGNATION_PRIMARY,
        public string $categoryType = self::CATEGORY_KNOWLEDGE_SOURCE,
        public array $supportedLanguages = ['en'],
        public array $supportedFormats = ['application/json', 'text/html'],
        public array $accessMechanisms = [],
        public array $authProfile = ['requires_auth' => false, 'auth_type' => 'none'],
        public string $costTier = 'free_public',
        public array $licensing = ['license_type' => 'Open Data', 'reuse_permitted' => true, 'attribution_required' => false],
        public array $rateLimits = ['requests_per_minute' => 60, 'burst_limit' => 10],
        public array $freshnessPolicy = ['max_cache_ttl_hours' => 168, 'revalidation_trigger' => 'weekly_168h'],
        public string $status = self::STATUS_ACTIVE,
        public string $lastVerifiedAt = '',
        public string $classificationEvidence = '',
        public array $verificationProfile = [],
        public array $independenceProfile = [],
        public array $applicabilityProfile = [],
        public array $provenanceRules = ['retains_upstream_url' => true, 'retains_publisher_identity' => true, 'hashes_raw_payload' => true],
        public array $fallbackSourceIds = []
    ) {
        if (trim($this->sourceId) === '') throw new InvalidArgumentException('sourceId cannot be empty.');
        if (trim($this->officialName) === '') throw new InvalidArgumentException('officialName cannot be empty.');
        if (trim($this->canonicalUrl) === '') throw new InvalidArgumentException('canonicalUrl cannot be empty.');
        if ($this->authorityTier < 1 || $this->authorityTier > 6) {
            throw new InvalidArgumentException("authorityTier must be between 1 and 6, got: {$this->authorityTier}");
        }
        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid source status '{$this->status}'. Allowed: " . implode(', ', self::VALID_STATUSES));
        }
        if ($this->lastVerifiedAt !== '') {
            TimestampValidator::assertValid($this->lastVerifiedAt, 'lastVerifiedAt');
        }
        $this->validateAuthProfile();
    }

    private function validateAuthProfile(): void
    {
        foreach (array_keys($this->authProfile) as $k) {
            if (!in_array($k, self::ALLOWED_AUTH_KEYS, true)) {
                throw new InvalidArgumentException(
                    "auth_profile contains forbidden arbitrary or secret field '{$k}'. Only requires_auth, auth_type, and env_var_key are permitted."
                );
            }
        }

        $requiresAuth = $this->authProfile['requires_auth'] ?? false;
        if (!is_bool($requiresAuth)) {
            throw new InvalidArgumentException("auth_profile.requires_auth must be a boolean.");
        }

        if ($requiresAuth === true) {
            $authType = $this->authProfile['auth_type'] ?? 'none';
            if ($authType === 'none') {
                throw new InvalidArgumentException("auth_profile requires_auth=true forbids auth_type='none'.");
            }
            if (!isset($this->authProfile['env_var_key']) || !is_string($this->authProfile['env_var_key']) || trim($this->authProfile['env_var_key']) === '') {
                throw new InvalidArgumentException("auth_profile requires a valid non-empty env_var_key when requires_auth=true.");
            }
            self::assertValidEnvVarReference(trim($this->authProfile['env_var_key']));
        } else {
            $authType = $this->authProfile['auth_type'] ?? 'none';
            if ($authType !== 'none') {
                throw new InvalidArgumentException("auth_profile requires_auth=false forces auth_type='none', got '{$authType}'.");
            }
            if (isset($this->authProfile['env_var_key']) && $this->authProfile['env_var_key'] !== null && $this->authProfile['env_var_key'] !== '') {
                throw new InvalidArgumentException("auth_profile requires_auth=false forbids env_var_key.");
            }
        }
    }

    public static function assertValidEnvVarReference(string $envKey): void
    {
        if (!preg_match('/^[A-Z][A-Z0-9_]{1,63}$/', $envKey)) {
            throw new InvalidArgumentException(
                "auth_profile.env_var_key must be an environment variable reference matching ^[A-Z][A-Z0-9_]{1,63}$, got: '{$envKey}'."
            );
        }

        if (ctype_xdigit($envKey) && strlen($envKey) >= 24) {
            throw new InvalidArgumentException("auth_profile.env_var_key appears to be a raw hexadecimal secret/hash value.");
        }

        $disallowedPrefixes = ['GHP_', 'GLPAT_', 'SK_LIVE_', 'SK_TEST_', 'EYJ0', 'EYJH', 'AIZASY'];
        foreach ($disallowedPrefixes as $prefix) {
            if (str_starts_with($envKey, $prefix)) {
                throw new InvalidArgumentException("auth_profile.env_var_key contains raw credential prefix '{$prefix}'.");
            }
        }

        if (strlen($envKey) >= 24 && !str_contains($envKey, '_')) {
            throw new InvalidArgumentException("auth_profile.env_var_key appears to be a raw secret/token value.");
        }
    }

    public function isAuthoritative(): bool
    {
        return $this->authorityTier <= 2;
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $sanitizedAuth = [
            'requires_auth' => $this->authProfile['requires_auth'] ?? false,
            'auth_type' => $this->authProfile['auth_type'] ?? 'none',
        ];
        if (($this->authProfile['requires_auth'] ?? false) === true && isset($this->authProfile['env_var_key'])) {
            $sanitizedAuth['env_var_key'] = (string)$this->authProfile['env_var_key'];
        }

        return [
            'source_id' => $this->sourceId,
            'official_name' => $this->officialName,
            'description' => $this->description,
            'owner' => $this->owner,
            'canonical_url' => $this->canonicalUrl,
            'domains' => $this->domains,
            'subdomains' => $this->subdomains,
            'authority_tier' => $this->authorityTier,
            'source_designation' => $this->sourceDesignation,
            'category_type' => $this->categoryType,
            'supported_languages' => $this->supportedLanguages,
            'supported_formats' => $this->supportedFormats,
            'access_mechanisms' => $this->accessMechanisms,
            'auth_profile' => $sanitizedAuth,
            'cost_tier' => $this->costTier,
            'licensing' => $this->licensing,
            'rate_limits' => $this->rateLimits,
            'freshness_policy' => $this->freshnessPolicy,
            'status' => $this->status,
            'last_verified_at' => $this->lastVerifiedAt,
            'classification_evidence' => $this->classificationEvidence,
            'verification_profile' => $this->verificationProfile,
            'independence_profile' => $this->independenceProfile,
            'applicability_profile' => $this->applicabilityProfile,
            'provenance_rules' => $this->provenanceRules,
            'fallback_source_ids' => $this->fallbackSourceIds,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['source_id']) || !is_string($data['source_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'source_id' in SourceDefinition data.");
        }
        if (!isset($data['official_name']) || !is_string($data['official_name'])) {
            throw new InvalidArgumentException("Missing or invalid 'official_name' in SourceDefinition data.");
        }
        if (!isset($data['canonical_url']) || !is_string($data['canonical_url'])) {
            throw new InvalidArgumentException("Missing or invalid 'canonical_url' in SourceDefinition data.");
        }

        return new self(
            sourceId: (string)$data['source_id'],
            officialName: (string)$data['official_name'],
            description: (string)($data['description'] ?? ''),
            owner: (array)($data['owner'] ?? []),
            canonicalUrl: (string)$data['canonical_url'],
            domains: (array)($data['domains'] ?? []),
            subdomains: (array)($data['subdomains'] ?? []),
            authorityTier: (int)($data['authority_tier'] ?? 1),
            sourceDesignation: (string)($data['source_designation'] ?? self::DESIGNATION_PRIMARY),
            categoryType: (string)($data['category_type'] ?? self::CATEGORY_KNOWLEDGE_SOURCE),
            supportedLanguages: (array)($data['supported_languages'] ?? ['en']),
            supportedFormats: (array)($data['supported_formats'] ?? ['application/json', 'text/html']),
            accessMechanisms: (array)($data['access_mechanisms'] ?? []),
            authProfile: (array)($data['auth_profile'] ?? ['requires_auth' => false, 'auth_type' => 'none']),
            costTier: (string)($data['cost_tier'] ?? 'free_public'),
            licensing: (array)($data['licensing'] ?? ['license_type' => 'Open Data', 'reuse_permitted' => true, 'attribution_required' => false]),
            rateLimits: (array)($data['rate_limits'] ?? ['requests_per_minute' => 60, 'burst_limit' => 10]),
            freshnessPolicy: (array)($data['freshness_policy'] ?? ['max_cache_ttl_hours' => 168, 'revalidation_trigger' => 'weekly_168h']),
            status: (string)($data['status'] ?? self::STATUS_ACTIVE),
            lastVerifiedAt: (string)($data['last_verified_at'] ?? ''),
            classificationEvidence: (string)($data['classification_evidence'] ?? ''),
            verificationProfile: (array)($data['verification_profile'] ?? []),
            independenceProfile: (array)($data['independence_profile'] ?? []),
            applicabilityProfile: (array)($data['applicability_profile'] ?? []),
            provenanceRules: (array)($data['provenance_rules'] ?? ['retains_upstream_url' => true, 'retains_publisher_identity' => true, 'hashes_raw_payload' => true]),
            fallbackSourceIds: (array)($data['fallback_source_ids'] ?? [])
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return $this->toArray();
    }
}
