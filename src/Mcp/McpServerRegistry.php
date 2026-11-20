<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Exceptions\McpException;

/**
 * Manages multiple external MCP server configurations, tool permissions, and connection lifecycles.
 */
class McpServerRegistry
{
    private PathResolverInterface $pathResolver;
    /** @var array<string, array<string, mixed>> */
    private array $servers = [];
    /** @var array<string, McpClient> */
    private array $activeClients = [];

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->loadFromConfig();
    }

    /**
     * Registers a new or updated MCP server configuration.
     *
     * @param array<string, mixed> $serverConfig
     */
    public function registerServer(string $serverId, array $serverConfig): void
    {
        $serverConfig['id'] = $serverId;
        $serverConfig['enabled'] = (bool)($serverConfig['enabled'] ?? true);
        $serverConfig['allowed_tools'] = (array)($serverConfig['allowed_tools'] ?? []);
        $serverConfig['denied_tools'] = (array)($serverConfig['denied_tools'] ?? []);
        $this->servers[$serverId] = $serverConfig;
    }

    public function unregisterServer(string $serverId): void
    {
        unset($this->servers[$serverId]);
        if (isset($this->activeClients[$serverId])) {
            $this->activeClients[$serverId]->disconnect();
            unset($this->activeClients[$serverId]);
        }
    }

    public function enableServer(string $serverId): void
    {
        if (isset($this->servers[$serverId])) {
            $this->servers[$serverId]['enabled'] = true;
        }
    }

    public function disableServer(string $serverId): void
    {
        if (isset($this->servers[$serverId])) {
            $this->servers[$serverId]['enabled'] = false;
        }
        if (isset($this->activeClients[$serverId])) {
            $this->activeClients[$serverId]->disconnect();
            unset($this->activeClients[$serverId]);
        }
    }

    public function isToolAllowed(string $serverId, string $toolName): bool
    {
        $config = $this->servers[$serverId] ?? null;
        if ($config === null || !($config['enabled'] ?? false)) {
            return false;
        }

        $denied = (array)($config['denied_tools'] ?? []);
        if (in_array($toolName, $denied, true)) {
            return false;
        }

        $allowed = (array)($config['allowed_tools'] ?? []);
        if (!empty($allowed) && !in_array($toolName, $allowed, true)) {
            return false;
        }

        return true;
    }

    public function getClient(string $serverId): McpClient
    {
        if (isset($this->activeClients[$serverId])) {
            return $this->activeClients[$serverId];
        }

        $config = $this->servers[$serverId] ?? null;
        if ($config === null) {
            throw new McpException("MCP server '{$serverId}' is not registered.");
        }

        if (!($config['enabled'] ?? false)) {
            throw new McpException("MCP server '{$serverId}' is disabled.");
        }

        $transportType = (string)($config['transport'] ?? 'stdio');
        if ($transportType === 'stdio') {
            $cmd = (string)($config['command'] ?? 'php');
            $args = (array)($config['args'] ?? []);
            $env = (array)($config['env'] ?? []);
            $transport = new StdioTransport($cmd, $args, $env);
        } elseif ($transportType === 'http' || $transportType === 'streamable_http') {
            $url = (string)($config['url'] ?? '');
            $headers = (array)($config['headers'] ?? []);
            $transport = new StreamableHttpTransport($url, $headers);
        } else {
            throw new McpException("Unsupported transport type: '{$transportType}'");
        }

        $client = new McpClient($transport);
        $this->activeClients[$serverId] = $client;
        return $client;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->servers;
    }

    private function loadFromConfig(): void
    {
        $configFile = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'config.json';
        if (file_exists($configFile)) {
            $raw = file_get_contents($configFile);
            $data = is_string($raw) ? json_decode($raw, true) : [];
            $mcpServers = (array)($data['mcp']['servers'] ?? []);
            foreach ($mcpServers as $id => $cfg) {
                if (is_array($cfg)) {
                    $this->registerServer((string)$id, $cfg);
                }
            }
        }
    }
}
