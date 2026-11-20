<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class EvidenceCollector
{
    private string $reportsDir;
    /** @var array<string, mixed> */
    private array $sessionData = [];

    public function __construct(?string $reportsDir = null)
    {
        $this->reportsDir = $reportsDir ?? dirname(__DIR__, 2) . '/reports';
        if (!is_dir($this->reportsDir)) {
            mkdir($this->reportsDir, 0777, true);
        }
    }

    /**
     * @param string $executionId
     * @param string $taskId
     * @param string $agentId
     * @param int $stage
     * @param array<string, mixed> $initialData
     */
    public function startExecution(
        string $executionId,
        string $taskId,
        string $agentId,
        int $stage,
        array $initialData = []
    ): void {
        $this->sessionData = [
            'execution_id' => $executionId,
            'task_id' => $taskId,
            'agent_id' => $agentId,
            'stage' => $stage,
            'status' => 'IN_PROGRESS',
            'started_at' => date('c'),
            'completed_at' => null,
            'skills_selected' => [],
            'skills_loaded' => [],
            'delegations' => [],
            'gate_checks' => [],
            'operations' => [],
            'reviewer' => null,
            'review_verdict' => null,
            'issues' => [],
            'extra' => $initialData
        ];
    }

    /**
     * @param list<string> $selected
     * @param list<string> $loaded
     */
    public function recordSkills(array $selected, array $loaded): void
    {
        $this->sessionData['skills_selected'] = $selected;
        $this->sessionData['skills_loaded'] = $loaded;
    }

    /**
     * @param string $gate
     * @param bool $passed
     * @param string $details
     */
    public function recordGateCheck(string $gate, bool $passed, string $details): void
    {
        $this->sessionData['gate_checks'][] = [
            'gate' => $gate,
            'passed' => $passed,
            'details' => $details,
            'timestamp' => date('c')
        ];
    }

    /**
     * Status values: PLANNED, ATTEMPTED, EXECUTED, PASSED, FAILED, BLOCKED, NOT_VERIFIED
     * @param string $operation
     * @param string $status
     * @param mixed $result
     * @param string|null $error
     */
    public function recordOperation(string $operation, string $status, mixed $result = null, ?string $error = null): void
    {
        $this->sessionData['operations'][] = [
            'operation' => $operation,
            'status' => $status,
            'result' => $result,
            'error' => $error,
            'timestamp' => date('c')
        ];
    }

    public function recordDelegation(string $targetAgentId, string $taskDescription, array $result): void
    {
        $this->sessionData['delegations'][] = [
            'target_agent_id' => $targetAgentId,
            'task_description' => $taskDescription,
            'result' => $result,
            'timestamp' => date('c')
        ];
    }

    public function recordReview(string $reviewerId, string $verdict, string $comments): void
    {
        $this->sessionData['reviewer'] = $reviewerId;
        $this->sessionData['review_verdict'] = $verdict;
        $this->sessionData['review_comments'] = $comments;
    }

    public function completeExecution(string $finalStatus): array
    {
        $this->sessionData['status'] = $finalStatus;
        $this->sessionData['completed_at'] = date('c');

        $execId = (string)($this->sessionData['execution_id'] ?? 'exec-' . time());
        $jsonPath = $this->reportsDir . "/execution-{$execId}.json";
        $mdPath = $this->reportsDir . "/execution-{$execId}.md";

        file_put_contents($jsonPath, json_encode($this->sessionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $md = "# Universal Core Execution Report: {$execId}\n\n";
        $md .= "**Task ID:** `{$this->sessionData['task_id']}`  \n";
        $md .= "**Agent ID:** `{$this->sessionData['agent_id']}`  \n";
        $md .= "**Lifecycle Stage:** {$this->sessionData['stage']}  \n";
        $md .= "**Final Status:** **{$this->sessionData['status']}**  \n";
        $md .= "**Started:** {$this->sessionData['started_at']} | **Completed:** {$this->sessionData['completed_at']}  \n\n";

        $md .= "## Selected & Loaded Skills\n";
        $md .= "- **Discovered:** " . implode(', ', (array)$this->sessionData['skills_selected']) . "\n";
        $md .= "- **Loaded:** " . implode(', ', (array)$this->sessionData['skills_loaded']) . "\n\n";

        $md .= "## Operations & Verification Trail\n";
        $md .= "| Operation | Status | Outcome / Details |\n|---|---|---|\n";
        foreach ((array)$this->sessionData['operations'] as $op) {
            $err = $op['error'] ? "Error: " . $op['error'] : 'OK';
            $md .= "| {$op['operation']} | {$op['status']} | {$err} |\n";
        }

        if (!empty($this->sessionData['delegations'])) {
            $md .= "\n## Delegated Specialist Operations\n";
            $md .= "| Delegated Agent | Task Scope | Status |\n|---|---|---|\n";
            foreach ((array)$this->sessionData['delegations'] as $del) {
                $delStatus = $del['result']['status'] ?? 'COMPLETED';
                $md .= "| `{$del['target_agent_id']}` | {$del['task_description']} | {$delStatus} |\n";
            }
        }

        if (!empty($this->sessionData['reviewer'])) {
            $md .= "\n## Independent Review\n";
            $md .= "- **Reviewer:** `{$this->sessionData['reviewer']}`\n";
            $md .= "- **Verdict:** **{$this->sessionData['review_verdict']}**\n";
            $md .= "- **Comments:** {$this->sessionData['review_comments']}\n";
        }

        file_put_contents($mdPath, $md);

        return [
            'json_path' => $jsonPath,
            'md_path' => $mdPath,
            'data' => $this->sessionData
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getSessionData(): array
    {
        return $this->sessionData;
    }
}
