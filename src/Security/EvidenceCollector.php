<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;

/**
 * Audit and evidence collection engine generating structured JSON and Markdown execution reports.
 *
 * Implements strict telemetry hygiene with automatic secret redaction and RDEI Gate 0 tracking.
 */
class EvidenceCollector implements EvidenceCollectorInterface
{
    private PathResolverInterface $pathResolver;
    private ?string $activeSessionId = null;
    private ?string $activeTaskId = null;
    private ?string $activeAgentId = null;
    private ?string $activeDeptId = null;
    private ?string $activeAdapterId = null;
    private int $sessionStartTimestamp = 0;
    /** @var array<int, array<string, mixed>> */
    private array $events = [];

    private const SENSITIVE_KEY_PATTERNS = [
        'password', 'secret', 'token', 'bearer',
        'api_key', 'access_token', 'client_secret', 'private_key',
        'secret_key', 'auth_key', 'access_key', 'encryption_key',
    ];

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function startSession(
        string $taskId,
        string $agentId,
        string $departmentId,
        string $adapterId
    ): string {
        $this->activeTaskId = $taskId;
        $this->activeAgentId = $agentId;
        $this->activeDeptId = $departmentId;
        $this->activeAdapterId = $adapterId;
        $this->sessionStartTimestamp = time();
        $this->activeSessionId = 'sess_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        $this->events = [];

        return $this->activeSessionId;
    }

    public function recordEvent(
        string $operation,
        string $targetResource,
        string $permissionDecision,
        array $details = []
    ): void {
        $cleanTarget = $this->redactSensitiveString($targetResource);
        $cleanDetails = $this->redactSensitiveData($details);

        $this->events[] = [
            'timestamp' => date('c'),
            'operation' => $operation,
            'target_resource' => $cleanTarget,
            'permission_decision' => $permissionDecision,
            'details' => $cleanDetails,
        ];
    }

    /**
     * Records a specialized RDEI research event.
     *
     * @param array<string, mixed> $details
     */
    public function recordResearchEvent(
        string $operation,
        string $targetResource,
        string $permissionDecision,
        array $details = []
    ): void {
        $this->recordEvent($operation, $targetResource, $permissionDecision, $details);
    }

    /**
     * Authoritatively records a Gate 0 clearance decision event.
     */
    public function recordGate0Decision(Gate0ClearanceResult $clearance): void
    {
        $decision = $clearance->isCleared() ? 'PERMITTED' : 'REJECTED';
        $details = [
            'status' => $clearance->status,
            'task_id' => $clearance->taskId,
            'dossier_id' => $clearance->dossierId,
            'dossier_hash' => $clearance->dossierHash,
            'reason' => $clearance->reason,
            'violations' => $clearance->violations,
            'operational_condition' => $clearance->operationalCondition,
            'audit_details' => $clearance->auditDetails,
        ];

        $this->recordEvent('GATE_0_EVALUATED', "rdei://tasks/{$clearance->taskId}/gate0", $decision, $details);
    }

    public function endSession(string $status, string $summary): array
    {
        $duration = time() - $this->sessionStartTimestamp;
        $taskId = $this->activeTaskId ?? 'TASK-' . date('Ymd-His');
        $dateStr = date('Ymd-His');

        $evidenceDir = $this->pathResolver->getProjectReportsDir() . '/../evidence';
        $reportsDir = $this->pathResolver->getProjectReportsDir();

        if (!is_dir($evidenceDir)) {
            mkdir($evidenceDir, 0755, true);
        }
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }

        $sessionData = [
            'session_id' => $this->activeSessionId,
            'project_id' => basename($this->pathResolver->getProjectRootDir()),
            'task_id' => $taskId,
            'agent_id' => $this->activeAgentId,
            'department_id' => $this->activeDeptId,
            'adapter_id' => $this->activeAdapterId,
            'status' => $status,
            'summary' => $this->redactSensitiveString($summary),
            'duration_seconds' => $duration,
            'events_count' => count($this->events),
            'events' => $this->events,
        ];

