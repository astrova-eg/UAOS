<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;

/**
 * Contract for evaluating engineering source eligibility and governance clearance.
 */
interface SourceEligibilityEvaluatorInterface
{
    /**
     * Evaluates source eligibility against validation, trust, freshness, and governance rules.
     *
     * @param SourceDefinition $source Source to evaluate
     * @param array<string, mixed> $context Operational context
     * @return SourceEligibilityDecision Deterministic eligibility decision
     */
    public function evaluateEligibility(SourceDefinition $source, array $context = []): SourceEligibilityDecision;
}
