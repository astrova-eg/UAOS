<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

/**
 * Dedicated runnable local MCP test server fixture providing controlled test tools.
 */
class LocalTestMcpServer
{
    private string $testWorkspace;
    private McpServer $server;

    public function __construct(?string $testWorkspace = null)
    {
        $this->testWorkspace = $testWorkspace ?? (sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_mcp_test_' . bin2hex(random_bytes(4)));
        if (!is_dir($this->testWorkspace)) {
            mkdir($this->testWorkspace, 0755, true);
        }

        $dummyResolver = new class($this->testWorkspace) implements \UniversalAgentOS\Contracts\PathResolverInterface {
            private string $ws;
            public function __construct(string $ws) { $this->ws = $ws; }
            public function getPackageResourcesDir(): string { return $this->ws; }
            public function getProjectRootDir(): string { return $this->ws; }
            public function getProjectStorageDir(): string { return $this->ws . '/.agent-os'; }
            public function getProjectConfigDir(): string { return $this->ws . '/.agent-os/config'; }
            public function getProjectStateDir(): string { return $this->ws . '/.agent-os/state'; }
            public function getProjectReportsDir(): string { return $this->ws . '/.agent-os/reports'; }
            public function getProjectAuthorizationsDir(): string { return $this->ws . '/.agent-os/authorizations'; }
            public function resolveProjectPath(string $r): string { return $this->ws . '/' . ltrim($r, '/'); }
            public function resolveResourcePath(string $r): string { return $this->ws . '/' . ltrim($r, '/'); }
        };

        $this->server = new McpServer($dummyResolver);
        $this->registerTestTools();
    }

    public function handleJsonRpc(string $rawJson): string
    {
        return $this->server->handleJsonRpc($rawJson);
    }

    public function listenStdio(): void
    {
        $this->server->listenStdio();
    }

    public function getTestWorkspace(): string
    {
        return $this->testWorkspace;
    }

    public function cleanup(): void
    {
        if (is_dir($this->testWorkspace)) {
            $files = glob($this->testWorkspace . '/*') ?: [];
            foreach ($files as $f) {
                if (is_file($f)) {
                    @unlink($f);
                }
            }
            @rmdir($this->testWorkspace);
        }
    }

    private function registerTestTools(): void
    {
        // 1. Read-only operation
        $this->server->registerTool('test_read', 'Test read-only tool', [
            'type' => 'object',
            'properties' => ['key' => ['type' => 'string']],
        ], function (array $args) {
            return ['status' => 'READ_SUCCESS', 'value' => $args['key'] ?? 'default_val'];
        });

        // 2. Authorized write
        $this->server->registerTool('test_write', 'Test authorized write tool', [
            'type' => 'object',
            'properties' => [
                'filename' => ['type' => 'string'],
                'content' => ['type' => 'string'],
                'auth_token' => ['type' => 'string'],
            ],
            'required' => ['filename', 'content'],
        ], function (array $args) {
            $path = $this->testWorkspace . DIRECTORY_SEPARATOR . basename((string)$args['filename']);
            file_put_contents($path, (string)$args['content']);
            return ['status' => 'WRITE_SUCCESS', 'path' => $path, 'bytes' => strlen((string)$args['content'])];
        });

        // 3. Rejected unauthorized write
        $this->server->registerTool('test_unauthorized_write', 'Test rejected write tool', [
            'type' => 'object',
            'properties' => ['filename' => ['type' => 'string']],
        ], function () {
            throw new \RuntimeException('Gate 4 Clearance Denied: Unauthorized write operation rejected.');
        });

        // 4. Invalid arguments
        $this->server->registerTool('test_invalid_args', 'Validates required arguments', [
            'type' => 'object',
            'properties' => ['expected_number' => ['type' => 'integer']],
            'required' => ['expected_number'],
        ], function (array $args) {
            if (!isset($args['expected_number']) || !is_int($args['expected_number'])) {
                throw new \InvalidArgumentException("Argument 'expected_number' is required and must be an integer.");
            }
            return ['number' => $args['expected_number']];
        });

        // 5. Execution error
        $this->server->registerTool('test_error', 'Intentionally throws error', [
            'type' => 'object',
        ], function () {
            throw new \RuntimeException('Simulated internal MCP tool failure');
        });

        // 6. Timeout simulation
        $this->server->registerTool('test_timeout', 'Sleeps to simulate timeout', [
            'type' => 'object',
            'properties' => ['sleep_seconds' => ['type' => 'integer']],
        ], function (array $args) {
            $seconds = (int)($args['sleep_seconds'] ?? 2);
            sleep($seconds);
            return ['slept' => $seconds];
        });

        // 7. Evidence generation
        $this->server->registerTool('test_evidence', 'Generates audit evidence record', [
            'type' => 'object',
            'properties' => ['action' => ['type' => 'string']],
        ], function (array $args) {
            return [
                'action' => $args['action'] ?? 'AUDIT_ACTION',
                'timestamp' => date('c'),
                'sha256' => hash('sha256', (string)($args['action'] ?? '')),
            ];
        });
    }
}

// Standalone CLI invocation
if (php_sapi_name() === 'cli' && isset($argv[0]) && realpath($argv[0]) === __FILE__) {
    $server = new LocalTestMcpServer();
    $server->listenStdio();
}