        $jsonFile = $evidenceDir . DIRECTORY_SEPARATOR . "exec-{$dateStr}-{$taskId}.json";
        $mdFile = $reportsDir . DIRECTORY_SEPARATOR . "EXEC-{$dateStr}-{$taskId}.md";

        file_put_contents($jsonFile, (string)json_encode($sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $mdContent = $this->generateMarkdownReport($sessionData);
        file_put_contents($mdFile, $mdContent);

        $this->activeSessionId = null;
        $this->events = [];

        return [
            'json' => $jsonFile,
            'md' => $mdFile,
        ];
    }

    public function getActiveSessionId(): ?string
    {
        return $this->activeSessionId;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getEvents(): array
    {
        return $this->events;
    }

    private function generateMarkdownReport(array $session): string
    {
        $md = "# Agent Execution Report: {$session['task_id']}\n\n";
        $md .= "- **Session ID:** `{$session['session_id']}`\n";
        $md .= "- **Project:** `{$session['project_id']}`\n";
        $md .= "- **Agent:** `{$session['agent_id']}` (`{$session['department_id']}`)\n";
        $md .= "- **Adapter:** `{$session['adapter_id']}`\n";
        $md .= "- **Status:** **{$session['status']}**\n";
        $md .= "- **Duration:** {$session['duration_seconds']}s\n\n";
        $md .= "## Summary\n\n{$session['summary']}\n\n";

        // Gate 0 & Research Governance Highlights
        $researchEvents = array_filter(
            $session['events'],
            static fn (array $e) => str_starts_with((string)$e['operation'], 'GATE_0_') || str_starts_with((string)$e['operation'], 'RESEARCH_')
        );

        if (!empty($researchEvents)) {
            $md .= "## Research & Gate 0 Governance\n\n";
            $md .= "| Operation | Decision | Target | Key Details |\n";
            $md .= "|---|---|---|---|\n";
            foreach ($researchEvents as $re) {
                $statusDetail = (string)($re['details']['status'] ?? $re['details']['reason'] ?? '');
                $md .= "| `{$re['operation']}` | `{$re['permission_decision']}` | `{$re['target_resource']}` | {$statusDetail} |\n";
            }
            $md .= "\n";
        }

        $md .= "## Execution Events (" . count($session['events']) . ")\n\n";
        $md .= "| Time | Operation | Decision | Target |\n";
        $md .= "|---|---|---|---|\n";

        foreach ($session['events'] as $evt) {
            $target = basename((string)$evt['target_resource']);
            $md .= "| {$evt['timestamp']} | `{$evt['operation']}` | `{$evt['permission_decision']}` | `{$target}` |\n";
        }

        return $md;
    }

    /**
     * Recursively redacts sensitive keys and values from telemetry data.
     */
    private function redactSensitiveData(mixed $data): mixed
    {
        if (is_array($data)) {
            $cleaned = [];
            foreach ($data as $key => $val) {
                if (is_string($key) && $this->isSensitiveKey($key)) {
                    $cleaned[$key] = '[REDACTED]';
                } else {
                    $cleaned[$key] = $this->redactSensitiveData($val);
                }
            }
            return $cleaned;
        }

        if (is_string($data)) {
            return $this->redactSensitiveString($data);
        }

        return $data;
    }

    private function isSensitiveKey(string $key): bool
    {
        $lower = strtolower($key);
        if ($lower === 'key' || $lower === 'auth') {
            return true;
        }
        foreach (self::SENSITIVE_KEY_PATTERNS as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    private function redactSensitiveString(string $input): string
    {
        // Redact Authorization headers or Bearer tokens
        $pattern = '/(bearer\s+)[A-Za-z0-9_\-\.]{8,}/i';
        $redacted = (string)preg_replace($pattern, '$1[REDACTED]', $input);

        // Redact credentials in URLs: https://user:pass@host
        $urlPattern = '/(https?:\/\/[^\/:]+:)[^@]+@/i';
        $redacted = (string)preg_replace($urlPattern, '$1[REDACTED]@', $redacted);

        return $redacted;
    }
}
