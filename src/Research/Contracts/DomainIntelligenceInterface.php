<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\DomainStrategy;

/**
 * Contract for domain-aware research intelligence and strategy resolution across domains A-P.
 */
interface DomainIntelligenceInterface
{
    /**
     * Retrieves the authoritative domain strategy for a domain code (A-P).
     */
    public function getDomainStrategy(string $domainCode): DomainStrategy;

    /**
     * Returns all 16 registered domain strategies keyed by domain letter (A-P).
     *
     * @return array<string, DomainStrategy>
     */
    public function getAllDomains(): array;

    /**
     * Resolves a multi-domain strategy applying strictness precedence.
     *
     * @param array<int, string> $domainCodes Domain letters involved in task
     * @return array<string, mixed> Merged domain execution profile
     */
    public function resolveMultiDomainStrategy(array $domainCodes): array;

    /**
     * Infers applicable domain codes from a natural language task prompt.
     *
     * @param string $prompt Task description or inquiry
     * @return array<int, string> Detected domain letters
     */
    public function classifyDomainsFromPrompt(string $prompt): array;
}
