<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing an adaptive research depth configuration (L0 to L4).
 *
 * Defines resource budgets, minimum source quorums, execution timeouts,
 * and mandatory analytical requirements tailored to task complexity and risk.
 */
readonly class DepthProfile implements JsonSerializable
{
    public const LEVEL_L0 = 'L0';
    public const LEVEL_L1 = 'L1';
    public const LEVEL_L2 = 'L2';
    public const LEVEL_L3 = 'L3';
    public const LEVEL_L4 = 'L4';

    public const VALID_LEVELS = [
        self::LEVEL_L0,
        self::LEVEL_L1,
        self::LEVEL_L2,
        self::LEVEL_L3,
        self::LEVEL_L4,
    ];

    /**
     * @param string $level Research depth level (L0, L1, L2, L3, L4)
     * @param int $minSources Minimum distinct external sources required for quorum
     * @param int $maxQueries Maximum external queries permitted across providers
     * @param int $maxExecutionTimeoutSeconds Maximum execution time budget in seconds
     * @param int $maxContextTokens Maximum token budget allocated for retrieved evidence
     * @param float $maxDownloadMb Maximum download volume ceiling in megabytes
     * @param bool $requiresTradeoffMatrix Whether multi-criteria trade-off matrix is mandatory
     * @param bool $requiresConflictAudit Whether formal contradiction & vulnerability audit is mandatory
     * @param string $rationale Human-readable explanation of depth assignment
     */
    public function __construct(
        public string $level,
        public int $minSources = 0,
        public int $maxQueries = 0,
        public int $maxExecutionTimeoutSeconds = 0,
        public int $maxContextTokens = 0,
        public float $maxDownloadMb = 0.0,
        public bool $requiresTradeoffMatrix = false,
        public bool $requiresConflictAudit = false,
        public string $rationale = ''
    ) {
        if (!in_array($this->level, self::VALID_LEVELS, true)) {
            throw new InvalidArgumentException(
                "Invalid research depth level '{$this->level}'. Allowed: " . implode(', ', self::VALID_LEVELS)
            );
        }

        if ($this->minSources < 0) {
            throw new InvalidArgumentException("minSources cannot be negative: {$this->minSources}");
        }

        if ($this->maxQueries < 0) {
            throw new InvalidArgumentException("maxQueries cannot be negative: {$this->maxQueries}");
        }

        if ($this->maxExecutionTimeoutSeconds < 0) {
            throw new InvalidArgumentException("maxExecutionTimeoutSeconds cannot be negative: {$this->maxExecutionTimeoutSeconds}");
        }

        if ($this->maxContextTokens < 0) {
            throw new InvalidArgumentException("maxContextTokens cannot be negative: {$this->maxContextTokens}");
        }

        if ($this->maxDownloadMb < 0.0) {
            throw new InvalidArgumentException("maxDownloadMb cannot be negative: {$this->maxDownloadMb}");
        }
    }

    /**
     * Factory method creating a calibrated DepthProfile for the given level.
     */
    public static function forLevel(string $level, string $rationale = ''): self
    {
        return match ($level) {
            self::LEVEL_L0 => self::l0($rationale),
            self::LEVEL_L1 => self::l1($rationale),
            self::LEVEL_L2 => self::l2($rationale),
            self::LEVEL_L3 => self::l3($rationale),
            self::LEVEL_L4 => self::l4($rationale),
            default => throw new InvalidArgumentException(
                "Invalid research depth level '{$level}'. Allowed: " . implode(', ', self::VALID_LEVELS)
            ),
        };
    }

    public static function l0(string $rationale = 'Level 0: No external research. Local codebase only.'): self
    {
        return new self(
            level: self::LEVEL_L0,
            minSources: 0,
            maxQueries: 0,
            maxExecutionTimeoutSeconds: 0,
            maxContextTokens: 0,
            maxDownloadMb: 0.0,
            requiresTradeoffMatrix: false,
            requiresConflictAudit: false,
            rationale: $rationale
        );
    }

    public static function l1(string $rationale = 'Level 1: Basic verification against single official source.'): self
    {
        return new self(
            level: self::LEVEL_L1,
            minSources: 1,
            maxQueries: 1,
            maxExecutionTimeoutSeconds: 5,
            maxContextTokens: 500,
            maxDownloadMb: 0.5,
            requiresTradeoffMatrix: false,
            requiresConflictAudit: false,
            rationale: $rationale
        );
    }

    public static function l2(string $rationale = 'Level 2: Standard research across official documentation and release notes.'): self
    {
        return new self(
            level: self::LEVEL_L2,
            minSources: 2,
            maxQueries: 3,
            maxExecutionTimeoutSeconds: 15,
            maxContextTokens: 1500,
            maxDownloadMb: 1.5,
            requiresTradeoffMatrix: false,
            requiresConflictAudit: false,
            rationale: $rationale
        );
    }

    public static function l3(string $rationale = 'Level 3: Deep architectural research with structured trade-off evaluation.'): self
    {
        return new self(
            level: self::LEVEL_L3,
            minSources: 3,
            maxQueries: 8,
            maxExecutionTimeoutSeconds: 45,
            maxContextTokens: 3500,
            maxDownloadMb: 4.0,
            requiresTradeoffMatrix: true,
            requiresConflictAudit: false,
            rationale: $rationale
        );
    }

    public static function l4(string $rationale = 'Level 4: Critical research with mandatory vulnerability audit and conflict resolution.'): self
    {
        return new self(
            level: self::LEVEL_L4,
            minSources: 4,
            maxQueries: 15,
            maxExecutionTimeoutSeconds: 90,
            maxContextTokens: 6000,
            maxDownloadMb: 8.0,
            requiresTradeoffMatrix: true,
            requiresConflictAudit: true,
            rationale: $rationale
        );
    }

    /**
     * Whether this depth profile mandates external research queries.
     */
    public function requiresExternalResearch(): bool
    {
        return $this->level !== self::LEVEL_L0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'level' => $this->level,
            'min_sources' => $this->minSources,
            'max_queries' => $this->maxQueries,
            'max_execution_timeout_seconds' => $this->maxExecutionTimeoutSeconds,
            'max_context_tokens' => $this->maxContextTokens,
            'max_download_mb' => $this->maxDownloadMb,
            'requires_tradeoff_matrix' => $this->requiresTradeoffMatrix,
            'requires_conflict_audit' => $this->requiresConflictAudit,
            'rationale' => $this->rationale,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['level']) || !is_string($data['level'])) {
            throw new InvalidArgumentException("Missing or invalid 'level' in DepthProfile data.");
        }

        return new self(
            level: $data['level'],
            minSources: (int)($data['min_sources'] ?? 0),
            maxQueries: (int)($data['max_queries'] ?? 0),
            maxExecutionTimeoutSeconds: (int)($data['max_execution_timeout_seconds'] ?? 0),
            maxContextTokens: (int)($data['max_context_tokens'] ?? 0),
            maxDownloadMb: (float)($data['max_download_mb'] ?? 0.0),
            requiresTradeoffMatrix: (bool)($data['requires_tradeoff_matrix'] ?? false),
            requiresConflictAudit: (bool)($data['requires_conflict_audit'] ?? false),
            rationale: (string)($data['rationale'] ?? '')
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
