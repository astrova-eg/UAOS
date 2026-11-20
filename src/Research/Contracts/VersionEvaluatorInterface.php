<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\VersionEvaluationResult;

/**
 * Version intelligence contract for sources, frameworks, and evidence.
 */
interface VersionEvaluatorInterface
{
    /**
     * Evaluates a version identifier in the context of an engineering source.
     *
     * @param string $version Version string (e.g., '8.2.0', 'v1.4', '2026.11')
     * @param SourceDefinition|null $source Source context (if applicable)
     * @param array<string, mixed> $context Project or environment context
     * @return VersionEvaluationResult Comprehensive version analysis
     */
    public function evaluateVersion(
        string $version,
        ?SourceDefinition $source = null,
        array $context = []
    ): VersionEvaluationResult;

    /**
     * Compares two version strings deterministically.
     *
     * @return int -1 if $v1 < $v2, 0 if $v1 == $v2, 1 if $v1 > $v2
     */
    public function compare(string $v1, string $v2): int;

    /**
     * Detects version scheme (e.g., semver, calver, ordinal, date, unknown).
     */
    public function detectScheme(string $version): string;
}
