<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

use UniversalAgentOS\Contracts\McpServerInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\TaskEnvelope;

/**
 * Model Context Protocol (MCP) server exposing guarded UAOS runtime tools via JSON-RPC 2.0.
 */
class McpServer implements McpServerInterface
{
    private PathResolverInterface $pathResolver;
    private ?ToolGatewayInterface $toolGateway;
    private ?Orchestrator $orchestrator;
    /** @var array<string, array<string, mixed>> */
    private array $tools = [];

    public function __construct(
        PathResolverInterface $pathResolver,
        ?ToolGatewayInterface $toolGateway = null,
        ?Orchestrator $orchestrator = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->toolGateway = $toolGateway;
        $this->orchestrator = $orchestrator;
        $this->registerDefaultTools();
    }

    public function registerTool(
        string $name,
        string $description,
        array $inputSchema,
        callable $handler,
        bool $requiresGate4 = false
    ): void {
        $this->tools[$name] = [
            'name' => $name,
            'description' => $description,
            'inputSchema' => $inputSchema,
            'handler' => $handler,
            'requires_gate4' => $requiresGate4,
        ];
    }

    public function handleJsonRpc(string $rawJson): string
    {
        try {
            $msg = McpMessage::fromJson($rawJson);
        } catch (\Exception $e) {
            return McpMessage::error(null, -32700, 'Parse error: ' . $e->getMessage())->toJson();
        }

        if ($msg->method === 'initialize') {
            return McpMessage::response($msg->id ?? 1, [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => ['listChanged' => false]],
                'serverInfo' => ['name' => 'UAOS MCP Server', 'version' => '2.0.0'],
            ])->toJson();
        }

        if ($msg->method === 'notifications/initialized') {
            return '';
        }

        if ($msg->method === 'tools/list') {
            $toolSpecs = [];
            foreach ($this->tools as $tool) {
                $toolSpecs[] = [
                    'name' => $tool['name'],
                    'description' => $tool['description'],
                    'inputSchema' => $tool['inputSchema'],
                ];
            }
            return McpMessage::response($msg->id ?? 1, ['tools' => $toolSpecs])->toJson();
        }

        if ($msg->method === 'tools/call') {
            return $this->handleToolCall($msg);
        }

        return McpMessage::error($msg->id, -32601, "Method '{$msg->method}' not found.")->toJson();
    }

    public function listenStdio(): void
    {
        while ($line = fgets(STDIN)) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }
            $response = $this->handleJsonRpc($trimmed);
            if ($response !== '') {
                echo $response . "\n";
                flush();
            }
        }
    }

    private function handleToolCall(McpMessage $msg): string
    {
        $params = (array)($msg->params ?? []);
        $toolName = (string)($params['name'] ?? '');
        $args = (array)($params['arguments'] ?? []);

        if (!isset($this->tools[$toolName])) {
            return McpMessage::error($msg->id, -32602, "Tool '{$toolName}' is not registered.")->toJson();
        }

        $tool = $this->tools[$toolName];
        try {
            $handler = $tool['handler'];
            $result = $handler($args);
            return McpMessage::response($msg->id ?? 1, [
                'content' => [['type' => 'text', 'text' => is_string($result) ? $result : json_encode($result, JSON_PRETTY_PRINT)]],
                'isError' => false,
            ])->toJson();
        } catch (\Exception $e) {
            return McpMessage::response($msg->id ?? 1, [
                'content' => [['type' => 'text', 'text' => "Execution error: " . $e->getMessage()]],
                'isError' => true,
            ])->toJson();
        }
    }

    private function registerDefaultTools(): void
    {
        $this->registerTool('uaos_read_file', 'Read file confined to project workspace', [
            'type' => 'object',
            'properties' => ['path' => ['type' => 'string']],
            'required' => ['path'],
        ], function (array $args) {
            if ($this->toolGateway === null) {
                throw new \RuntimeException('ToolGateway unavailable');
            }
            return $this->toolGateway->readFile((string)($args['path'] ?? ''));
        });

        $this->registerTool('uaos_write_file', 'Write file with Gate 4 gating for Stage 10+ code', [
            'type' => 'object',
            'properties' => [
                'path' => ['type' => 'string'],
                'content' => ['type' => 'string'],
                'gate4_token' => ['type' => 'string'],
                'task_id' => ['type' => 'string'],
            ],
            'required' => ['path', 'content'],
        ], function (array $args) {
            if ($this->toolGateway === null) {
                throw new \RuntimeException('ToolGateway unavailable');
            }
            return $this->toolGateway->writeFile(
                (string)$args['path'],
                (string)$args['content'],
                isset($args['gate4_token']) ? (string)$args['gate4_token'] : null,
                isset($args['task_id']) ? (string)$args['task_id'] : null
            );
        }, true);

        $this->registerTool('uaos_plan_task', 'Plan task with agent selection, profile, and skills', [
            'type' => 'object',
            'properties' => [
                'task_prompt' => ['type' => 'string'],
                'agent_id' => ['type' => 'string'],
                'stage' => ['type' => 'integer'],
            ],
            'required' => ['task_prompt'],
        ], function (array $args) {
            if ($this->orchestrator === null) {
                throw new \RuntimeException('Orchestrator unavailable');
            }
            $envelope = new TaskEnvelope(
                taskId: 'MCP-TASK-' . bin2hex(random_bytes(4)),
                taskPrompt: (string)$args['task_prompt'],
                agentId: isset($args['agent_id']) ? (string)$args['agent_id'] : null,
                stage: isset($args['stage']) ? (int)$args['stage'] : 1
            );
            return $this->orchestrator->planTask($envelope);
        });
    }
}
