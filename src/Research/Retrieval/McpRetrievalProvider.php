<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Retrieval;

use UniversalAgentOS\Contracts\McpClientInterface;
use UniversalAgentOS\Exceptions\RetrievalSecurityException;
use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * MCP research bridge retrieval provider querying external tools via MCP protocol.
 */
class McpRetrievalProvider implements RetrievalProviderInterface
{
    public const PROVIDER_NAME = 'mcp_server';

    /**
     * @param array<string, McpClientInterface> $clients Registered MCP clients keyed by server ID
     */
    public function __construct(
        private array $clients = []
    ) {}

    public function registerClient(string $serverId, McpClientInterface $client): void
    {
        $this->clients[$serverId] = $client;
    }

    public function getName(): string
    {
        return self::PROVIDER_NAME;
    }

    public function supports(SourceDefinition $source): bool
    {
        foreach ($source->accessMechanisms as $mech) {
            if (($mech['mechanism_type'] ?? '') === self::PROVIDER_NAME && ($mech['is_active'] ?? false)) {
                return true;
            }
        }
        return false;
    }

    public function retrieve(string $target, SourceDefinition $source, array $options = []): array
    {
        $mechanism = $this->resolveMechanism($source);
        $serverId = (string)($mechanism['mcp_server_id'] ?? '');

        if ($serverId === '' || !isset($this->clients[$serverId])) {
            throw new RetrievalSecurityException("MCP retrieval failed: No registered active McpClient found for server ID '{$serverId}'");
        }

        $client = $this->clients[$serverId];
        if (!$client->isConnected()) {
            $client->connect();
        }

        $toolName = (string)($options['tool_name'] ?? 'fetch');
        $arguments = (array)($options['arguments'] ?? ['url' => $target !== '' ? $target : $mechanism['endpoint_url']]);

        $result = $client->callTool($toolName, $arguments);
        $content = '';
        if (isset($result['content'])) {
            if (is_string($result['content'])) {
                $content = $result['content'];
            } elseif (is_array($result['content'])) {
                $parts = [];
                foreach ($result['content'] as $block) {
                    if (is_array($block) && isset($block['text'])) {
                        $parts[] = (string)$block['text'];
                    }
                }
                $content = implode("\n\n", $parts);
            }
        } elseif (isset($result['text'])) {
            $content = (string)$result['text'];
        } else {
            $content = (string)json_encode($result, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        if (trim($content) === '') {
            throw new RetrievalSecurityException("MCP server '{$serverId}' returned empty content for tool '{$toolName}'");
        }

        return [
            'content' => $content,
            'mime_type' => 'text/plain',
            'headers' => ['x-mcp-server-id' => $serverId, 'x-mcp-tool' => $toolName],
            'status_code' => 200,
            'hash' => hash('sha256', $content),
            'url' => $arguments['url'] ?? $mechanism['endpoint_url'],
            'source_id' => $source->sourceId,
            'retrieved_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMechanism(SourceDefinition $source): array
    {
        foreach ($source->accessMechanisms as $mech) {
            if (($mech['mechanism_type'] ?? '') === self::PROVIDER_NAME && ($mech['is_active'] ?? false)) {
                return $mech;
            }
        }
        throw new RetrievalSecurityException("No active 'mcp_server' mechanism found in source '{$source->sourceId}'");
    }
}
