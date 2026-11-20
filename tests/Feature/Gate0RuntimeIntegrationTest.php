<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Feature;

use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

/**
 * End-to-End runtime integration test verifying Gate 0 enforcement using real components.
 *
 * Exercises real Orchestrator, real ResearchOrchestrator, real Gate0ClearanceVerifier,
 * real ResearchDossierRepository, and real EvidenceCollector across BLOCKED and PASSED scenarios.
 */
class Gate0RuntimeIntegrationTest
{
    public static function run(): void
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_gate0_e2e_' . bin2hex(random_bytes(4));
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'reports', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'evidence', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'research' . DIRECTORY_SEPARATOR . 'dossiers', 0755, true);

        try {
            $resolver = new PathResolver(projectRootDir: $tmpDir);
            $repo = new ResearchDossierRepository(storageDir: $tmpDir . DIRECTORY_SEPARATOR . 'research');
            $evidence = new EvidenceCollector($resolver);
            $verifier = new Gate0ClearanceVerifier($repo, $resolver, $evidence);

            $retrievalProvider = new class implements RetrievalProviderInterface {
                public function getName(): string { return 'in_memory_test'; }
                public function supports(SourceDefinition $source): bool { return true; }
                public function retrieve(string $target, SourceDefinition $source, array $options = []): array {
                    $content = "# Architecture Standard\n\n"
                        . "Strict separation of concerns isolates domain models from persistence layers.\n\n"
                        . "Strong cryptographic seals prevent unauthorized state modification.";
                    return [
                        'content' => $content,
                        'hash' => hash('sha256', $content),
                        'format' => 'markdown',
                        'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                    ];
                }
            };

            $retrieval = new RetrievalManager([$retrievalProvider]);
            $researchOrchestrator = new ResearchOrchestrator(
                pathResolver: $resolver,
                dossierRepository: $repo,
                clearanceVerifier: $verifier,
                retrievalManager: $retrieval,
                evidenceCollector: $evidence
            );

            $orchestrator = new Orchestrator(
                pathResolver: $resolver,
                evidenceCollector: $evidence,
                researchEngine: $researchOrchestrator
            );

            self::testScenario1Blocked($orchestrator, $repo);
            self::testScenario2Passed($orchestrator, $repo);
            self::testScenario3DisconnectedEngineFailsClosed($resolver);
            self::testScenario4L0ExemptionPreserved($orchestrator);
        } finally {
            self::recursiveRemove($tmpDir);
        }
    }

    private static function testScenario1Blocked(Orchestrator $orchestrator, ResearchDossierRepository $repo): void
    {
        // Task requiring depth L2+ entering real integrated path without valid evidence sources
        $task = new TaskEnvelope('TASK-E2E-BLOCKED-01', 'Architect distributed queue event processor', null, 2);
        $plan = $orchestrator->planTask($task);

        Assert::assertTrue($plan['gate_0_blocked'], 'Scenario 1: Gate 0 is marked BLOCKED in plan');
        Assert::assertTrue($plan['compiled_prompt'] === null, 'Scenario 1: Compiled prompt is suppressed when blocked');
        Assert::assertTrue($plan['gate_0_clearance'] instanceof Gate0ClearanceResult, 'Clearance result object generated');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED, $plan['gate_0_clearance']->operationalCondition, 'Condition is QUORUM_UNSATISFIED');

        $result = $orchestrator->execute($task);

        Assert::assertEquals('BLOCKED', $result->status, 'Scenario 1: Execution status is BLOCKED');
        Assert::assertEquals(2, $result->exitCode, 'Scenario 1: Exit code is 2');
        Assert::assertFalse($result->isSuccess(), 'Scenario 1: Execution did not succeed');
        Assert::assertStringContains('failed Gate 0 research clearance', $result->output, 'Scenario 1: Output explains Gate 0 failure');
        Assert::assertFalse(str_contains($result->output, 'successfully planned and dispatched'), 'Scenario 1: Dispatch boundary NOT reached');
        Assert::assertEquals('BLOCKED', $result->telemetry['gate_0_status'], 'Scenario 1: Telemetry records BLOCKED status');

        // Confirm real dossier was persisted with GATE_STATUS_BLOCKED
        $dossier = $plan['research_dossier'];
        Assert::assertTrue($dossier instanceof ResearchDossier, 'Scenario 1: Real dossier was created');
        Assert::assertEquals(ResearchDossier::GATE_STATUS_BLOCKED, $dossier->gate0Status, 'Scenario 1: Dossier status is BLOCKED');
        Assert::assertTrue($repo->has($dossier->dossierId), 'Scenario 1: Blocked dossier persisted to repository');
    }

    private static function testScenario2Passed(Orchestrator $orchestrator, ResearchDossierRepository $repo): void
    {
        $sourceDef = new SourceDefinition(
            sourceId: 'SRC-E2E-01',
            officialName: 'Authoritative Architecture Specification',
            description: 'Core architectural patterns for enterprise software',
            owner: ['entity_name' => 'Standards Body', 'entity_type' => 'Standard Body'],
            canonicalUrl: 'https://standards.example.com/arch',
            domains: ['A'],
            subdomains: ['A.01'],
            authorityTier: 1
        );

        // Task with valid research evidence enters the real integrated path (Stage 10 maps to L1, single source quorum)
        $task = new TaskEnvelope(
            taskId: 'TASK-E2E-PASSED-01',
            taskPrompt: 'Implement data sanitization helper functions',
            agentId: null,
            stage: 10,
            metadata: ['sources' => [$sourceDef]]
        );

        $plan = $orchestrator->planTask($task);

        Assert::assertFalse($plan['gate_0_blocked'], 'Scenario 2: Gate 0 is NOT blocked');
        Assert::assertTrue($plan['gate_0_clearance'] instanceof Gate0ClearanceResult, 'Clearance result object generated');
        Assert::assertTrue($plan['gate_0_clearance']->isPassed(), 'Scenario 2: Gate 0 clearance is PASSED');
        Assert::assertTrue($plan['compiled_prompt'] !== null, 'Scenario 2: Prompt compiled');
        Assert::assertStringContains('SECTION 2.6: VERIFIED RESEARCH & TECHNICAL EVIDENCE', $plan['compiled_prompt'], 'Scenario 2: Section 2.6 evidence injected');

        $result = $orchestrator->execute($task);

        Assert::assertEquals('SUCCESS', $result->status, 'Scenario 2: Execution status is SUCCESS');
        Assert::assertEquals(0, $result->exitCode, 'Scenario 2: Exit code is 0');
        Assert::assertTrue($result->isSuccess(), 'Scenario 2: Execution succeeds');
        Assert::assertStringContains('successfully planned and dispatched', $result->output, 'Scenario 2: Reaches dispatch boundary');
        Assert::assertEquals('PASSED', $result->telemetry['gate_0_status'], 'Scenario 2: Telemetry records PASSED status');

        // Confirm real dossier was persisted with GATE_STATUS_PASSED and valid seal
        $dossier = $plan['research_dossier'];
        Assert::assertTrue($dossier instanceof ResearchDossier, 'Scenario 2: Real dossier was created');
        Assert::assertEquals(ResearchDossier::GATE_STATUS_PASSED, $dossier->gate0Status, 'Scenario 2: Dossier marked PASSED');
        Assert::assertTrue($dossier->isSealed(), 'Scenario 2: Dossier is cryptographically sealed');
        Assert::assertTrue($dossier->verifyIntegrity(), 'Scenario 2: Dossier cryptographic integrity verified');
        Assert::assertTrue(count($dossier->claims) >= 1, 'Scenario 2: Dossier contains extracted claims');
        Assert::assertTrue($repo->has($dossier->dossierId), 'Scenario 2: Passed dossier persisted in repository');
    }

    private static function testScenario3DisconnectedEngineFailsClosed(PathResolver $resolver): void
    {
        // Orchestrator without research engine executes a task requiring research
        $unwired = new Orchestrator(pathResolver: $resolver);
        $task = new TaskEnvelope('TASK-E2E-UNWIRED', 'Architect distributed authentication gateway', null, 2);

        $plan = $unwired->planTask($task);
        Assert::assertTrue($plan['gate_0_blocked'], 'Scenario 3: Missing research engine fails closed (gate_0_blocked=true)');
        Assert::assertTrue($plan['compiled_prompt'] === null, 'Scenario 3: Prompt suppressed');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_UNAVAILABLE_DEPENDENCY, $plan['gate_0_clearance']->operationalCondition, 'Condition is UNAVAILABLE_DEPENDENCY');

        $result = $unwired->execute($task);
        Assert::assertEquals('BLOCKED', $result->status, 'Scenario 3: Execution blocked without research engine');
        Assert::assertEquals(2, $result->exitCode, 'Scenario 3: Blocked exit code 2');
        Assert::assertFalse(str_contains($result->output, 'successfully planned and dispatched'), 'Scenario 3: Dispatch boundary NOT reached');
    }

    private static function testScenario4L0ExemptionPreserved(Orchestrator $orchestrator): void
    {
        $task = new TaskEnvelope('TASK-E2E-L0', 'Fix typo in local variable name', null, 1);
        $plan = $orchestrator->planTask($task);

        Assert::assertFalse($plan['gate_0_blocked'], 'Scenario 4: L0 task is exempt from research');
        Assert::assertTrue($plan['gate_0_clearance']->isExempt(), 'Scenario 4: Clearance status is EXEMPT');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_EXEMPT_L0, $plan['gate_0_clearance']->operationalCondition, 'Condition is EXEMPT_L0');

        $result = $orchestrator->execute($task);
        Assert::assertEquals('SUCCESS', $result->status, 'Scenario 4: L0 execution succeeds');
        Assert::assertEquals(0, $result->exitCode, 'Scenario 4: L0 exit code is 0');
        Assert::assertStringContains('successfully planned and dispatched', $result->output, 'Scenario 4: Reaches dispatch boundary');
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
