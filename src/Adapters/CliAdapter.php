<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * Standalone CLI and headless terminal agent adapter.
 */
class CliAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'cli';
    }

    public function getName(): string
    {
        return 'Standalone CLI Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: false,
            supportsMcpTools: true,
            supportsToolInterception: true,
            supportsReadOnlyScoping: true,
            supportsInteractivePrompts: true,
            supportsMultiTurnStreaming: false,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_RUNTIME,
            supportedToolOperations: ['read_file', 'write_file', 'execute_shell', 'cli_dispatch'],
            unsupportedOperations: ['interactive_gui_modals'],
            knownBypassPaths: []
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        return 0;
    }
}
