<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * Google Antigravity bridge & subagent spawner adapter.
 */
class AntigravityAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'antigravity';
    }

    public function getName(): string
    {
        return 'Google Antigravity Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: true,
            supportsMcpTools: true,
            supportsToolInterception: false,
            supportsReadOnlyScoping: true,
            supportsInteractivePrompts: true,
            supportsMultiTurnStreaming: true,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_ADAPTER,
            supportedToolOperations: ['read_file', 'write_file', 'execute_shell', 'invoke_subagent'],
            unsupportedOperations: ['main_agent_kernel_interception'],
            knownBypassPaths: [
                'main_agent_direct_tools' => 'Parent conversation retains native tools; scoping applies to spawned subagents.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        if (!is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        $manifest = [
            'name' => 'uaos-antigravity-manifest',
            'version' => '2.0.0',
            'agents_roster_count' => 112,
            'departments_count' => 14,
            'subagent_defaults' => [
                'EnableWriteTools' => false,
                'Model' => 'inherit',
            ],
        ];

        $manifestFile = $outputDirectory . DIRECTORY_SEPARATOR . 'antigravity-agents.json';
        file_put_contents($manifestFile, (string)json_encode($manifest, JSON_PRETTY_PRINT));
        return 1;
    }
}
