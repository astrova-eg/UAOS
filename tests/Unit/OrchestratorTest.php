<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Tests\Assert;

class OrchestratorTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();

        $clearedEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                return (new ResearchDossier(
                    dossierId: 'DOS-TEST-ORCH-01',
                    taskId: $request->taskId,
                    projectIdentity: 'Core',
                    researchDepth: DepthProfile::LEVEL_L1,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    gate0Status: ResearchDossier::GATE_STATUS_PASSED
                ))->seal();
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::passed($taskId, 'DOS-TEST-ORCH-01', hash('sha256', 'dossier'));
            }
        };

        $orchestrator = new Orchestrator($resolver, researchEngine: $clearedEngine);

        $task = new TaskEnvelope('TASK-ORCH-001', 'Perform security audit and compliance verification');
        $plan = $orchestrator->planTask($task);

        Assert::assertEquals('AUD-001', $plan['agent_id'], "Task classifier maps security audit to AUD-001");
        Assert::assertEquals('dept-m-independent-audit', $plan['department_id'], "Maps to Department M");
        Assert::assertStringContains('SECTION 1: SHARED AGENT GOVERNANCE CORE', $plan['compiled_prompt'], "Compiled prompt has Section 1");
        Assert::assertStringContains('SECTION 2.5: ACTIVE PROJECT TECHNOLOGY PROFILE', $plan['compiled_prompt'], "Compiled prompt has Section 2.5");

        $execResult = $orchestrator->execute($task);
        Assert::assertTrue($execResult->isSuccess(), "Orchestrator execution returned success");
        Assert::assertEquals(0, $execResult->exitCode, "Exit code is 0");

        // Verification: missing research engine fails closed for tasks requiring research
        $unwiredOrchestrator = new Orchestrator($resolver);
        $blockedPlan = $unwiredOrchestrator->planTask($task);
        Assert::assertTrue($blockedPlan['gate_0_blocked'], "Gate 0 is blocked when research engine is absent");
        Assert::assertTrue($blockedPlan['compiled_prompt'] === null, "Compiled prompt is suppressed when Gate 0 blocked");
        $blockedResult = $unwiredOrchestrator->execute($task);
        Assert::assertEquals('BLOCKED', $blockedResult->status, "Execution status is BLOCKED without research engine");
        Assert::assertEquals(2, $blockedResult->exitCode, "Exit code is 2 for blocked execution");

        // Verification: L0 exemption is preserved without research engine
        $l0Task = new TaskEnvelope('TASK-ORCH-L0', 'Fix typo in documentation comment');
        $l0Plan = $unwiredOrchestrator->planTask($l0Task);
        Assert::assertFalse($l0Plan['gate_0_blocked'], "Gate 0 is exempt for L0 task");
        Assert::assertTrue($l0Plan['compiled_prompt'] !== null, "Prompt is compiled for L0 exempt task");
        $l0Result = $unwiredOrchestrator->execute($l0Task);
        Assert::assertTrue($l0Result->isSuccess(), "L0 task execution succeeds under exemption");
        Assert::assertEquals(0, $l0Result->exitCode, "L0 exit code is 0");
    }
}
