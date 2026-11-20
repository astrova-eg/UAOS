<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * Cline rules compiler and integration adapter.
 */
class ClineAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'cline';
    }

    public function getName(): string
    {
        return 'Cline AI Adapter v2.0';
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
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_ADVISORY,
            supportedToolOperations: ['clinerules_generation', 'mcp_integration'],
            unsupportedOperations: ['native_file_interception'],
            knownBypassPaths: [
                'direct_file_write' => 'Cline executes tool operations directly unless constrained to MCP server.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $rulesFile = $outputDirectory . DIRECTORY_SEPARATOR . '.clinerules';
        $content = "# Universal Agent Operating System Cline Instructions\n"
            . "- Honor Section 2.5 Active Project Profile for stack choices.\n"
            . "- Stage 10+ code write operations strictly require verified Gate 4 clearance tokens.\n"
            . "- Invariant 5 forbids rm -rf, drop table, and git push --force.\n";

        file_put_contents($rulesFile, $content);
        return 1;
    }
}
