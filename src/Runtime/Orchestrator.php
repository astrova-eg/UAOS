<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\SkillEngineInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Exceptions\AgentOSException;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Engine\ResearchDepthSelector;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;

/**
 * Central task coordination and agent lifecycle dispatcher.
 *
 * Integrates RDEI Gate 0 verification and Section 2.6 research evidence injection.
 */
class Orchestrator
{
    private TaskClassifier $classifier;
    private PromptBuilder $promptBuilder;
    private SkillEngineInterface $skillEngine;
    private ReviewRouter $reviewRouter;
    private ConfigurationLoader $configLoader;
    private ResearchDepthSelector $depthSelector;

    public function __construct(
        private PathResolverInterface $pathResolver,
        private ?ToolGatewayInterface $toolGateway = null,
        private ?EvidenceCollectorInterface $evidenceCollector = null,
        ?SkillEngineInterface $skillEngine = null,
        private ?ResearchEngineInterface $researchEngine = null
    ) {
        $this->classifier = new TaskClassifier();
        $this->promptBuilder = new PromptBuilder($pathResolver);
        $this->skillEngine = $skillEngine ?? new SkillEngine($pathResolver);
        $this->reviewRouter = new ReviewRouter();
        $this->configLoader = new ConfigurationLoader($pathResolver);
        $this->depthSelector = new ResearchDepthSelector();
    }

    /**
     * Prepares and coordinates execution context for a given task.
     *
     * @return array<string, mixed> Assembled context and metadata
     */
    public function planTask(TaskEnvelope $task): array
    {
        $agentId = $task->agentId;
        $deptId = null;

        if ($agentId === null || trim($agentId) === '') {
            $classification = $this->classifier->classify($task->taskPrompt);
            $agentId = (string)$classification['recommended_agent'];
            $deptId = (string)$classification['department_id'];
        }

        $agentSpec = $this->loadAgentSpec($agentId);
        $deptCharter = $this->loadDeptCharter($deptId ?? $this->resolveDepartmentId($agentId));
        $profile = $this->loadActiveProfile();

        $dossier = null;
        $clearance = null;
        $gate0Blocked = false;

        // RDEI Research Lifecycle & Gate 0 Verification
        $depth = $this->depthSelector->selectDepth($task->taskPrompt, $task->stage, [], $profile);
        if ($depth->level === DepthProfile::LEVEL_L0) {
            $clearance = Gate0ClearanceResult::exempt($task->taskId, 'Task research depth L0 requires no external evidence.');
        } elseif ($this->researchEngine === null) {
            $clearance = Gate0ClearanceResult::blocked(
                taskId: $task->taskId,
                reason: "Gate 0 enforcement failure: Research engine is unavailable or unconfigured for task requiring depth {$depth->level}.",
                violations: ["Missing mandatory research engine for depth {$depth->level}."],
                operationalCondition: Gate0ClearanceResult::CONDITION_UNAVAILABLE_DEPENDENCY
            );
            $gate0Blocked = true;
        } else {
            try {
                $researchRequest = ResearchRequest::fromTaskEnvelope($task, $depth, $profile);
                $dossier = $this->researchEngine->conductResearch($researchRequest);
                $clearance = $this->researchEngine->verifyClearance($task->taskId);
                if (!$clearance->isCleared()) {
                    $gate0Blocked = true;
                }
            } catch (\Throwable $e) {
                $clearance = Gate0ClearanceResult::blocked(
                    taskId: $task->taskId,
                    reason: 'Gate 0 enforcement failure: ' . $e->getMessage(),
                    violations: ['Research execution error: ' . $e->getMessage()],
                    operationalCondition: Gate0ClearanceResult::CONDITION_UNAVAILABLE_DEPENDENCY
                );
                $gate0Blocked = true;
            }
        }

        $skillResult = $this->skillEngine->resolveSkills(
            $agentId,
            $task->taskPrompt,
            $profile,
            (int)$this->configLoader->get('orchestration.context_char_budget', 45000)
        );

        $compiledPrompt = $gate0Blocked
            ? null
            : $this->promptBuilder->buildPrompt(
                $agentSpec,
                $deptCharter,
                $task->withAgentId($agentId),
                $profile,
                $skillResult['skills'],
                $dossier,
                $clearance
            );

        $auditor = $this->reviewRouter->routeReview($agentId, $task->stage);

        return [
            'task_id' => $task->taskId,
            'agent_id' => $agentId,
            'department_id' => $deptId ?? $this->resolveDepartmentId($agentId),
            'stage' => $task->stage,
            'compiled_prompt' => $compiledPrompt,
            'skills_included' => array_keys($skillResult['skills']),
            'skills_audit' => $skillResult['audit'],
            'assigned_auditor' => $auditor,
            'gate_0_clearance' => $clearance,
            'gate_0_blocked' => $gate0Blocked,
            'research_dossier' => $dossier,
        ];
    }

