<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

/**
 * Value object encapsulating all inputs, constraints, and context for an agent task.
 */
readonly class TaskEnvelope
{
    /**
     * @param string $taskId Unique authoritative task identifier
     * @param string $taskPrompt Natural language task description / objective
     * @param string|null $agentId Selected agent identifier (e.g., ARCH-001)
     * @param int $stage Lifecycle workflow stage (1 to 14)
     * @param array<string, mixed> $inputs Input parameters, files, or payload
     * @param array<string, mixed> $metadata Arbitrary execution metadata
     * @param string|null $gate4Token Optional Gate 4 cryptographic authorization token
     */
    public function __construct(
        public string $taskId,
        public string $taskPrompt,
        public ?string $agentId = null,
        public int $stage = 1,
        public array $inputs = [],
        public array $metadata = [],
        public ?string $gate4Token = null
    ) {
    }

    /**
     * Creates a new envelope with an updated agent identifier.
     */
    public function withAgentId(string $agentId): self
    {
        return new self(
            $this->taskId,
            $this->taskPrompt,
            $agentId,
            $this->stage,
            $this->inputs,
            $this->metadata,
            $this->gate4Token
        );
    }
}
