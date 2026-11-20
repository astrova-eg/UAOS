<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Adapters;

use UniversalAgentOS\Adapters\AdapterRegistry;
use UniversalAgentOS\Contracts\HostCapabilityMap;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Tests\Assert;

class AdapterContractTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $registry = new AdapterRegistry($resolver);

        $adapters = $registry->all();
        Assert::assertTrue(count($adapters) >= 7, "All 7 platform adapters registered");

        // 1. Antigravity Adapter
        $ag = $registry->get('antigravity');
        $agCaps = $ag->getCapabilities();
        Assert::assertTrue($agCaps->supportsNativeSubagents, "Antigravity supports native subagents");
        Assert::assertEquals(HostCapabilityMap::ENFORCEMENT_ADAPTER, $agCaps->primaryEnforcementCategory, "Antigravity is Adapter-enforced");

        // 2. Cursor Adapter
        $cur = $registry->get('cursor');
        $curCaps = $cur->getCapabilities();
        Assert::assertTrue($curCaps->supportsMcpTools, "Cursor supports MCP tools");
        Assert::assertEquals(HostCapabilityMap::ENFORCEMENT_RUNTIME, $curCaps->primaryEnforcementCategory, "Cursor MCP is Runtime-enforced");

        // 3. Windsurf Adapter
        $win = $registry->get('windsurf');
        $winCaps = $win->getCapabilities();
        Assert::assertTrue($winCaps->supportsMcpTools, "Windsurf supports MCP tools");

        // 4. VS Code Adapter
        $vsc = $registry->get('vscode');
        $vscCaps = $vsc->getCapabilities();
        Assert::assertTrue($vscCaps->supportsReadOnlyScoping, "VS Code supports read-only scoping");

        // 5. JetBrains Adapter
        $jet = $registry->get('jetbrains');
        $jetCaps = $jet->getCapabilities();
        Assert::assertEquals(HostCapabilityMap::ENFORCEMENT_HOST, $jetCaps->primaryEnforcementCategory, "JetBrains is Host-enforced");

        // 6. CLI Adapter
        $cli = $registry->get('cli');
        $cliCaps = $cli->getCapabilities();
        Assert::assertEquals(HostCapabilityMap::ENFORCEMENT_RUNTIME, $cliCaps->primaryEnforcementCategory, "CLI is Runtime-enforced");

        // Export test
        $tmpExportDir = sys_get_temp_dir() . '/uaos_test_export_' . bin2hex(random_bytes(4));
        mkdir($tmpExportDir, 0755, true);
        try {
            Assert::assertTrue($ag->exportAgents($tmpExportDir) > 0, "Antigravity exported agent manifest");
            Assert::assertTrue($cur->exportAgents($tmpExportDir) > 0, "Cursor exported .cursor/rules");
            Assert::assertTrue($win->exportAgents($tmpExportDir) > 0, "Windsurf exported .windsurfrules");
            Assert::assertTrue($vsc->exportAgents($tmpExportDir) > 0, "VS Code exported .roomodes");
            Assert::assertTrue($jet->exportAgents($tmpExportDir) > 0, "JetBrains exported .idea tools");
        } finally {
            // Cleanup
            @unlink($tmpExportDir . '/antigravity-agents.json');
            @unlink($tmpExportDir . '/.windsurfrules');
            @unlink($tmpExportDir . '/.roomodes');
            @unlink($tmpExportDir . '/.clinerules');
            @unlink($tmpExportDir . '/.cursor/rules/uaos-governance.mdc');
            @rmdir($tmpExportDir . '/.cursor/rules');
            @rmdir($tmpExportDir . '/.cursor');
            @unlink($tmpExportDir . '/.idea/uaos-external-tools.xml');
            @rmdir($tmpExportDir . '/.idea');
            @rmdir($tmpExportDir);
        }
    }
}
