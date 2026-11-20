<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * VS Code and Roo Code mode configuration generator and MCP adapter.
 */
class VsCodeAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'vscode';
    }

    public function getName(): string
    {
        return 'VS Code & Roo Code Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: false,
            supportsMcpTools: true,
            supportsToolInterception: true,
            supportsReadOnlyScoping: true,
            supportsInteractivePrompts: true,
            supportsMultiTurnStreaming: true,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_RUNTIME,
            supportedToolOperations: ['roomodes_generation', 'mcp_tool_gateway'],
            unsupportedOperations: ['native_vs_code_terminal_interception'],
            knownBypassPaths: [
                'custom_mode_terminal_permission' => 'If user grants terminal write mode in Roo Code settings.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $modes = [
            'customModes' => [
                [
                    'slug' => 'uaos-architect',
                    'name' => 'UAOS Architecture Agent',
                    'roleDefinition' => 'System Architect bound to UAOS Governance and Project Profile.',
                    'groups' => ['read', 'mcp'],
                ],
                [
                    'slug' => 'uaos-auditor',
                    'name' => 'UAOS Independent Auditor',
                    'roleDefinition' => 'Department M Independent Auditor verifying compliance and code quality.',
                    'groups' => ['read'],
                ],
            ],
        ];

        $modesFile = $outputDirectory . DIRECTORY_SEPARATOR . '.roomodes';
        file_put_contents($modesFile, (string)json_encode($modes, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        return 1;
    }
}
