<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

/**
 * Value object representing the normalized execution outcome of an agent task.
 */
readonly class ExecutionResult
{
    /**
     * @param string $taskId Unique authoritative task identifier
     * @param string $status Outcome status ('SUCCESS', 'FAILED', 'BLOCKED', 'CANCELLED')
     * @param int $exitCode Process/task exit code (0 for success)
     * @param string $output Primary text or markdown deliverable
     * @param array<int, string> $artifactsCreated List of file paths written or generated
     * @param array<string, mixed> $telemetry Execution timings, token usage, tool invocations
     * @param string|null $errorMessage Error message if failed
     */
    public function __construct(
        public string $taskId,
        public string $status,
        public int $exitCode,
        public string $output,
        public array $artifactsCreated = [],
        public array $telemetry = [],
        public ?string $errorMessage = null
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->status === 'SUCCESS' && $this->exitCode === 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'status' => $this->status,
            'exit_code' => $this->exitCode,
            'output' => $this->output,
            'artifacts_created' => $this->artifactsCreated,
            'telemetry' => $this->telemetry,
            'error_message' => $this->errorMessage,
        ];
    }
}
