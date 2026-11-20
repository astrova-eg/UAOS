<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class PromptBuilder
{
    private string $agentsDir;
    private int $maxCharacterBudget;

    public function __construct(?string $agentsDir = null, int $maxCharacterBudget = 120000)
    {
        $this->agentsDir = $agentsDir ?? dirname(__DIR__, 2);
        $this->maxCharacterBudget = $maxCharacterBudget;
    }

    /**
     * @param array{
     *     agent: array<string, mixed>,
     *     task_title: string,
     *     task_scope: string,
     *     stage: int,
     *     skills: array<string, array{meta: array<string, mixed>, content: string}>,
     *     state?: array<string, mixed>
     * } $context
     * @return array{prompt: string, character_count: int, truncated: bool}
     */
    public function build(array $context): array
    {
        $agent = $context['agent'];
        $skills = $context['skills'];
        $sections = [];

        // 1. Immutable Shared Governance Core (Highest Priority)
        $coreFile = $this->agentsDir . '/governance/SHARED-AGENT-GOVERNANCE-CORE.md';
        $coreContent = file_exists($coreFile) ? (string)file_get_contents($coreFile) : '';
        $sections['governance_core'] = "=== SECTION 1: IMMUTABLE SHARED GOVERNANCE CORE ===\n" . trim($coreContent);

        // 2. Constitutional & Policy Requirements
        $constFile = $this->agentsDir . '/governance/ENGINEERING-CONSTITUTION.md';
        $constContent = file_exists($constFile) ? (string)file_get_contents($constFile) : '';
        $sections['constitution'] = "=== SECTION 2: CONSTITUTIONAL LAW & DOCUMENT PRECEDENCE ===\n" . trim($constContent);

        // 3. Agent Identity & Specialization
        $agentId = (string)($agent['id'] ?? 'UNKNOWN');
        $agentName = (string)($agent['name'] ?? 'Generic Agent');
        $role = (string)($agent['role'] ?? 'Specialist');
        $specialization = (string)($agent['specialization'] ?? '');
        $sections['agent_identity'] = "=== SECTION 3: AGENT IDENTITY & ROLE ===\n" .
            "Agent ID: {$agentId}\nOfficial Name: {$agentName}\nRole: {$role}\nSpecialization: {$specialization}";

        // 4. Department Charter
        $deptFolder = (string)($agent['department_folder'] ?? '');
        $deptName = (string)($agent['department_name'] ?? '');
        $sections['department'] = "=== SECTION 4: DEPARTMENT CHARTER ===\n" .
            "Department: {$deptName}\nFolder: {$deptFolder}";

        // 5. Task Scope & Acceptance Criteria
        $stage = (int)$context['stage'];
        $taskTitle = (string)$context['task_title'];
        $taskScope = (string)$context['task_scope'];
        $sections['task'] = "=== SECTION 5: TASK SCOPE & LIFECYCLE STAGE ===\n" .
            "Current Lifecycle Stage: {$stage}\nTask Title: {$taskTitle}\nTask Scope:\n{$taskScope}";

        // 6. Applicable Skills Content
        $skillBlocks = [];
        foreach ($skills as $skillId => $skillData) {
            $meta = $skillData['meta'];
            $name = (string)($meta['name'] ?? $skillId);
            $skillBlocks[] = "--- SKILL: {$skillId} ({$name}) ---\n" . trim((string)$skillData['content']);
        }
        $sections['skills'] = "=== SECTION 6: INJECTED OPERATIONAL SKILLS ===\n" . implode("\n\n", $skillBlocks);

        // 7. Authorized Tools & Permissions
        $permissions = (array)($agent['permissions'] ?? ['read_file']);
        $sections['permissions'] = "=== SECTION 7: AUTHORIZED PERMISSIONS & TOOLS ===\n" .
            "Granted Permissions: " . implode(', ', $permissions) . "\n" .
            "Mandatory Restriction: All tool operations must be routed via ToolExecutionGateway.";

        // Assemble with Budget Strategy
        $mandatoryParts = [
            $sections['governance_core'],
            $sections['constitution'],
            $sections['agent_identity'],
            $sections['permissions'],
            $sections['task']
        ];
        $mandatoryText = implode("\n\n", $mandatoryParts);
        $mandatoryLen = mb_strlen($mandatoryText);

        if ($mandatoryLen > $this->maxCharacterBudget) {
            throw new \RuntimeException("Mandatory governance context ({$mandatoryLen} chars) exceeds character budget ({$this->maxCharacterBudget}).");
        }

        $remainingBudget = $this->maxCharacterBudget - $mandatoryLen;
        $optionalParts = [$sections['department'], $sections['skills']];
        $optionalText = implode("\n\n", $optionalParts);
        $truncated = false;

        if (mb_strlen($optionalText) > $remainingBudget) {
            $optionalText = mb_substr($optionalText, 0, $remainingBudget - 100) . "\n[... SKILL CONTEXT COMPACTED DUE TO BUDGET ...]";
            $truncated = true;
        }

        $finalPrompt = $mandatoryText . "\n\n" . $optionalText;

        return [
            'prompt' => $finalPrompt,
            'character_count' => mb_strlen($finalPrompt),
            'truncated' => $truncated
        ];
    }
}
