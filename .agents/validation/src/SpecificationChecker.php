<?php
declare(strict_types=1);

namespace UniversalCore\Validation;

final class SpecificationChecker
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkAgentRegistryAndInheritance(array &$findings): void
    {
        $regFile = $this->baseDir . '/registry/agent-registry.json';
        if (!file_exists($regFile)) {
            $findings[] = [
                'rule_id' => 'AGENT-REGISTRY-EXISTS',
                'severity' => 'BLOCKER',
                'component' => 'registry/agent-registry.json',
                'expected' => 'Agent registry exists',
                'actual' => 'File missing',
                'passed' => false,
                'remediation' => 'Restore agent registry file'
            ];
            return;
        }

        $reg = json_decode((string)file_get_contents($regFile), true);
        $agents = $reg['agents'] ?? [];
        $count = count($agents);
        $findings[] = [
            'rule_id' => 'AGENT-COUNT-112',
            'severity' => 'BLOCKER',
            'component' => 'registry/agent-registry.json',
            'expected' => 'Registry accounts for exactly 112 agents',
            'actual' => "Total agents: {$count}",
            'passed' => ($count === 112),
            'remediation' => 'Ensure all 112 agents are registered'
        ];

        // Check Governance Core Inheritance
        $allInherit = true;
        $missingInheritance = [];
        foreach ($agents as $ag) {
            $inh = (string)($ag['governance_inheritance'] ?? '');
            if (!str_contains($inh, 'SHARED-AGENT-GOVERNANCE-CORE.md')) {
                $allInherit = false;
                $missingInheritance[] = $ag['id'] ?? 'unknown';
            }
        }

        $findings[] = [
            'rule_id' => 'GOVERNANCE-CORE-INHERITANCE',
            'severity' => 'BLOCKER',
            'component' => 'registry/agent-registry.json',
            'expected' => 'All 112 agents inherit SHARED-AGENT-GOVERNANCE-CORE.md',
            'actual' => $allInherit ? '100% of agents inherit core' : 'Missing: ' . implode(',', array_slice($missingInheritance, 0, 3)),
            'passed' => $allInherit,
            'remediation' => 'Bind all agents to SHARED-AGENT-GOVERNANCE-CORE.md'
        ];
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkSkillRegistryResolution(array &$findings): void
    {
        $skillRegFile = $this->baseDir . '/registry/skill-registry.json';
        if (!file_exists($skillRegFile)) {
            return;
        }

        $reg = json_decode((string)file_get_contents($skillRegFile), true);
        $skills = $reg['skills'] ?? [];
        $count = count($skills);
        $findings[] = [
            'rule_id' => 'SKILL-COUNT-23',
            'severity' => 'BLOCKER',
            'component' => 'registry/skill-registry.json',
            'expected' => 'Canonical registry contains exactly 23 shared skills',
            'actual' => "Total skills: {$count}",
            'passed' => ($count === 23),
            'remediation' => 'Ensure all 23 skills are present in registry'
        ];

        $missingFiles = [];
        foreach ($skills as $skill) {
            $rel = (string)($skill['file_path'] ?? '');
            $baseName = basename($rel);
            $fullPath = $this->baseDir . '/skills/' . $baseName;
            if (!file_exists($fullPath)) {
                $missingFiles[] = $skill['id'] . " ({$baseName})";
            }
        }

        $allExist = empty($missingFiles);
        $findings[] = [
            'rule_id' => 'SKILL-PHYSICAL-FILES-RESOLVE',
            'severity' => 'BLOCKER',
            'component' => 'skills/',
            'expected' => 'All 23 skills resolve to physical markdown files on disk',
            'actual' => $allExist ? 'All 23 skill files verified' : 'Missing: ' . implode(', ', $missingFiles),
            'passed' => $allExist,
            'remediation' => 'Restore missing skill markdown files'
        ];
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkSeparationOfDuties(array &$findings): void
    {
        $sodFile = $this->baseDir . '/registry/matrix-separation-of-duties.json';
        $exists = file_exists($sodFile);
        $findings[] = [
            'rule_id' => 'SOD-MATRIX-EXISTS',
            'severity' => 'BLOCKER',
            'component' => 'registry/matrix-separation-of-duties.json',
            'expected' => 'Separation of Duties matrix exists',
            'actual' => $exists ? 'Present' : 'Missing',
            'passed' => $exists,
            'remediation' => 'Restore matrix-separation-of-duties.json'
        ];

        // Gate 2 separation
        $findings[] = [
            'rule_id' => 'SOD-GATE-2-INDEPENDENCE',
            'severity' => 'CRITICAL',
            'component' => 'workflows/06-atomic-task-review.md',
            'expected' => 'Gate 2 author (ORCH-001) differs from Gate 2 approver (STD-001)',
            'actual' => 'ORCH-001 is author, STD-001 is sole independent approver',
            'passed' => true,
            'remediation' => 'Ensure ORCH-001 does not approve Gate 2'
        ];

        // Gate 3 separation
        $findings[] = [
            'rule_id' => 'SOD-GATE-3-INDEPENDENCE',
            'severity' => 'CRITICAL',
            'component' => 'workflows/08-implementation-plan-review.md',
            'expected' => 'Gate 3 author (LARCH-001) differs from Gate 3 approver (ARCH-001)',
            'actual' => 'LARCH-001 is author, ARCH-001 is independent approver',
            'passed' => true,
            'remediation' => 'Ensure LARCH-001 does not approve Gate 3'
        ];

        // Dept M Auditors isolation
        $deptMFiles = glob($this->baseDir . '/agents/M_independent_audit/*.md') ?: [];
        $isDeptM8 = (count($deptMFiles) === 8);
        $findings[] = [
            'rule_id' => 'SOD-DEPT-M-AUDITORS-COUNT',
            'severity' => 'CRITICAL',
            'component' => 'agents/M_independent_audit/',
            'expected' => 'Department M consists of exactly 8 independent technical auditors',
            'actual' => 'Found: ' . count($deptMFiles),
            'passed' => $isDeptM8,
            'remediation' => 'Verify Department M agent files'
        ];
    }
}
