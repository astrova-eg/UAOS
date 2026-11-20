<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Registry;

use InvalidArgumentException;
use UniversalAgentOS\Research\Contracts\SourceEligibilityEvaluatorInterface;
use UniversalAgentOS\Research\Contracts\SourceRegistryInterface;
use UniversalAgentOS\Research\Contracts\SourceRepositoryInterface;
use UniversalAgentOS\Research\Contracts\SourceValidatorInterface;
use UniversalAgentOS\Research\Intelligence\SourceEligibilityEvaluator;
use UniversalAgentOS\Research\Intelligence\TrustIntelligenceEngine;
use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;
use UniversalAgentOS\Research\Models\SourceLifecycleState;
use UniversalAgentOS\Research\Models\TrustEvaluationResult;
use UniversalAgentOS\Research\Repository\SourceRepository;
use UniversalAgentOS\Research\Validation\SourceDefinitionValidator;

/**
 * Production-grade Source Registry managing knowledge source lifecycles and governance.
 */
class SourceRegistry implements SourceRegistryInterface
{
    private SourceRepositoryInterface $repository;
    private SourceValidatorInterface $validator;
    private SourceEligibilityEvaluatorInterface $eligibilityEvaluator;
    private TrustIntelligenceEngine $trustEngine;

    public function __construct(
        ?SourceRepositoryInterface $repository = null,
        ?SourceValidatorInterface $validator = null,
        ?SourceEligibilityEvaluatorInterface $eligibilityEvaluator = null,
        ?TrustIntelligenceEngine $trustEngine = null
    ) {
        $this->repository = $repository ?? new SourceRepository();
        $this->validator = $validator ?? new SourceDefinitionValidator();
        $this->eligibilityEvaluator = $eligibilityEvaluator ?? new SourceEligibilityEvaluator(validator: $this->validator);
        $this->trustEngine = $trustEngine ?? new TrustIntelligenceEngine();
    }

