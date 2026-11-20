<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Research\Formatting\CitationFormatter;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;

/**
 * Dynamic prompt compiler injecting Active Project Profile (Section 2.5),
 * Verified Research Evidence (Section 2.6), Governance, and Skills.
 */
class PromptBuilder
{
    private PathResolverInterface $pathResolver;
    private CitationFormatter $citationFormatter;

    public function __construct(
        PathResolverInterface $pathResolver,
        ?CitationFormatter $citationFormatter = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->citationFormatter = $citationFormatter ?? new CitationFormatter();
    }

    /**
     * Compiles the full agent system prompt with 7 sections (or 8 when Section 2.6 is active).
     *
     * @param string $agentSpec Full content of agent specification
     * @param string $deptCharter Full content of department charter
     * @param TaskEnvelope $task Task context envelope
     * @param array<string, mixed> $projectProfile Discovered project profile
     * @param array<string, string> $skills Filtered skills content map
     * @param ResearchDossier|null $dossier Optional verified research dossier
     * @param Gate0ClearanceResult|null $clearance Optional Gate 0 clearance decision
     */
    public function buildPrompt(
        string $agentSpec,
        string $deptCharter,
        TaskEnvelope $task,
        array $projectProfile = [],
        array $skills = [],
        ?ResearchDossier $dossier = null,
        ?Gate0ClearanceResult $clearance = null
    ): string {
        $sections = [];

        // SECTION 1: GOVERNANCE CORE
        $govCorePath = $this->pathResolver->resolveResourcePath('governance/SHARED-AGENT-GOVERNANCE-CORE.md');
        $sections[] = "# SECTION 1: SHARED AGENT GOVERNANCE CORE\n\n"
            . (file_exists($govCorePath) ? (string)file_get_contents($govCorePath) : '');

        // SECTION 2: CONSTITUTION
        $constPath = $this->pathResolver->resolveResourcePath('governance/ENGINEERING-CONSTITUTION.md');
        $sections[] = "# SECTION 2: ENGINEERING CONSTITUTION & INVARIANTS\n\n"
            . (file_exists($constPath) ? (string)file_get_contents($constPath) : '');

        // SECTION 2.5: ACTIVE PROJECT TECHNOLOGY PROFILE
        $sections[] = $this->buildSection25Profile($projectProfile);

        // SECTION 2.6: VERIFIED RESEARCH & TECHNICAL EVIDENCE (RDEI)
        if ($dossier !== null) {
            $effectiveClearance = $clearance ?? Gate0ClearanceResult::blocked(
                $task->taskId,
                'Unverified dossier supplied without authoritative clearance.',
                ['Missing Gate 0 clearance verification'],
                $dossier->dossierId,
                $dossier->dossierHash
            );
            $sections[] = $this->citationFormatter->formatSection26($dossier, $effectiveClearance);
        }

        // SECTION 3: AGENT SPECIFICATION
        $sections[] = "# SECTION 3: AGENT IDENTITY & ROLE SPECIFICATION\n\n{$agentSpec}";

        // SECTION 4: DEPARTMENT CHARTER
        $sections[] = "# SECTION 4: DEPARTMENT CHARTER\n\n{$deptCharter}";

        // SECTION 5: TASK SCOPE
        $sections[] = "# SECTION 5: TASK SCOPE & BOUNDARIES\n\n"
            . "- **Task ID:** `{$task->taskId}`\n"
            . "- **Lifecycle Stage:** {$task->stage}\n"
            . "- **Objective:**\n{$task->taskPrompt}\n";

        // SECTION 6: RELEVANT ENGINEERING SKILLS
        $skillsBlock = "# SECTION 6: RELEVANT ENGINEERING SKILLS\n\n";
        foreach ($skills as $skillName => $skillContent) {
            $skillsBlock .= "## Skill: {$skillName}\n\n{$skillContent}\n\n---\n\n";
        }
        $sections[] = $skillsBlock;

        // SECTION 7: OPERATIONAL PERMISSIONS & TOOL GATEWAY RULES
        $sections[] = "# SECTION 7: OPERATIONAL PERMISSIONS & TOOL GATEWAY RULES\n\n"
            . "- Write operations targeting Stage 10+ strictly require a verified Gate 4 cryptographic token.\n"
            . "- Dangerous shell commands (rm -rf, drop table, force push) are strictly blocked under Invariant 5.\n"
            . "- All file operations are confined within the host project workspace boundary.\n";

        return implode("\n\n" . str_repeat('=', 80) . "\n\n", $sections);
    }

    /**
     * Builds Section 2.5: Active Project Profile.
     *
     * @param array<string, mixed> $profile
     */
    private function buildSection25Profile(array $profile): string
    {
        $tech = (array)($profile['technologies'] ?? []);
        $projectName = htmlspecialchars((string)($profile['project_name'] ?? 'Generic PHP Application'), ENT_QUOTES);
        $backend = htmlspecialchars((string)($tech['backend_framework'] ?? 'generic_php'), ENT_QUOTES);
        $phpVer = htmlspecialchars((string)($tech['php_version'] ?? '8.2+'), ENT_QUOTES);
        $db = htmlspecialchars((string)($tech['database_engine'] ?? 'unknown'), ENT_QUOTES);
        $orm = htmlspecialchars((string)($tech['orm'] ?? 'unknown'), ENT_QUOTES);
        $frontend = htmlspecialchars((string)($tech['frontend_framework'] ?? 'standard_web'), ENT_QUOTES);
        $testing = htmlspecialchars((string)($tech['testing_framework'] ?? 'phpunit'), ENT_QUOTES);
        $arch = htmlspecialchars((string)($tech['architecture_pattern'] ?? 'mvc'), ENT_QUOTES);

        return "# SECTION 2.5: ACTIVE PROJECT TECHNOLOGY PROFILE\n\n"
            . "```yaml\n"
            . "project_name: \"{$projectName}\"\n"
            . "backend_framework: \"{$backend}\"\n"
            . "php_version: \"{$phpVer}\"\n"
            . "database_engine: \"{$db}\"\n"
            . "orm: \"{$orm}\"\n"
            . "frontend_framework: \"{$frontend}\"\n"
            . "testing_framework: \"{$testing}\"\n"
            . "architecture_pattern: \"{$arch}\"\n"
            . "```\n\n"
            . "> **Adherence Directive:** You MUST adapt all code, design patterns, queries, and file paths "
            . "strictly to the active project technology profile declared above. Do NOT assume Universal Core conventions.";
    }
}
