<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

use UniversalAgentOS\Contracts\McpClientInterface;
use UniversalAgentOS\Exceptions\McpException;

/**
 * Universal MCP Client supporting protocol initialization, tool discovery, and tool invocation.
 */
class McpClient implements McpClientInterface
{
    private McpTransportInterface $transport;
    private int $requestId = 1;
    private bool $connected = false;
    /** @var array<string, mixed> */
    private array $serverCapabilities = [];
    /** @var array<string, array<string, mixed>> */
    private array $discoveredTools = [];

    public function __construct(McpTransportInterface $transport)
    {
        $this->transport = $transport;
    }

    public function connect(): array
    {
        $id = $this->nextId();
        $initRequest = McpMessage::request($id, 'initialize', [
            'protocolVersion' => '2024-11-05',
            'capabilities' => [
                'tools' => ['listChanged' => true],
            ],
            'clientInfo' => [
                'name' => 'UniversalAgentOS MCP Client',
                'version' => '2.0.0',
            ],
        ]);

        $this->transport->send($initRequest);
        $response = $this->transport->receive(10);

        if ($response === null) {
            throw new McpException("Connection to MCP server timed out during initialization handshake.");
        }

        if ($response->error !== null) {
            throw new McpException("MCP server initialization failed: " . ($response->error['message'] ?? 'Unknown error'));
        }

        $this->connected = true;
        $this->serverCapabilities = (array)($response->result['capabilities'] ?? []);

        // Send notifications/initialized as required by protocol
        $this->transport->send(new McpMessage(jsonrpc: '2.0', method: 'notifications/initialized'));

        return (array)($response->result ?? []);
    }

    public function discoverTools(): array
    {
        if (!$this->connected) {
            $this->connect();
        }

        $id = $this->nextId();
        $this->transport->send(McpMessage::request($id, 'tools/list', []));
        $response = $this->transport->receive(10);

        if ($response === null) {
            throw new McpException("Tool discovery on MCP server timed out.");
        }

        if ($response->error !== null) {
            throw new McpException("Tool discovery failed: " . ($response->error['message'] ?? 'Unknown error'));
        }

        $tools = (array)($response->result['tools'] ?? []);
        $this->discoveredTools = [];
        foreach ($tools as $tool) {
            if (isset($tool['name'])) {
                $this->discoveredTools[$tool['name']] = $tool;
            }
        }

        return $tools;
    }

    public function callTool(string $toolName, array $arguments = []): array
    {
        if (!$this->connected) {
            $this->connect();
        }

        $id = $this->nextId();
        $this->transport->send(McpMessage::request($id, 'tools/call', [
            'name' => $toolName,
            'arguments' => $arguments,
        ]));

        $response = $this->transport->receive(15);
        if ($response === null) {
            throw new McpException("Invocation of tool '{$toolName}' timed out.");
        }

        if ($response->error !== null) {
            throw new McpException("MCP tool '{$toolName}' execution error: " . ($response->error['message'] ?? 'Unknown'));
        }

        $result = (array)($response->result ?? []);
        if (($result['isError'] ?? false) === true) {
            $content = (array)($result['content'] ?? []);
            $msg = !empty($content) ? (string)($content[0]['text'] ?? 'Tool returned isError') : 'Tool error';
            throw new McpException("Tool '{$toolName}' failed: {$msg}");
        }

        return $result;
    }

    public function disconnect(): void
    {
        $this->transport->close();
        $this->connected = false;
        $this->discoveredTools = [];
    }

    public function isConnected(): bool
    {
        return $this->connected && $this->transport->isOpen();
    }

    public function getDiagnostics(): array
    {
        return [
            'is_connected' => $this->isConnected(),
            'server_capabilities' => $this->serverCapabilities,
            'discovered_tools_count' => count($this->discoveredTools),
            'transport_diagnostics' => $this->transport->getDiagnostics(),
        ];
    }

    private function nextId(): int
    {
        return $this->requestId++;
    }
}
