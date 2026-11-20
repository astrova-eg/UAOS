<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\AdapterInterface;
use UniversalAgentOS\Contracts\HostCapabilityMap;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Exceptions\UnsupportedOperationException;
use UniversalAgentOS\Runtime\ExecutionResult;
use UniversalAgentOS\Runtime\PromptBuilder;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Security\Gate4Response;

/**
 * Abstract base adapter providing common execution context preparation and security checks.
 */
abstract class AbstractAdapter implements AdapterInterface
{
    protected PathResolverInterface $pathResolver;
    protected ?ToolGatewayInterface $toolGateway;
    protected PromptBuilder $promptBuilder;

    public function __construct(
        PathResolverInterface $pathResolver,
        ?ToolGatewayInterface $toolGateway = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->toolGateway = $toolGateway;
        $this->promptBuilder = new PromptBuilder($pathResolver);
    }

    abstract public function getAdapterId(): string;

    abstract public function getName(): string;

    abstract public function getCapabilities(): HostCapabilityMap;

    public function prepareContext(TaskEnvelope $task): string
    {
        $agentId = $task->agentId ?? 'A01';
        $agentSpec = "# Agent: {$agentId}\nActive execution persona.";
        $deptCharter = "# Department Charter\nStandard departmental boundary instructions.";

        return $this->promptBuilder->buildPrompt($agentSpec, $deptCharter, $task);
    }

    public function executeTool(string $toolName, array $arguments): array
    {
        if ($this->toolGateway === null) {
            throw new UnsupportedOperationException("ToolGateway is not configured on this adapter.");
        }

        return match ($toolName) {
            'read_file' => [
                'content' => $this->toolGateway->readFile((string)($arguments['path'] ?? '')),
            ],
            'write_file' => $this->toolGateway->writeFile(
                (string)($arguments['path'] ?? ''),
                (string)($arguments['content'] ?? ''),
                isset($arguments['gate4_token']) ? (string)$arguments['gate4_token'] : null,
                isset($arguments['task_id']) ? (string)$arguments['task_id'] : null
            ),
            'execute_shell' => $this->toolGateway->executeShell(
                (string)($arguments['command'] ?? ''),
                isset($arguments['working_directory']) ? (string)$arguments['working_directory'] : null,
                isset($arguments['gate4_token']) ? (string)$arguments['gate4_token'] : null
            ),
            default => throw new UnsupportedOperationException("Unsupported tool operation: {$toolName}"),
        };
    }

    public function requestGate4Approval(Gate4Request $request): Gate4Response
    {
        return Gate4Response::rejected("Interactive Gate 4 approval requires out-of-band authorization script.");
    }

    public function recordResult(ExecutionResult $result): bool
    {
        return true;
    }

    public function handleCancellation(string $taskId, string $reason): void
    {
    }

    public function handleTimeout(string $taskId, int $timeoutSeconds): void
    {
    }
}
