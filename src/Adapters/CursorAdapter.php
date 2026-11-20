<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * Cursor rules compiler and MCP bridge adapter.
 */
class CursorAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'cursor';
    }

    public function getName(): string
    {
        return 'Cursor IDE Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: false,
            supportsMcpTools: true,
            supportsToolInterception: false,
            supportsReadOnlyScoping: false,
            supportsInteractivePrompts: false,
            supportsMultiTurnStreaming: true,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_RUNTIME,
            supportedToolOperations: ['mcp_tool_call', 'cursor_rules_injection'],
            unsupportedOperations: ['native_terminal_interception', 'subagent_hierarchy'],
            knownBypassPaths: [
                'native_composer_tools' => 'Model may invoke native editor tools bypassing rules if MCP is not set exclusive.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        $rulesDir = $outputDirectory . DIRECTORY_SEPARATOR . '.cursor' . DIRECTORY_SEPARATOR . 'rules';
        if (!is_dir($rulesDir)) {
            mkdir($rulesDir, 0755, true);
        }

        $ruleContent = "---\n"
            . "description: Universal Agent Operating System Invariants and Governance\n"
            . "globs: *.*\n"
            . "---\n\n"
            . "# UAOS Governance Rules\n"
            . "- Invariant 1: Sovereign Human Authority.\n"
            . "- Invariant 5: Dangerous command blocking (rm -rf, DROP TABLE, force push).\n"
            . "- All Stage 10+ implementations require Gate 4 HMAC authorization.\n";

        file_put_contents($rulesDir . DIRECTORY_SEPARATOR . 'uaos-governance.mdc', $ruleContent);
        return 1;
    }
}
