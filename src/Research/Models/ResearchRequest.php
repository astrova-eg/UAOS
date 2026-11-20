<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;
use UniversalAgentOS\Runtime\TaskEnvelope;

/**
 * Value object encapsulating all inputs, constraints, and project context for a research inquiry.
 */
readonly class ResearchRequest implements JsonSerializable
{
    /**
     * @param string $taskId Unique authoritative task identifier (e.g. TASK-20261115-01)
     * @param string $taskPrompt Natural language task description / objective
     * @param DepthProfile $depth Assigned research depth profile (L0 to L4)
     * @param int $stage Current workflow stage (1 to 14, default 1)
     * @param array<int, string> $targetDomains Target taxonomy domain letters (e.g. ['A', 'B'])
     * @param array<int, string> $targetTechnologies Target technology keywords (e.g. ['php', 'laravel'])
     * @param array<string, mixed> $activeProfile Active target project profile discovered by ProjectDiscoveryEngine
     * @param array<string, mixed> $metadata Arbitrary contextual execution metadata
     * @param string $requestedAt ISO 8601 UTC timestamp of request generation
     */
    public function __construct(
        public string $taskId,
        public string $taskPrompt,
        public DepthProfile $depth,
        public int $stage = 1,
        public array $targetDomains = [],
        public array $targetTechnologies = [],
        public array $activeProfile = [],
        public array $metadata = [],
        public string $requestedAt = ''
    ) {
        $trimmedTaskId = trim($this->taskId);
        if ($trimmedTaskId === '') {
            throw new InvalidArgumentException('taskId cannot be empty.');
        }

        $trimmedPrompt = trim($this->taskPrompt);
        if ($trimmedPrompt === '') {
            throw new InvalidArgumentException('taskPrompt cannot be empty.');
        }

        if ($this->stage < 1 || $this->stage > 14) {
            throw new InvalidArgumentException("Workflow stage must be between 1 and 14, got: {$this->stage}");
        }

        if ($this->requestedAt !== '') {
            TimestampValidator::assertValid($this->requestedAt, 'requestedAt');
        }
    }

    /**
     * Creates a ResearchRequest from a core runtime TaskEnvelope.
     *
     * @param TaskEnvelope $envelope Runtime task envelope
     * @param DepthProfile|string $depth Calibrated depth profile or level identifier
     * @param array<string, mixed> $activeProfile Discovered project profile
     * @param array<int, string> $targetDomains Target domains
     * @param array<int, string> $targetTechnologies Target technologies
     */
    public static function fromTaskEnvelope(
        TaskEnvelope $envelope,
        DepthProfile|string $depth = DepthProfile::LEVEL_L1,
        array $activeProfile = [],
        array $targetDomains = [],
        array $targetTechnologies = []
    ): self {
        $profile = is_string($depth) ? DepthProfile::forLevel($depth) : $depth;

        return new self(
            taskId: $envelope->taskId,
            taskPrompt: $envelope->taskPrompt,
            depth: $profile,
            stage: $envelope->stage,
            targetDomains: $targetDomains,
            targetTechnologies: $targetTechnologies,
            activeProfile: $activeProfile,
            metadata: $envelope->metadata,
            requestedAt: gmdate('Y-m-d\TH:i:s\Z')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'task_id' => $this->taskId,
            'task_prompt' => $this->taskPrompt,
            'depth' => $this->depth->toArray(),
            'stage' => $this->stage,
            'target_domains' => $this->targetDomains,
            'target_technologies' => $this->targetTechnologies,
            'active_profile' => $this->activeProfile,
            'metadata' => $this->metadata,
            'requested_at' => $this->requestedAt !== '' ? $this->requestedAt : gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        if (!isset($data['task_id']) || !is_string($data['task_id'])) {
            throw new InvalidArgumentException("Missing or invalid 'task_id' in ResearchRequest data.");
        }

        if (!isset($data['task_prompt']) || !is_string($data['task_prompt'])) {
            throw new InvalidArgumentException("Missing or invalid 'task_prompt' in ResearchRequest data.");
        }

        $depthData = $data['depth'] ?? DepthProfile::LEVEL_L1;
        $depth = is_array($depthData)
            ? DepthProfile::fromArray($depthData)
            : DepthProfile::forLevel((string)$depthData);

        return new self(
            taskId: (string)$data['task_id'],
            taskPrompt: (string)$data['task_prompt'],
            depth: $depth,
            stage: (int)($data['stage'] ?? 1),
            targetDomains: (array)($data['target_domains'] ?? []),
            targetTechnologies: (array)($data['target_technologies'] ?? []),
            activeProfile: (array)($data['active_profile'] ?? []),
            metadata: (array)($data['metadata'] ?? []),
            requestedAt: (string)($data['requested_at'] ?? gmdate('Y-m-d\TH:i:s\Z'))
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
