<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\FreshnessEvaluationResult;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Freshness evaluation contract for research evidence and source currency.
 */
interface FreshnessEvaluatorInterface
{
    /**
     * Evaluates freshness of a timestamp against domain and source policies.
     *
     * @param string|null $timestamp ISO-8601 timestamp to evaluate
     * @param SourceDefinition|null $source Source context (if available)
     * @param array<string, mixed> $context Operational context (domain, ttl_override, reference_time)
     * @return FreshnessEvaluationResult Comprehensive freshness evaluation result
     */
    public function evaluateFreshness(
        ?string $timestamp,
        ?SourceDefinition $source = null,
        array $context = []
    ): FreshnessEvaluationResult;

    /**
     * Resolves the effective TTL in hours for a source in a given domain context.
     *
     * @param SourceDefinition|null $source Source definition
     * @param array<string, mixed> $context Operational context
     * @return int Effective TTL in hours
     */
    public function resolveEffectiveTtlHours(?SourceDefinition $source = null, array $context = []): int;
}
