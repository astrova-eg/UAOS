<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Provider abstraction contract for external technical document and API retrieval.
 *
 * Implemented by transport adapters (HTTP direct, MCP server, structured API, git trees)
 * operating across the fail-closed retrieval boundary.
 */
interface RetrievalProviderInterface
{
    /**
     * Unique identifier/name of the retrieval provider (e.g. 'http_direct', 'mcp_server', 'structured_api').
     */
    public function getName(): string;

    /**
     * Evaluates whether this provider supports retrieving from the given source definition.
     *
     * @param SourceDefinition $source Authoritative source definition
     */
    public function supports(SourceDefinition $source): bool;

    /**
     * Retrieves raw content and document metadata from the target endpoint or query.
     *
     * @param string $target Target URL, endpoint, or query identifier
     * @param SourceDefinition $source Authoritative source definition
     * @param array<string, mixed> $options Provider-specific retrieval options
     * @return array<string, mixed> Structured document payload (content, mime_type, headers, status_code, hash)
     */
    public function retrieve(string $target, SourceDefinition $source, array $options = []): array;
}
