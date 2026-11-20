<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Universal MCP Client interface supporting both local STDIO and remote Streamable HTTP transports.
 */
interface McpClientInterface
{
    /**
     * Connects to the external MCP server and negotiates capabilities.
     *
     * @return array<string, mixed> Server initialization response and capabilities
     */
    public function connect(): array;

    /**
     * Discovers tools registered on the connected MCP server.
     *
     * @return array<int, array<string, mixed>> List of tool specifications
     */
    public function discoverTools(): array;

    /**
     * Invokes a specific tool on the connected MCP server.
     *
     * @param string $toolName Name of the remote tool
     * @param array<string, mixed> $arguments Tool arguments
     * @return array<string, mixed> Result returned by the tool
     */
    public function callTool(string $toolName, array $arguments = []): array;

    /**
     * Gracefully disconnects and terminates process or network connection.
     */
    public function disconnect(): void;

    /**
     * Checks if the client currently maintains an active connection.
     */
    public function isConnected(): bool;

    /**
     * Returns diagnostics regarding transport, endpoint, and connection latency.
     *
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array;
}
