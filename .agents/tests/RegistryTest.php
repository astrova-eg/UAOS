<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';

final class RegistryTest
{
    public static function run(): void
    {
        echo "Running RegistryTest...\n";
        $baseDir = dirname(__DIR__);

        // 1. Agent Registry Validation
        $agentRegPath = $baseDir . '/registry/agent-registry.json';
        Assert::assertTrue(file_exists($agentRegPath), "RegistryTest: agent-registry.json exists");

        $agentReg = json_decode((string)file_get_contents($agentRegPath), true);
        $agents = $agentReg['agents'] ?? [];
        Assert::assertEquals(112, count($agents), "RegistryTest: Exactly 112 agents present");

        $ids = [];
        $duplicateIds = [];
        $missingInheritance = [];
        foreach ($agents as $ag) {
            $id = (string)$ag['id'];
            if (isset($ids[$id])) {
                $duplicateIds[] = $id;
            }
            $ids[$id] = true;

            $inh = (string)($ag['governance_inheritance'] ?? '');
            if (!str_contains($inh, 'SHARED-AGENT-GOVERNANCE-CORE.md')) {
                $missingInheritance[] = $id;
            }

            // Verify specification path exists
            $specPath = $baseDir . '/' . $ag['specification_path'];
            if (!file_exists($specPath)) {
                Assert::assertTrue(false, "RegistryTest: Spec file missing for {$id} at {$specPath}");
            }
        }

        Assert::assertEquals(0, count($duplicateIds), "RegistryTest: Zero duplicate agent IDs");
        Assert::assertEquals(0, count($missingInheritance), "RegistryTest: All 112 agents inherit SHARED-AGENT-GOVERNANCE-CORE.md");

        // 2. Skill Registry Validation
        $skillRegPath = $baseDir . '/registry/skill-registry.json';
        Assert::assertTrue(file_exists($skillRegPath), "RegistryTest: skill-registry.json exists");

        $skillReg = json_decode((string)file_get_contents($skillRegPath), true);
        $skills = $skillReg['skills'] ?? [];
        Assert::assertEquals(23, count($skills), "RegistryTest: Exactly 23 skills present");

        $missingSkillFiles = [];
        foreach ($skills as $skill) {
            $baseName = basename((string)$skill['file_path']);
            $fullPath = $baseDir . '/skills/' . $baseName;
            if (!file_exists($fullPath)) {
                $missingSkillFiles[] = $skill['id'];
            }
        }
        Assert::assertEquals(0, count($missingSkillFiles), "RegistryTest: All 23 skill markdown files resolve physically");

        // 3. Deprecated Duplicate Skill Registry Validation
        $dupPath = $baseDir . '/skills/skill-registry.json';
        $dupReg = json_decode((string)file_get_contents($dupPath), true);
        Assert::assertTrue(isset($dupReg['deprecated']) && $dupReg['deprecated'] === true, "RegistryTest: Duplicate skill registry is marked deprecated");
    }
}
