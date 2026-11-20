<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable value object holding version intelligence analysis.
 */
readonly class VersionEvaluationResult implements JsonSerializable
{
    public const SCHEME_SEMVER = 'semver';
    public const SCHEME_CALVER = 'calver';
    public const SCHEME_ORDINAL = 'ordinal';
    public const SCHEME_DATE = 'date';
    public const SCHEME_UNKNOWN = 'unknown';

    public const STATE_ACTIVE = 'active';
    public const STATE_DEPRECATED = 'deprecated';
    public const STATE_END_OF_LIFE = 'end_of_life';
    public const STATE_UNSUPPORTED = 'unsupported';
    public const STATE_UNKNOWN = 'unknown';

    public function __construct(
        public string $rawVersion,
        public string $scheme,
        public string $state,
        public bool $isOutdated,
        public bool $isSupported,
        public array $parsedParts = [],
        public array $compatibilityMetadata = [],
        public array $reasons = []
    ) {}

    public function isSemVer(): bool
    {
        return $this->scheme === self::SCHEME_SEMVER;
    }

    public function isEndOfLife(): bool
    {
        return $this->state === self::STATE_END_OF_LIFE;
    }

    public function toArray(): array
    {
        return [
            'raw_version' => $this->rawVersion,
            'scheme' => $this->scheme,
            'state' => $this->state,
            'is_outdated' => $this->isOutdated,
            'is_supported' => $this->isSupported,
            'parsed_parts' => $this->parsedParts,
            'compatibility_metadata' => $this->compatibilityMetadata,
            'reasons' => $this->reasons,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
