<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

use UniversalAgentOS\Runtime\ExecutionResult;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Security\Gate4Response;

/**
 * Universal Integration Contract connecting AI coding environments to the UAOS runtime.
 */
interface AdapterInterface
{
    /**
     * Unique adapter identifier (e.g. 'antigravity', 'cursor', 'windsurf', 'vscode', 'cli').
     */
    public function getAdapterId(): string;

    /**
     * Human-readable name and version.
     */
    public function getName(): string;

    /**
     * Returns declared capabilities and security enforcement categories.
     */
    public function getCapabilities(): HostCapabilityMap;

    /**
     * Exports agent specifications into host-consumable formats (rules, modes, manifests).
     *
     * @return int Number of exported artifacts
     */
    public function exportAgents(string $outputDirectory): int;

    /**
     * Prepares and delivers execution context and compiled prompt.
     */
    public function prepareContext(TaskEnvelope $task): string;

    /**
     * Mediates tool execution or delegates to host.
     *
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    public function executeTool(string $toolName, array $arguments): array;

    /**
     * Handles human authorization request through host UI or out-of-band script.
     */
    public function requestGate4Approval(Gate4Request $request): Gate4Response;

    /**
     * Formats and captures execution evidence back into the runtime.
     */
    public function recordResult(ExecutionResult $result): bool;

    /**
     * Handles task cancellation.
     */
    public function handleCancellation(string $taskId, string $reason): void;

    /**
     * Handles task timeout.
     */
    public function handleTimeout(string $taskId, int $timeoutSeconds): void;
}
