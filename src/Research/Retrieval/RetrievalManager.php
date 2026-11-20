<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Retrieval;

use UniversalAgentOS\Exceptions\RetrievalSecurityException;
use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Contracts\SourceEligibilityEvaluatorInterface;
use UniversalAgentOS\Research\Intelligence\SourceEligibilityEvaluator;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Primary retrieval orchestrator integrating multiple transport providers and enforcing eligibility governance.
 */
class RetrievalManager
{
    /** @var array<string, RetrievalProviderInterface> */
    private array $providers = [];
    private SourceEligibilityEvaluatorInterface $eligibilityEvaluator;

    /**
     * @param array<int, RetrievalProviderInterface> $providers
     */
    public function __construct(
        array $providers = [],
        ?SourceEligibilityEvaluatorInterface $eligibilityEvaluator = null
    ) {
        $this->eligibilityEvaluator = $eligibilityEvaluator ?? new SourceEligibilityEvaluator();
        foreach ($providers as $provider) {
            $this->registerProvider($provider);
        }
    }

    public function registerProvider(RetrievalProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    /**
     * @return array<string, RetrievalProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Retrieves technical content from a source, strictly evaluating source eligibility fail-closed.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed> Structured document payload
     */
    public function retrieve(string $target, SourceDefinition $source, array $options = []): array
    {
        $skipEligibility = (bool)($options['skip_eligibility_check'] ?? false);
        if (!$skipEligibility) {
            $decision = $this->eligibilityEvaluator->evaluateEligibility($source, $options);
            if (!$decision->isEligible && !$decision->isConditionallyEligible()) {
                $reason = implode('; ', $decision->violations);
                throw new RetrievalSecurityException(
                    "Retrieval blocked by eligibility governance for source '{$source->sourceId}': Status '{$decision->status}'. Violations: {$reason}"
                );
            }
        }

        $provider = $this->resolveProviderForSource($source, $options);
        $payload = $provider->retrieve($target, $source, $options);

        // Security check: ensure content is string and hash matches
        $content = (string)($payload['content'] ?? '');
        $expectedHash = hash('sha256', $content);
        if (($payload['hash'] ?? '') !== $expectedHash) {
            throw new RetrievalSecurityException("Payload cryptographic integrity verification failed for source '{$source->sourceId}'");
        }

        return $payload;
    }

    /**
     * Resolves an appropriate retrieval provider supporting the authoritative source definition.
     */
    public function resolveProviderForSource(SourceDefinition $source, array $options = []): RetrievalProviderInterface
    {
        $preferred = $options['preferred_provider'] ?? null;
        if ($preferred !== null && isset($this->providers[$preferred])) {
            $p = $this->providers[$preferred];
            if ($p->supports($source)) {
                return $p;
            }
        }

        foreach ($this->providers as $p) {
            if ($p->supports($source)) {
                return $p;
            }
        }

        throw new RetrievalSecurityException("No registered retrieval provider supports source '{$source->sourceId}'");
    }
}
