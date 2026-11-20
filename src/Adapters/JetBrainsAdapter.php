<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\HostCapabilityMap;

/**
 * JetBrains IDEs integration adapter providing external tool configurations and prompt templates.
 */
class JetBrainsAdapter extends AbstractAdapter
{
    public function getAdapterId(): string
    {
        return 'jetbrains';
    }

    public function getName(): string
    {
        return 'JetBrains IDEs Adapter v2.0';
    }

    public function getCapabilities(): HostCapabilityMap
    {
        return new HostCapabilityMap(
            supportsNativeSubagents: false,
            supportsMcpTools: false,
            supportsToolInterception: false,
            supportsReadOnlyScoping: false,
            supportsInteractivePrompts: true,
            supportsMultiTurnStreaming: false,
            primaryEnforcementCategory: HostCapabilityMap::ENFORCEMENT_HOST,
            supportedToolOperations: ['external_tool_launcher', 'prompt_library'],
            unsupportedOperations: ['native_subagents', 'background_daemon_interception'],
            knownBypassPaths: [
                'direct_editor_manipulation' => 'User or AI Assistant in editor executes outside UAOS unless using external tool.',
            ]
        );
    }

    public function exportAgents(string $outputDirectory): int
    {
        $toolDir = $outputDirectory . DIRECTORY_SEPARATOR . '.idea';
        if (!is_dir($toolDir)) {
            mkdir($toolDir, 0755, true);
        }

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n"
            . "<toolSet name=\"UAOS\">\n"
            . "  <tool name=\"UAOS Task Runner\" showInMainMenu=\"true\" showInEditor=\"true\">\n"
            . "    <exec>\n"
            . "      <option name=\"COMMAND\" value=\"vendor/bin/agent-os\" />\n"
            . "      <option name=\"PARAMETERS\" value=\"run --task=&quot;\$SelectedText\$&quot;\" />\n"
            . "      <option name=\"WORKING_DIRECTORY\" value=\"\$ProjectFileDir\$\" />\n"
            . "    </exec>\n"
            . "  </tool>\n"
            . "</toolSet>\n";

        file_put_contents($toolDir . DIRECTORY_SEPARATOR . 'uaos-external-tools.xml', $xml);
        return 1;
    }
}
