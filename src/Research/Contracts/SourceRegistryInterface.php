<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;
use UniversalAgentOS\Research\Models\TrustEvaluationResult;

/**
 * Authoritative registry contract for managing engineering knowledge source lifecycles.
 */
interface SourceRegistryInterface
{
    /**
     * Registers a new source definition after strict validation.
     *
     * @param SourceDefinition $source Source to register
     * @param string $actor Identity of the registering agent or operator
     * @return SourceEligibilityDecision Initial eligibility evaluation
     */
    public function register(SourceDefinition $source, string $actor = 'system'): SourceEligibilityDecision;

    /**
     * Updates an existing source definition.
     *
     * @param SourceDefinition $source Updated source definition
     * @param string $actor Identity of updating agent or operator
     * @param string $reason Purpose of update
     * @return SourceEligibilityDecision Re-evaluated eligibility decision
     */
    public function update(SourceDefinition $source, string $actor = 'system', string $reason = ''): SourceEligibilityDecision;

    /**
     * Retrieves a source definition by its unique identifier.
     */
    public function get(string $sourceId): ?SourceDefinition;

    /**
     * Checks if a source ID exists in the registry.
     */
    public function has(string $sourceId): bool;

    /**
     * Transitions a source to a new lifecycle state.
     *
     * @param string $sourceId Source identifier
     * @param string $newState Target lifecycle state (active, suspended, deprecated, revoked, archived)
     * @param string $reason Reason for transition
     * @param string $actor Operating agent or human
     * @return SourceEligibilityDecision Updated eligibility decision
     */
    public function transitionState(string $sourceId, string $newState, string $reason, string $actor = 'system'): SourceEligibilityDecision;

    /**
     * Evaluates source eligibility under current governed policies.
     */
    public function evaluateEligibility(string $sourceId, array $context = []): SourceEligibilityDecision;

    /**
     * Evaluates source trust score and produces explainable result.
     */
    public function evaluateTrust(string $sourceId, array $context = []): TrustEvaluationResult;

    /**
     * Queries registered sources matching criteria.
     *
     * @param array<string, mixed> $criteria Filter criteria (domain, subdomain, category, status, tier, eligibility)
     * @return array<int, SourceDefinition>
     */
    public function find(array $criteria = []): array;

    /**
     * Retrieves audit and lifecycle transition history for a source.
     *
     * @return array<int, SourceAuditRecord>
     */
    public function getAuditHistory(string $sourceId): array;
}
