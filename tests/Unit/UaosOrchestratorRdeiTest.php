<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

class UaosOrchestratorRdeiTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $evidence = new EvidenceCollector($resolver);

        self::testOrchestratorDispatchWithResearchPassed($resolver, $evidence);
        self::testOrchestratorGate0BlockingEnforcement($resolver, $evidence);
        self::testOrchestratorLevel0Exemption($resolver, $evidence);
        self::testGate4PreservationAndTelemetry($resolver, $evidence);
    }

    private static function testOrchestratorDispatchWithResearchPassed(PathResolver $resolver, EvidenceCollector $evidence): void
    {
        $mockResearchEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                $claim = new ExtractedClaim(
                    claimId: 'CLM-01',
                    statement: 'Strict types ensure type safety in PHP 8.2+.',
                    status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
                    confidenceScore: 0.99,
                    applicableVersions: ['8.2', '8.3'],
                    citations: [['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'strict types']]
                );

                return (new ResearchDossier(
                    dossierId: 'DOS-TEST-PASS-01',
                    taskId: $request->taskId,
                    projectIdentity: 'TestProject',
                    researchDepth: DepthProfile::LEVEL_L1,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true]],
                    claims: [$claim],
                    gate0Status: ResearchDossier::GATE_STATUS_PASSED
                ))->seal();
            }

            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }

            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::passed($taskId, 'DOS-TEST-PASS-01', hash('sha256', 'dossier'));
            }
        };

        $orchestrator = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $evidence,
            researchEngine: $mockResearchEngine
        );

        $task = new TaskEnvelope('TASK-ARCH-01', 'Architect user authentication repository', null, 2);
        $plan = $orchestrator->planTask($task);

        Assert::assertFalse($plan['gate_0_blocked'], 'Gate 0 is not blocked for valid research');
        Assert::assertTrue($plan['gate_0_clearance']->isPassed(), 'Gate 0 clearance is PASSED');
        Assert::assertStringContains('SECTION 2.6: VERIFIED RESEARCH & TECHNICAL EVIDENCE', $plan['compiled_prompt'], 'Section 2.6 is injected');

        $result = $orchestrator->execute($task);
        Assert::assertTrue($result->isSuccess(), 'Execution succeeds when Gate 0 is cleared');
        Assert::assertEquals(0, $result->exitCode, 'Exit code is 0');
    }

    private static function testOrchestratorGate0BlockingEnforcement(PathResolver $resolver, EvidenceCollector $evidence): void
    {
        $mockBlockedEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                return (new ResearchDossier(
                    dossierId: 'DOS-TEST-FAIL-01',
                    taskId: $request->taskId,
                    projectIdentity: 'TestProject',
                    researchDepth: DepthProfile::LEVEL_L2,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    sourcesConsulted: [],
                    claims: [],
                    gate0Status: ResearchDossier::GATE_STATUS_BLOCKED
                ))->seal();
            }

            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }

            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::blocked(
                    taskId: $taskId,
                    reason: 'Mandatory source quorum not satisfied',
                    violations: ['Zero sources available for depth L2'],
                    operationalCondition: Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED
                );
            }
        };

        $orchestrator = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $evidence,
            researchEngine: $mockBlockedEngine
        );

        $task = new TaskEnvelope('TASK-BLOCKED-01', 'Architect payments integration', null, 2);
        $plan = $orchestrator->planTask($task);

        Assert::assertTrue($plan['gate_0_blocked'], 'Gate 0 is marked as blocked in plan');
        Assert::assertTrue($plan['compiled_prompt'] === null, 'Compiled prompt is suppressed when Gate 0 is blocked');

        $result = $orchestrator->execute($task);
        Assert::assertFalse($result->isSuccess(), 'Execution fails closed when Gate 0 is blocked');
        Assert::assertEquals('BLOCKED', $result->status, 'ExecutionResult status is BLOCKED');
        Assert::assertEquals(2, $result->exitCode, 'Blocked exit code is 2');
        Assert::assertStringContains('failed Gate 0 research clearance', $result->output, 'Output explains Gate 0 failure');
    }

    private static function testOrchestratorLevel0Exemption(PathResolver $resolver, EvidenceCollector $evidence): void
    {
        $mockEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                throw new \RuntimeException('Should not be called for L0 tasks');
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::exempt($taskId);
            }
        };

        $orchestrator = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $evidence,
            researchEngine: $mockEngine
        );

        // Trivial prompt maps to L0
        $task = new TaskEnvelope('TASK-TRIVIAL-01', 'Fix minor typo in local variable name', null, 1);
        $plan = $orchestrator->planTask($task);

        Assert::assertFalse($plan['gate_0_blocked'], 'L0 task is not blocked');
        Assert::assertTrue($plan['gate_0_clearance']->isExempt(), 'Clearance is EXEMPT for L0');
        Assert::assertTrue($plan['compiled_prompt'] !== null, 'Compiled prompt is generated');

        $result = $orchestrator->execute($task);
        Assert::assertTrue($result->isSuccess(), 'L0 task executes successfully');
    }

    private static function testGate4PreservationAndTelemetry(PathResolver $resolver, EvidenceCollector $evidence): void
    {
        $mockEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                return (new ResearchDossier(
                    dossierId: 'DOS-TELEMETRY-01',
                    taskId: $request->taskId,
                    projectIdentity: 'TelemetryApp',
                    researchDepth: DepthProfile::LEVEL_L1,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true]],
                    claims: [new ExtractedClaim('CLM-01', 'Valid', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
                        ['source_id' => 'PHP_DOCS', 'content_hash' => 'h', 'quote_segment' => 'q']
                    ])],
                    gate0Status: ResearchDossier::GATE_STATUS_PASSED
                ))->seal();
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::passed($taskId, 'DOS-TELEMETRY-01', hash('sha256', 'telemetry'));
            }
        };

        $orchestrator = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $evidence,
            researchEngine: $mockEngine
        );

        $task = new TaskEnvelope('TASK-TELEMETRY-01', 'Implement core service repository', null, 2);
        $result = $orchestrator->execute($task);

        Assert::assertEquals('PASSED', $result->telemetry['gate_0_status'] ?? '', 'Gate 0 status recorded in execution telemetry');
    }

    private static function recursiveRemove(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? self::recursiveRemove($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
