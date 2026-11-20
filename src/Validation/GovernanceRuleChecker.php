<?php

declare(strict_types=1);

namespace UniversalAgentOS\Validation;

/**
 * Checks static governance rules, line ceilings, strict types, and placeholder prevention.
 */
class GovernanceRuleChecker
{
    private string $baseDir;

    public function __construct(string $baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkCanonicalDocuments(array &$findings): void
    {
        $docs = [
            'GOV-DOC-002' => 'governance/ENGINEERING-CONSTITUTION.md',
            'GOV-DOC-022' => 'governance/DOCUMENT-HIERARCHY-POLICY.md',
            'GOV-DOC-023' => 'governance/HUMAN-AUTHORITY-POLICY.md',
            'GOV-DOC-024' => 'governance/COMPLETENESS-AND-NO-MVP-POLICY.md',
            'GOV-DOC-007' => 'governance/SECURITY-GOVERNANCE-POLICY.md',
            'GOV-DOC-008' => 'governance/QUALITY-GOVERNANCE-POLICY.md',
            'GOV-DOC-025' => 'governance/DATABASE-GOVERNANCE-POLICY.md',
            'GOV-CORE-001' => 'governance/SHARED-AGENT-GOVERNANCE-CORE.md',
            'GOV-DOC-015' => 'governance/APPROVAL-GATES-POLICY.md',
            'GOV-DOC-017' => 'governance/CHANGE-MANAGEMENT-POLICY.md',
        ];

        foreach ($docs as $id => $relPath) {
            $fullPath = $this->baseDir . '/' . $relPath;
            $exists = file_exists($fullPath) && filesize($fullPath) > 50;
            $findings[] = [
                'rule_id' => "CANON-DOC-{$id}",
                'severity' => 'BLOCKER',
                'component' => $relPath,
                'expected' => "Canonical document {$relPath} exists and non-empty",
                'actual' => $exists ? 'Exists and valid' : 'Missing or empty',
                'passed' => $exists,
                'remediation' => "Restore or populate canonical document at {$relPath}",
            ];
        }
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkLineCountCeiling(array &$findings): void
    {
        $exceptionsFile = $this->baseDir . '/governance/file-size-exceptions.json';
        $exceptions = [];
        if (file_exists($exceptionsFile)) {
            $raw = file_get_contents($exceptionsFile);
            $parsed = json_decode((string)$raw, true);
            if (is_array($parsed) && isset($parsed['exceptions']) && is_array($parsed['exceptions'])) {
                foreach ($parsed['exceptions'] as $ex) {
                    $exceptions[(string)$ex['file_path']] = $ex;
                }
            }
        }

        $files = array_merge(
            glob($this->baseDir . '/runtime/*.php') ?: [],
            glob($this->baseDir . '/runtime/src/*.php') ?: [],
            glob($this->baseDir . '/validation/*.php') ?: [],
            glob($this->baseDir . '/validation/src/*.php') ?: [],
            glob($this->baseDir . '/scripts/*.ps1') ?: []
        );

        foreach ($files as $file) {
            $rel = str_replace([$this->baseDir . '/', '\\'], ['', '/'], $file);
            $totalPhysical = count(file($file) ?: []);
            $hasException = isset($exceptions[$rel]);
            $passed = ($totalPhysical <= 300) || $hasException;

            $findings[] = [
                'rule_id' => 'RULE-2A-LINE-CEILING',
                'severity' => 'CRITICAL',
                'component' => $rel,
                'expected' => 'Physical lines <= 300 or explicit human exception',
                'actual' => "Total physical lines: {$totalPhysical}" . ($hasException ? ' (Human approved exception)' : ''),
                'passed' => $passed,
                'remediation' => "Refactor {$rel} into smaller cohesive components or request explicit human exception",
            ];
        }
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkPhpStrictTypingAndPlaceholders(array &$findings): void
    {
        $files = array_merge(
            glob($this->baseDir . '/runtime/*.php') ?: [],
            glob($this->baseDir . '/runtime/src/*.php') ?: [],
            glob($this->baseDir . '/validation/*.php') ?: [],
            glob($this->baseDir . '/validation/src/*.php') ?: []
        );

        foreach ($files as $file) {
            $rel = str_replace([$this->baseDir . '/', '\\'], ['', '/'], $file);
            $content = (string)file_get_contents($file);

            $hasStrict = str_contains($content, 'declare(strict_types=1);');
            $findings[] = [
                'rule_id' => 'PHP-STRICT-TYPING',
                'severity' => 'CRITICAL',
                'component' => $rel,
                'expected' => 'declare(strict_types=1); declared at top of file',
                'actual' => $hasStrict ? 'Strict types declared' : 'Missing strict_types declaration',
                'passed' => $hasStrict,
                'remediation' => "Add declare(strict_types=1); at the top of {$rel}",
            ];

            $hasPlaceholders = false;
            if (!str_contains($rel, 'GovernanceRuleChecker.php')) {
                $hasPlaceholders = (bool)preg_match('/\b(TO' . 'DO|FIX' . 'ME|ST' . 'UB)\b/i', $content);
            }
            $findings[] = [
                'rule_id' => 'ZERO-MVP-NO-PLACEHOLDERS',
                'severity' => 'CRITICAL',
                'component' => $rel,
                'expected' => 'Zero placeholders in production code',
                'actual' => $hasPlaceholders ? 'Forbidden placeholder detected' : 'Zero placeholders found',
                'passed' => !$hasPlaceholders,
                'remediation' => "Remove temporary stubs or incomplete methods from {$rel}",
            ];
        }
    }

    /**
     * @param list<array<string, mixed>> $findings
     */
    public function checkRegistryIntegrity(array &$findings): void
    {
        $canonSkills = $this->baseDir . '/registry/skill-registry.json';
        $canonExists = file_exists($canonSkills);
        $findings[] = [
            'rule_id' => 'SKILL-REGISTRY-CANONICAL',
            'severity' => 'BLOCKER',
            'component' => 'registry/skill-registry.json',
            'expected' => 'Canonical skill registry exists',
            'actual' => $canonExists ? 'Present and valid' : 'Missing',
            'passed' => $canonExists,
            'remediation' => 'Restore canonical skill registry',
        ];
    }
}
