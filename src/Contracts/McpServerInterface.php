<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Interface for UAOS MCP Server exposing controlled runtime capabilities via JSON-RPC.
 */
interface McpServerInterface
{
    /**
     * Registers a tool callable by MCP clients.
     *
     * @param string $name Unique tool name
     * @param string $description Tool purpose description
     * @param array<string, mixed> $inputSchema JSON schema for tool arguments
     * @param callable $handler Callable executing the tool
     * @param bool $requiresGate4 Whether invocation requires Gate 4 clearance
     */
    public function registerTool(
        string $name,
        string $description,
        array $inputSchema,
        callable $handler,
        bool $requiresGate4 = false
    ): void;

    /**
     * Handles a single raw JSON-RPC 2.0 request payload.
     *
     * @param string $rawJson Incoming JSON-RPC request
     * @return string Outgoing JSON-RPC response payload
     */
    public function handleJsonRpc(string $rawJson): string;

    /**
     * Starts listening on stdio (STDIN -> STDOUT loop) until EOF or termination signal.
     */
    public function listenStdio(): void;
}
