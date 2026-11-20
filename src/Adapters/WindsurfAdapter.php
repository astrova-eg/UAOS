<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * Windsurf Cascade rules compiler and MCP connector adapter.
 */
class WindsurfAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'windsurf';
    }

    public function getName(): string
    {
        return 'Windsurf Cascade Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: false,
            supportsMcpTools: true,
            supportsToolInterception: false,
            supportsReadOnlyScoping: false,
            supportsInteractivePrompts: true,
            supportsMultiTurnStreaming: true,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_RUNTIME,
            supportedToolOperations: ['mcp_tool_call', 'cascade_rules'],
            unsupportedOperations: ['native_cascade_interception'],
            knownBypassPaths: [
                'cascade_direct_write' => 'Cascade model may modify files natively unless confined to MCP.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $rulesFile = $outputDirectory . DIRECTORY_SEPARATOR . '.windsurfrules';
        $content = "# Universal Agent Operating System Cascade Directives\n"
            . "- Respect project profile in .agent-os/project-profile.json\n"
            . "- File writes to Stage 10+ code require verified Gate 4 clearance tokens\n"
            . "- Invariant 5 blocks destructive shell execution\n";

        file_put_contents($rulesFile, $content);
        return 1;
    }
}
