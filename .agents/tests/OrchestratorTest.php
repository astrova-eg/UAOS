<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once dirname(__DIR__) . '/runtime/src/Orchestrator.php';
require_once dirname(__DIR__) . '/runtime/src/SkillEngine.php';
require_once dirname(__DIR__) . '/runtime/src/ToolGateway.php';
require_once dirname(__DIR__) . '/runtime/src/PromptBuilder.php';
require_once dirname(__DIR__) . '/runtime/src/EvidenceCollector.php';

use UniversalCore\Runtime\Orchestrator;

final class OrchestratorTest
{
    public static function run(): void
    {
        echo "Running OrchestratorTest...\n";
        $orch = new Orchestrator();

        // 1. Task classification
        $c1 = $orch->classifyTask("Need a deep architectural design for multi-tenant isolation");
        Assert::assertEquals('architecture', $c1['category'], "OrchestratorTest: Classifies architecture task correctly");
        Assert::assertEquals(2, $c1['stage'], "OrchestratorTest: Maps architecture task to stage 2");

        $c2 = $orch->classifyTask("Implement WCAG 2.2 AA accessibility components for dashboard");
        Assert::assertEquals('ui_ux', $c2['category'], "OrchestratorTest: Classifies UI/UX task correctly");

        // 2. Agent resolution from canonical registry
        $ag1 = $orch->resolveAgent('architecture');
        Assert::assertEquals('ARCH-001', $ag1['id'], "OrchestratorTest: Resolves ARCH-001 for architecture");

        $ag2 = $orch->resolveAgent('ui_ux');
        Assert::assertEquals('A11Y-001', $ag2['id'], "OrchestratorTest: Resolves A11Y-001 for accessibility UI/UX");

        // 3. Separation of duties reviewer assignment
        $rev1 = $orch->assignReviewer('ORCH-001', 6);
        Assert::assertEquals('STD-001', $rev1, "OrchestratorTest: Gate 2 task reviewer is independently STD-001");

        $rev2 = $orch->assignReviewer('LARCH-001', 8);
        Assert::assertEquals('ARCH-001', $rev2, "OrchestratorTest: Gate 3 plan reviewer is independently ARCH-001");

        $rev3 = $orch->assignReviewer('CORE-001', 12);
        Assert::assertEquals('AUD-008', $rev3, "OrchestratorTest: Stage 12 audit reviewer is Dept M AUD-008");

        // 4. End-to-end task execution & evidence collection
        $runResult = $orch->runTask([
            'task_id' => 'TEST-TASK-001',
            'task_description' => 'Verify accessibility standards compliance',
            'preferred_agent_id' => 'A11Y-001'
        ]);

        Assert::assertEquals('COMPLETED', $runResult['status'], "OrchestratorTest: Task completes with status COMPLETED");
        Assert::assertTrue(count($runResult['skills_loaded']) > 0, "OrchestratorTest: Skills successfully loaded during orchestration");
        Assert::assertTrue(file_exists($runResult['report']['json_path']), "OrchestratorTest: Evidence JSON report generated");
        Assert::assertTrue(file_exists($runResult['report']['md_path']), "OrchestratorTest: Evidence Markdown report generated");

        // Clean up test report files
        if (file_exists($runResult['report']['json_path'])) {
            unlink($runResult['report']['json_path']);
        }
        if (file_exists($runResult['report']['md_path'])) {
            unlink($runResult['report']['md_path']);
        }

        // 5. Specialist delegation execution
        $delResult = $orch->runTask([
            'task_id' => 'TEST-DEL-001',
            'task_description' => 'Architect SaaS multi-tenant isolation',
            'preferred_agent_id' => 'ARCH-001',
            'delegations' => [
                ['agent_id' => 'DBA-001', 'task' => 'Design tenant PostgreSQL schema'],
                ['agent_id' => 'SEC-001', 'task' => 'Audit tenant encryption boundaries']
            ]
        ]);
        Assert::assertEquals('COMPLETED', $delResult['status'], "OrchestratorTest: Delegated execution status is COMPLETED");
        Assert::assertTrue(isset($delResult['delegations']['DBA-001']), "OrchestratorTest: DBA-001 delegation recorded");
        Assert::assertTrue(isset($delResult['delegations']['SEC-001']), "OrchestratorTest: SEC-001 delegation recorded");
        if (file_exists($delResult['report']['json_path'])) {
            unlink($delResult['report']['json_path']);
        }
        if (file_exists($delResult['report']['md_path'])) {
            unlink($delResult['report']['md_path']);
        }
    }
}
