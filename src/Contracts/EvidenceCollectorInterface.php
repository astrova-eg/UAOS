<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Structured execution log and markdown audit trail generator.
 */
interface EvidenceCollectorInterface
{
    /**
     * Initializes a new execution session for a task.
     */
    public function startSession(
        string $taskId,
        string $agentId,
        string $departmentId,
        string $adapterId
    ): string;

    /**
     * Records a single operational event within the active session.
     *
     * @param string $operation Name of operation (e.g., 'write_file', 'shell_exec')
     * @param string $targetResource Canonical target path or command
     * @param string $permissionDecision 'PERMITTED', 'REJECTED', or 'FLAGGED'
     * @param array<string, mixed> $details Operation details, exit code, hashes
     */
    public function recordEvent(
        string $operation,
        string $targetResource,
        string $permissionDecision,
        array $details = []
    ): void;

    /**
     * Completes the execution session, writes structured JSON and markdown reports.
     *
     * @param string $status Final status ('SUCCESS', 'FAILED', 'BLOCKED')
     * @param string $summary Summary narrative
     * @return array<string, string> Paths to saved report files ['json' => ..., 'md' => ...]
     */
    public function endSession(string $status, string $summary): array;

    /**
     * Retrieves active session ID.
     */
    public function getActiveSessionId(): ?string;
}
