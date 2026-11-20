<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class Orchestrator
{
    private string $agentsDir;
    private array $agentRegistry;
    private SkillEngine $skillEngine;
    private ToolGateway $toolGateway;
    private PromptBuilder $promptBuilder;
    private EvidenceCollector $evidenceCollector;
    private string $stateDir;

    public function __construct(
        ?string $agentsDir = null,
        ?SkillEngine $skillEngine = null,
        ?ToolGateway $toolGateway = null,
        ?PromptBuilder $promptBuilder = null,
        ?EvidenceCollector $evidenceCollector = null
    ) {
        $this->agentsDir = $agentsDir ?? dirname(__DIR__, 2);
        $this->skillEngine = $skillEngine ?? new SkillEngine(
            $this->agentsDir . '/registry/skill-registry.json',
            $this->agentsDir . '/skills'
        );
        $this->toolGateway = $toolGateway ?? new ToolGateway(dirname($this->agentsDir));
        $this->promptBuilder = $promptBuilder ?? new PromptBuilder($this->agentsDir);
        $this->evidenceCollector = $evidenceCollector ?? new EvidenceCollector($this->agentsDir . '/reports');
        $this->stateDir = $this->agentsDir . '/runtime/state';
        if (!is_dir($this->stateDir)) {
            mkdir($this->stateDir, 0777, true);
        }
        $this->loadAgentRegistry();
    }

    private function loadAgentRegistry(): void
    {
        $regPath = $this->agentsDir . '/registry/agent-registry.json';
        if (!file_exists($regPath)) {
            throw new \RuntimeException("Agent registry not found: {$regPath}");
        }
        $decoded = json_decode((string)file_get_contents($regPath), true);
        if (!is_array($decoded) || !isset($decoded['agents'])) {
            throw new \RuntimeException("Malformed agent registry: {$regPath}");
        }
        $this->agentRegistry = [];
        foreach ($decoded['agents'] as $agent) {
            $this->agentRegistry[(string)$agent['id']] = $agent;
        }
    }

    /**
     * @param string $taskDescription
     * @return array{category: string, stage: int, keywords: list<string>, requires_review: bool}
     */
    public function classifyTask(string $taskDescription): array
    {
        $desc = strtolower($taskDescription);
        $category = 'general_engineering';
        $stage = 10;
        $keywords = [];
        $requiresReview = true;

        if (str_contains($desc, 'discovery') || str_contains($desc, 'research') || str_contains($desc, 'feasibility')) {
            $category = 'discovery';
            $stage = 1;
            $keywords = ['discovery', 'research'];
        } elseif (str_contains($desc, 'architecture') || str_contains($desc, 'adr') || str_contains($desc, 'design')) {
            $category = 'architecture';
            $stage = 2;
            $keywords = ['architecture', 'design', 'software-architecture'];
        } elseif (str_contains($desc, 'specification') || str_contains($desc, 'srs') || str_contains($desc, 'requirements')) {
            $category = 'specifications';
            $stage = 3;
            $keywords = ['requirements', 'specifications'];
        } elseif (str_contains($desc, 'atomic task') || str_contains($desc, 'dag') || str_contains($desc, 'breakdown')) {
            $category = 'task_engineering';
            $stage = 5;
            $keywords = ['task', 'orchestration'];
        } elseif (str_contains($desc, 'implementation plan') || str_contains($desc, 'planning')) {
            $category = 'planning';
            $stage = 7;
            $keywords = ['planning', 'rollback'];
        } elseif (str_contains($desc, 'security') || str_contains($desc, 'crypto') || str_contains($desc, 'auth')) {
            $category = 'security';
            $keywords = ['security', 'cryptography', 'secure-coding'];
        } elseif (str_contains($desc, 'database') || str_contains($desc, 'migration') || str_contains($desc, 'schema')) {
            $category = 'database';
            $keywords = ['database', 'migration'];
        } elseif (str_contains($desc, 'accessibility') || str_contains($desc, 'wcag') || str_contains($desc, 'ui') || str_contains($desc, 'ux')) {
            $category = 'ui_ux';
            $keywords = ['accessibility', 'ui-ux', 'wcag'];
        } elseif (str_contains($desc, 'audit') || str_contains($desc, 'verify') || str_contains($desc, 'test')) {
            $category = 'audit_testing';
            $stage = 12;
            $keywords = ['testing', 'audit', 'quality'];
        }

        return [
            'category' => $category,
            'stage' => $stage,
            'keywords' => $keywords,
            'requires_review' => $requiresReview
        ];
    }

    /**
     * @param string $category
     * @param string|null $preferredAgentId
     * @return array<string, mixed>
     */
    public function resolveAgent(string $category, ?string $preferredAgentId = null): array
    {
        if ($preferredAgentId !== null && isset($this->agentRegistry[$preferredAgentId])) {
            return $this->agentRegistry[$preferredAgentId];
        }

        $fallbackMap = [
            'discovery' => 'DISC-001',
            'architecture' => 'ARCH-001',
            'specifications' => 'REQ-001',
            'task_engineering' => 'ORCH-001',
            'planning' => 'LARCH-001',
            'security' => 'SEC-001',
            'database' => 'DBA-001',
            'ui_ux' => 'A11Y-001',
            'audit_testing' => 'AUD-001',
            'general_engineering' => 'CORE-001'
        ];

        $agentId = $fallbackMap[$category] ?? 'CHENG-001';
        if (!isset($this->agentRegistry[$agentId])) {
            // Find any agent in category
            $agentId = array_key_first($this->agentRegistry);
        }

        return $this->agentRegistry[$agentId];
    }

    /**
     * Assigns an independent reviewer upholding Separation of Duties.
     */
    public function assignReviewer(string $primaryAgentId, int $stage): string
    {
        if ($stage === 4) return 'RVAL-001';
        if ($stage === 6) return 'STD-001'; // Gate 2: STD-001 reviews ORCH-001
        if ($stage === 8) return 'ARCH-001'; // Gate 3: ARCH-001 reviews LARCH-001
        if ($stage >= 10 && $stage <= 12) return 'AUD-008'; // Gate 5: Dept M Auditor
        if ($stage === 13) return 'ACPT-001'; // Acceptance DoD
        return 'QGOV-001';
    }

    /**
     * @param array{
     *     task_id: string,
     *     task_description: string,
     *     preferred_agent_id?: string,
     *     gate4_token_path?: string,
     *     secret_key?: string,
     *     auto_execute?: bool,
     *     operation?: array<string, mixed>
     * } $input
     * @return array<string, mixed>
     */
    public function runTask(array $input): array
    {
        $taskId = (string)$input['task_id'];
        $desc = (string)$input['task_description'];
        $classification = $this->classifyTask($desc);
        $agent = $this->resolveAgent($classification['category'], $input['preferred_agent_id'] ?? null);
        $reviewerId = $this->assignReviewer((string)$agent['id'], $classification['stage']);

        $execId = 'exec-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
        $this->evidenceCollector->startExecution($execId, $taskId, (string)$agent['id'], $classification['stage'], [
            'classification' => $classification,
            'assigned_reviewer' => $reviewerId
        ]);

        // 1. Skill Discovery & Loading
        $discoveredSkillIds = $this->skillEngine->discoverSkillIds(
            (string)$agent['id'],
            (string)($agent['role'] ?? null),
            $classification['keywords']
        );
        $loadedSkills = $this->skillEngine->loadSkills($discoveredSkillIds);
        $this->evidenceCollector->recordSkills($discoveredSkillIds, array_keys($loadedSkills));

        // 2. Build Runtime Prompt
        $promptResult = $this->promptBuilder->build([
            'agent' => $agent,
            'task_title' => $taskId,
            'task_scope' => $desc,
            'stage' => $classification['stage'],
            'skills' => $loadedSkills
        ]);

        // 3. Evaluate & Execute Tool if operation requested
        $toolResult = null;
        if (!empty($input['operation'])) {
            $op = $input['operation'];
            $op['agent_id'] = $agent['id'];
            $op['department'] = $agent['department_folder'];
            $op['stage'] = $classification['stage'];
            if (isset($input['gate4_token_path'])) {
                $op['gate4_token_path'] = $input['gate4_token_path'];
            }
            if (isset($input['secret_key'])) {
                $op['secret_key'] = $input['secret_key'];
            }

            $toolResult = $this->toolGateway->evaluateAndExecute($op);
            $status = $toolResult['executed'] ? 'EXECUTED' : ($toolResult['permitted'] ? 'FAILED' : 'BLOCKED');
            $this->evidenceCollector->recordOperation(
                (string)$op['operation'],
                $status,
                $toolResult['output'],
                $toolResult['error']
            );
        }

        // 4. Process Delegated Specialists
        $delegationsSummary = [];
        $anyDelBlocked = false;
        if (!empty($input['delegations']) && is_array($input['delegations'])) {
            foreach ($input['delegations'] as $del) {
                $delAgentId = (string)($del['agent_id'] ?? '');
                $delTask = (string)($del['task'] ?? 'Delegated task');
                if (!isset($this->agentRegistry[$delAgentId])) {
                    continue;
                }
                $delAgent = $this->agentRegistry[$delAgentId];
                $delSkills = $this->skillEngine->discoverSkillIds($delAgentId, $delAgent['role'] ?? null);
                $delLoaded = $this->skillEngine->loadSkills($delSkills);
                $delToolRes = null;
                if (!empty($del['operation'])) {
                    $delOp = $del['operation'];
                    $delOp['agent_id'] = $delAgentId;
                    $delOp['department'] = $delAgent['department_folder'];
                    $delOp['stage'] = $classification['stage'];
                    $delToolRes = $this->toolGateway->evaluateAndExecute($delOp);
                }
                $delStatus = ($delToolRes === null || $delToolRes['executed']) ? 'COMPLETED' : 'BLOCKED';
                if ($delStatus === 'BLOCKED') {
                    $anyDelBlocked = true;
                }
                $delRecord = [
                    'agent_id' => $delAgentId,
                    'agent_name' => $delAgent['name'],
                    'skills_loaded' => array_keys($delLoaded),
                    'status' => $delStatus,
                    'tool_result' => $delToolRes
                ];
                $delegationsSummary[$delAgentId] = $delRecord;
                $this->evidenceCollector->recordDelegation($delAgentId, $delTask, $delRecord);
            }
        }

        // 5. Record Independent Review
        $reviewVerdict = (($toolResult === null || $toolResult['executed']) && !$anyDelBlocked) ? 'APPROVED' : 'REJECTED';
        $reviewComment = ($toolResult && !$toolResult['executed']) || $anyDelBlocked
            ? "Blocked at gateway or delegated operation rejected."
            : "Deliverable satisfies architectural constraints and separation of duties.";
        $this->evidenceCollector->recordReview($reviewerId, $reviewVerdict, $reviewComment);

        $finalStatus = ($reviewVerdict === 'APPROVED') ? 'COMPLETED' : 'BLOCKED';
        $report = $this->evidenceCollector->completeExecution($finalStatus);

        return [
            'execution_id' => $execId,
            'status' => $finalStatus,
            'agent' => $agent,
            'reviewer' => $reviewerId,
            'skills_loaded' => array_keys($loadedSkills),
            'delegations' => $delegationsSummary,
            'prompt_length' => $promptResult['character_count'],
            'tool_result' => $toolResult,
            'report' => $report
        ];
    }
}
