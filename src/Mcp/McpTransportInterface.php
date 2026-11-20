<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

/**
 * Transport contract for transmitting and receiving MCP JSON-RPC messages.
 */
interface McpTransportInterface
{
    /**
     * Sends a message to the MCP peer.
     */
    public function send(McpMessage $message): void;

    /**
     * Receives the next message from the MCP peer with optional timeout.
     *
     * @param int $timeoutSeconds Timeout in seconds
     * @return McpMessage|null Received message or null if timed out
     */
    public function receive(int $timeoutSeconds = 10): ?McpMessage;

    /**
     * Closes the transport connection.
     */
    public function close(): void;

    /**
     * Checks if the transport is open and active.
     */
    public function isOpen(): bool;

    /**
     * Diagnostics regarding transport type and state.
     *
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array;
}
