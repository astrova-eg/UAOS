<?php
declare(strict_types=1);

require_once __DIR__ . '/src/GovernanceRuleChecker.php';
require_once __DIR__ . '/src/SpecificationChecker.php';

use UniversalCore\Validation\GovernanceRuleChecker;
use UniversalCore\Validation\SpecificationChecker;

$baseDir = dirname(__DIR__);
$reportsDir = $baseDir . '/reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0777, true);
}

echo "=== UNIVERSAL CORE STATIC GOVERNANCE VALIDATOR ===\n";
echo "Target Base Directory: {$baseDir}\n\n";

$findings = [];
$govChecker = new GovernanceRuleChecker($baseDir);
$specChecker = new SpecificationChecker($baseDir);

// 1. Canonical Governance Documents
$govChecker->checkCanonicalDocuments($findings);

// 2. Line Count Ceiling (Rule 2A)
$govChecker->checkLineCountCeiling($findings);

// 3. PHP Strict Typing and Zero MVP Placeholders
$govChecker->checkPhpStrictTypingAndPlaceholders($findings);

// 4. Canonical and Deprecated Registries
$govChecker->checkRegistryIntegrity($findings);

// 5. Agent Registry & Governance Core Inheritance
$specChecker->checkAgentRegistryAndInheritance($findings);

// 6. Skill Registry & Physical Resolution
$specChecker->checkSkillRegistryResolution($findings);

// 7. Separation of Duties
$specChecker->checkSeparationOfDuties($findings);

// 8. 56 Mandatory Sections across 112 Agent Specifications
$agentFiles = glob($baseDir . '/agents/*/*.md') ?: [];
$allHave56 = true;
$missingSecs = [];
$mandatorySections = [
    '1. Agent Identity', '2. Unique Agent ID', '3. Official Agent Name', '4. Department',
    '5. Primary Specialization', '6. Professional Role', '7. Mission Statement', '8. Professional Expertise',
    '9. Knowledge Domains', '10. Framework Expertise', '11. PHP and Laravel Expertise Where Applicable',
    '12. Supported Technology Context', '13. Core Responsibilities', '14. Detailed Responsibilities',
    '15. Secondary Responsibilities', '16. Explicit Exclusions', '17. Authority and Permissions',
    '18. Prohibited Actions', '19. Decision-Making Scope', '20. Required Inputs',
    '21. Input Validation Rules', '22. Required Context', '23. Output Definitions',
    '24. Output Formats', '25. Operational Workflow', '26. Step-by-Step Execution Method',
    '27. Analysis Methodology', '28. Technical Standards', '29. Quality Standards',
    '30. Security Requirements', '31. Tools and Capabilities', '32. Required Skills',
    '33. Shared Skills', '34. Required Reference Documents', '35. Collaborating Agents',
    '36. Collaboration Rules', '37. Handoff Protocol', '38. Escalation Protocol',
    '39. Conflict Handling', '40. Failure Handling', '41. Error Reporting',
    '42. Acceptance Criteria', '43. Quality Gates', '44. Verification Procedures',
    '45. Evidence Requirements', '46. Performance Indicators', '47. Completion Conditions',
    '48. Stop Conditions', '49. Reusability Rules', '50. Project Adaptation Rules',
    '51. Example Scenarios', '52. Example Inputs', '53. Example Outputs',
    '54. Common Mistakes', '55. Self-Review Checklist', '56. Version and Maintenance Policy'
];

foreach ($agentFiles as $af) {
    $c = (string)file_get_contents($af);
    $slug = basename($af, '.md');
    foreach ($mandatorySections as $sec) {
        $esc = preg_quote($sec, '/');
        if (!preg_match("/##\s+{$esc}/i", $c)) {
            $allHave56 = false;
            $missingSecs[] = "{$slug} missing {$sec}";
            break;
        }
    }
}

$findings[] = [
    'rule_id' => 'AGENT-SPEC-56-SECTIONS',
    'severity' => 'BLOCKER',
    'component' => 'agents/*/*.md',
    'expected' => 'All 112 agent operational specifications contain all 56 mandatory sections',
    'actual' => $allHave56 ? '100% compliance across all 112 agents' : implode(', ', array_slice($missingSecs, 0, 3)),
    'passed' => $allHave56,
    'remediation' => 'Complete missing mandatory sections in agent specifications'
];

// Summarize Results
$total = count($findings);
$passedCount = count(array_filter($findings, fn($f) => $f['passed']));
$failedCount = $total - $passedCount;
$status = ($failedCount === 0) ? 'FULLY_COMPLIANT' : 'NON_COMPLIANT';

echo "--------------------------------------------------------------------------------\n";
printf("%-28s | %-10s | %-8s | %s\n", "RULE ID", "SEVERITY", "RESULT", "COMPONENT");
echo "--------------------------------------------------------------------------------\n";
foreach ($findings as $f) {
    $resText = $f['passed'] ? '[PASS]' : '[FAIL]';
    printf("%-28s | %-10s | %-8s | %s\n", $f['rule_id'], $f['severity'], $resText, $f['component']);
    if (!$f['passed']) {
        echo "   -> Expected: {$f['expected']}\n";
        echo "   -> Actual:   {$f['actual']}\n";
        echo "   -> Action:   {$f['remediation']}\n";
    }
}
echo "--------------------------------------------------------------------------------\n";
echo "SUMMARY: {$passedCount} / {$total} Checks Passed. Status: {$status}\n";

$reportData = [
    'timestamp' => date('c'),
    'status' => $status,
    'total_checks' => $total,
    'passed' => $passedCount,
    'failed' => $failedCount,
    'findings' => $findings
];

file_put_contents($reportsDir . '/governance-validation-report.json', json_encode($reportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$md = "# Universal Core Governance Validation Report\n\n";
$md .= "**Timestamp:** " . date('c') . "  \n";
$md .= "**Status:** **{$status}** ({$passedCount}/{$total} Checks Passed)  \n\n";
$md .= "| Rule ID | Severity | Result | Component | Expected | Actual |\n|---|---|---|---|---|---|\n";
foreach ($findings as $f) {
    $r = $f['passed'] ? 'PASS' : '**FAIL**';
    $md .= "| `{$f['rule_id']}` | {$f['severity']} | {$r} | `{$f['component']}` | {$f['expected']} | {$f['actual']} |\n";
}
file_put_contents($reportsDir . '/governance-validation-report.md', $md);

exit($failedCount === 0 ? 0 : 1);
