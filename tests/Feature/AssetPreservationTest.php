<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Feature;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Tests\Assert;

class AssetPreservationTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $resources = $resolver->getPackageResourcesDir();

        // 1. Department Charters: 14 departments (DEPT-A through DEPT-N)
        $deptFiles = glob($resources . '/departments/*.md') ?: [];
        Assert::assertCount(14, $deptFiles, "All 14 department charters exist (DEPT-A through DEPT-N)");

        // 2. Shared Skills: 23 canonical skills
        $skillFiles = glob($resources . '/skills/*.md') ?: [];
        Assert::assertCount(23, $skillFiles, "All 23 shared engineering skills exist");

        // 3. Agent Specifications: 112 agents
        $agentFiles = glob($resources . '/agents/*/*.md') ?: [];
        Assert::assertCount(112, $agentFiles, "All 112 physical agent specifications exist across 14 departments");

        // 4. Agent Registry: 112 agents in registry
        $agentRegFile = $resources . '/registry/agent-registry.json';
        Assert::assertTrue(file_exists($agentRegFile), "Agent registry exists");
        $agentReg = json_decode((string)file_get_contents($agentRegFile), true);
        $regAgents = (array)($agentReg['agents'] ?? []);
        Assert::assertCount(112, $regAgents, "Agent registry accounts for exactly 112 unique agents");

        // 5. Skill Registry: 23 skills in registry
        $skillRegFile = $resources . '/registry/skill-registry.json';
        Assert::assertTrue(file_exists($skillRegFile), "Skill registry exists");
        $skillReg = json_decode((string)file_get_contents($skillRegFile), true);
        $regSkills = (array)($skillReg['skills'] ?? []);
        Assert::assertCount(23, $regSkills, "Skill registry accounts for exactly 23 shared skills");

        // 6. Lifecycle Workflows: 14 stage workflows + matrix + escalation
        $workflowFiles = glob($resources . '/workflows/*.md') ?: [];
        Assert::assertTrue(count($workflowFiles) >= 14, "All 14 lifecycle workflows exist");

        // 7. Governance Policies: Constitution, Shared Governance Core
        Assert::assertTrue(file_exists($resources . '/governance/ENGINEERING-CONSTITUTION.md'), "Constitution exists");
        Assert::assertTrue(file_exists($resources . '/governance/SHARED-AGENT-GOVERNANCE-CORE.md'), "Shared Governance Core exists");
        Assert::assertTrue(file_exists($resources . '/governance/FILE-SIZE-GOVERNANCE.md'), "File Size Governance exists");
    }
}