    public function register(SourceDefinition $source, string $actor = 'system'): SourceEligibilityDecision
    {
        // 1. Prevent duplicate identities
        if ($this->repository->has($source->sourceId)) {
            throw new InvalidArgumentException(
                "Source ID conflict: A source with identity '{$source->sourceId}' is already registered."
            );
        }

        // 2. Validate definition against canonical schema
        $this->validator->assertValid($source);

        // 3. Persist source definition and initialize lifecycle state
        $this->repository->save($source);
        $initialState = ($source->status === SourceDefinition::STATUS_DEPRECATED)
            ? SourceLifecycleState::STATE_DEPRECATED
            : SourceLifecycleState::STATE_ACTIVE;
        $this->repository->setLifecycleState($source->sourceId, $initialState);

        // 4. Record registration audit
        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $source->sourceId,
            action: SourceAuditRecord::ACTION_REGISTER,
            actor: $actor,
            fromState: null,
            toState: $initialState,
            reason: 'Initial source registration and verification.',
            details: ['authority_tier' => $source->authorityTier, 'domains' => $source->domains]
        ));

        // 5. Evaluate eligibility (A source is never eligible merely because it was saved)
        $decision = $this->eligibilityEvaluator->evaluateEligibility($source, ['lifecycle_state' => $initialState]);

        // 6. Record eligibility decision in audit trail
        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $source->sourceId,
            action: SourceAuditRecord::ACTION_ELIGIBILITY_EVALUATION,
            actor: $actor,
            reason: 'Initial post-registration eligibility evaluation.',
            details: $decision->toArray()
        ));

        return $decision;
    }

    public function update(SourceDefinition $source, string $actor = 'system', string $reason = ''): SourceEligibilityDecision
    {
        $existing = $this->repository->get($source->sourceId);
        if ($existing === null) {
            throw new InvalidArgumentException("Cannot update unregistered source '{$source->sourceId}'.");
        }

        $currentLifecycle = $this->repository->getLifecycleState($source->sourceId);
        if (SourceLifecycleState::isTerminal($currentLifecycle)) {
            throw new InvalidArgumentException(
                "Cannot update source '{$source->sourceId}' in terminal state '{$currentLifecycle}'."
            );
        }

        $this->validator->assertValid($source);
        $this->repository->save($source);

        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $source->sourceId,
            action: SourceAuditRecord::ACTION_UPDATE,
            actor: $actor,
            fromState: $currentLifecycle,
            toState: $currentLifecycle,
            reason: $reason !== '' ? $reason : 'Source definition updated.',
            details: ['previous_verified_at' => $existing->lastVerifiedAt, 'new_verified_at' => $source->lastVerifiedAt]
        ));

        return $this->eligibilityEvaluator->evaluateEligibility($source, ['lifecycle_state' => $currentLifecycle]);
    }

    public function get(string $sourceId): ?SourceDefinition
    {
        return $this->repository->get($sourceId);
    }

    public function has(string $sourceId): bool
    {
        return $this->repository->has($sourceId);
    }

    public function transitionState(
        string $sourceId,
        string $newState,
        string $reason,
        string $actor = 'system'
    ): SourceEligibilityDecision {
        $source = $this->repository->get($sourceId);
        if ($source === null) {
            throw new InvalidArgumentException("Cannot transition non-existent source '{$sourceId}'.");
        }

        $currentState = $this->repository->getLifecycleState($sourceId);

        // Enforce lifecycle transition rules
        SourceLifecycleState::assertValidTransition($currentState, $newState);
        $this->repository->setLifecycleState($sourceId, $newState);

        // Synchronize operational status if deprecated or reactivated
        if ($newState === SourceLifecycleState::STATE_DEPRECATED && $source->status !== SourceDefinition::STATUS_DEPRECATED) {
            $data = $source->toArray();
            $data['status'] = SourceDefinition::STATUS_DEPRECATED;
            $source = SourceDefinition::fromArray($data);
            $this->repository->save($source);
        }

        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $sourceId,
            action: SourceAuditRecord::ACTION_TRANSITION,
            actor: $actor,
            fromState: $currentState,
            toState: $newState,
            reason: $reason,
            details: ['timestamp' => gmdate('Y-m-d\TH:i:s\Z')]
        ));

        return $this->eligibilityEvaluator->evaluateEligibility($source, ['lifecycle_state' => $newState]);
    }

    public function evaluateEligibility(string $sourceId, array $context = []): SourceEligibilityDecision
    {
        $source = $this->repository->get($sourceId);
        if ($source === null) {
            throw new InvalidArgumentException("Cannot evaluate non-existent source '{$sourceId}'.");
        }

        $lifecycleState = $this->repository->getLifecycleState($sourceId);
        $context['lifecycle_state'] = $context['lifecycle_state'] ?? $lifecycleState;

        $decision = $this->eligibilityEvaluator->evaluateEligibility($source, $context);

        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $sourceId,
            action: SourceAuditRecord::ACTION_ELIGIBILITY_EVALUATION,
            actor: $context['actor'] ?? 'system',
            reason: $context['reason'] ?? 'Periodic or on-demand eligibility evaluation.',
            details: $decision->toArray()
        ));

        return $decision;
    }

    public function evaluateTrust(string $sourceId, array $context = []): TrustEvaluationResult
    {
        $source = $this->repository->get($sourceId);
        if ($source === null) {
            throw new InvalidArgumentException("Cannot evaluate trust for non-existent source '{$sourceId}'.");
        }

        $result = $this->trustEngine->evaluateSourceTrust($source, $context);

        $this->repository->recordAudit(SourceAuditRecord::create(
            sourceId: $sourceId,
            action: SourceAuditRecord::ACTION_TRUST_EVALUATION,
            actor: $context['actor'] ?? 'system',
            reason: 'Trust evaluation executed.',
            details: $result->toArray()
        ));

        return $result;
    }

    public function find(array $criteria = []): array
    {
        $sources = $this->repository->all($criteria);

        if (isset($criteria['is_eligible'])) {
            $wantEligible = (bool)$criteria['is_eligible'];
            $sources = array_filter($sources, function (SourceDefinition $src) use ($wantEligible) {
                $lifecycleState = $this->repository->getLifecycleState($src->sourceId);
                $decision = $this->eligibilityEvaluator->evaluateEligibility($src, ['lifecycle_state' => $lifecycleState]);
                return $decision->isEligible === $wantEligible;
            });
        }

        return array_values($sources);
    }

    public function getAuditHistory(string $sourceId): array
    {
        return $this->repository->getAuditHistory($sourceId);
    }
}