    /**
     * Executes a planned task lifecycle.
     */
    public function execute(TaskEnvelope $task): ExecutionResult
    {
        $plan = $this->planTask($task);
        $sessionId = null;

        if ($this->evidenceCollector !== null) {
            try {
                $sessionId = $this->evidenceCollector->startSession(
                    $plan['task_id'],
                    $plan['agent_id'],
                    $plan['department_id'],
                    (string)$this->configLoader->get('active_adapter', 'cli')
                );
            } catch (\Throwable) {}
        }

        // Gate 0 Enforcement Boundary: Prevent progression when Gate 0 is blocked or uncleared
        $clearance = $plan['gate_0_clearance'] ?? null;
        $isCleared = ($clearance instanceof Gate0ClearanceResult) && $clearance->isCleared();
        if (!empty($plan['gate_0_blocked']) || !$isCleared) {
            $reason = ($clearance instanceof Gate0ClearanceResult) ? $clearance->reason : 'Gate 0 research clearance failed';
            $violations = ($clearance instanceof Gate0ClearanceResult) ? $clearance->violations : ['Gate 0 clearance missing or invalid'];

            if ($this->evidenceCollector !== null) {
                try {
                    $this->evidenceCollector->recordEvent(
                        'GATE_0_EVALUATED',
                        "rdei://tasks/{$task->taskId}/gate0",
                        'REJECTED',
                        ['reason' => $reason, 'violations' => $violations]
                    );
                    $this->evidenceCollector->endSession('BLOCKED', "Execution halted: Gate 0 blocked ({$reason})");
                } catch (\Throwable) {}
            }

            return new ExecutionResult(
                taskId: $task->taskId,
                status: 'BLOCKED',
                exitCode: 2,
                output: "Execution blocked: Task {$task->taskId} failed Gate 0 research clearance.\nReason: {$reason}\nViolations: " . implode('; ', $violations),
                telemetry: [
                    'agent_id' => $plan['agent_id'],
                    'session_id' => $sessionId,
                    'gate_0_status' => 'BLOCKED',
                    'gate_0_reason' => $reason,
                    'violations' => $violations,
                ]
            );
        }

        $output = "Task {$task->taskId} successfully planned and dispatched to agent {$plan['agent_id']}.\n"
            . "Assigned Department M Independent Auditor: {$plan['assigned_auditor']}.\n";

        if ($this->evidenceCollector !== null) {
            try {
                $this->evidenceCollector->endSession('SUCCESS', 'Task executed cleanly within boundaries.');
            } catch (\Throwable) {}
        }

        return new ExecutionResult(
            taskId: $task->taskId,
            status: 'SUCCESS',
            exitCode: 0,
            output: $output,
            telemetry: [
                'agent_id' => $plan['agent_id'],
                'session_id' => $sessionId,
                'gate_0_status' => $clearance?->status ?? 'NOT_EVALUATED',
            ]
        );
    }

    private function loadAgentSpec(string $agentId): string
    {
        $hostCustom = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'agents' . DIRECTORY_SEPARATOR . "{$agentId}.md";
        if (file_exists($hostCustom)) {
            return (string)file_get_contents($hostCustom);
        }

        $registryPath = $this->pathResolver->resolveResourcePath('registry/agent-registry.json');
        if (file_exists($registryPath)) {
            $raw = file_get_contents($registryPath);
            $data = is_string($raw) ? json_decode($raw, true) : [];
            foreach ((array)($data['agents'] ?? []) as $agent) {
                if (strcasecmp((string)($agent['id'] ?? ''), $agentId) === 0) {
                    $specRel = (string)($agent['specification_path'] ?? '');
                    if ($specRel !== '' && file_exists($full = $this->pathResolver->resolveResourcePath($specRel))) {
                        return (string)file_get_contents($full);
                    }
                }
            }
        }

        throw new AgentOSException("Agent specification not found for ID: {$agentId}");
    }

    private function loadDeptCharter(string $deptId): string
    {
        $deptDir = $this->pathResolver->getPackageResourcesDir() . DIRECTORY_SEPARATOR . 'departments';
        if (is_dir($deptDir)) {
            foreach (scandir($deptDir) ?: [] as $f) {
                if ($f !== '.' && $f !== '..' && stripos($f, $deptId) !== false && str_ends_with($f, '.md')) {
                    return (string)file_get_contents($deptDir . DIRECTORY_SEPARATOR . $f);
                }
            }
        }
        return "# Department Charter: {$deptId}\n\nStandard departmental boundary instructions.";
    }

    private function loadActiveProfile(): array
    {
        $profileFile = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'project-profile.json';
        if (file_exists($profileFile)) {
            $raw = file_get_contents($profileFile);
            $decoded = is_string($raw) ? json_decode($raw, true) : [];
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return [
            'project_name' => basename($this->pathResolver->getProjectRootDir()),
            'technologies' => ['backend_framework' => 'generic_php', 'database_engine' => 'mysql'],
        ];
    }

    private function resolveDepartmentId(string $agentId): string
    {
        $registryPath = $this->pathResolver->resolveResourcePath('registry/agent-registry.json');
        if (file_exists($registryPath)) {
            $raw = file_get_contents($registryPath);
            $data = is_string($raw) ? json_decode($raw, true) : [];
            foreach ((array)($data['agents'] ?? []) as $ag) {
                if (strcasecmp((string)($ag['id'] ?? ''), $agentId) === 0) {
                    return (string)($ag['department_folder'] ?? 'A_leadership_governance');
                }
            }
        }
        return 'dept-a';
    }
}
