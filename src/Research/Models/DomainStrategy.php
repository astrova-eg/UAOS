<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object encapsulating domain-specific research strategy, source selection,
 * quorum requirements, and evidential verification rules for a taxonomy domain (A-P).
 */
readonly class DomainStrategy implements JsonSerializable
{
    /**
     * @param string $domainCode Single letter A-P identifying the domain
     * @param string $name Authoritative domain title
     * @param string $epistemologicalCategory Nature of knowledge (e.g. formal, empirical, statutory)
     * @param array<int, string> $primarySourceCategories Required or preferred source categories
     * @param string $minimumDepth Minimum recommended research depth (L0-L4)
     * @param int $minimumQuorum Minimum distinct sources required for quorum
     * @param int $defaultTtlHours Default cache validity TTL in hours
     * @param array<int, string> $verificationRules Specialized evidence verification rules
     * @param string $overlayPolicy Identifier of specialized policy overlay (if applicable)
     */
    public function __construct(
        public string $domainCode,
        public string $name,
        public string $epistemologicalCategory,
        public array $primarySourceCategories,
        public string $minimumDepth = DepthProfile::LEVEL_L2,
        public int $minimumQuorum = 2,
        public int $defaultTtlHours = 168,
        public array $verificationRules = [],
        public string $overlayPolicy = 'pol-base-global'
    ) {
        $trimmedCode = strtoupper(trim($this->domainCode));
        if ($trimmedCode === '' || strlen($trimmedCode) !== 1 || $trimmedCode < 'A' || $trimmedCode > 'P') {
            throw new InvalidArgumentException("Domain code must be a single letter from 'A' to 'P', got: '{$this->domainCode}'");
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Domain name cannot be empty.');
        }

        if (!in_array($this->minimumDepth, DepthProfile::VALID_LEVELS, true)) {
            throw new InvalidArgumentException("Invalid minimumDepth '{$this->minimumDepth}'.");
        }

        if ($this->minimumQuorum < 0) {
            throw new InvalidArgumentException("minimumQuorum cannot be negative: {$this->minimumQuorum}");
        }

        if ($this->defaultTtlHours <= 0) {
            throw new InvalidArgumentException("defaultTtlHours must be positive: {$this->defaultTtlHours}");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'domain_code' => $this->domainCode,
            'name' => $this->name,
            'epistemological_category' => $this->epistemologicalCategory,
            'primary_source_categories' => $this->primarySourceCategories,
            'minimum_depth' => $this->minimumDepth,
            'minimum_quorum' => $this->minimumQuorum,
            'default_ttl_hours' => $this->defaultTtlHours,
            'verification_rules' => $this->verificationRules,
            'overlay_policy' => $this->overlayPolicy,
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
