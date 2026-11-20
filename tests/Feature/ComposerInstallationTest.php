<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Feature;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\ProjectStorageManager;
use UniversalAgentOS\Tests\Assert;

class ComposerInstallationTest
{
    public static function run(): void
    {
        $composerJsonFile = __DIR__ . '/../../composer.json';
        Assert::assertTrue(file_exists($composerJsonFile), "Root composer.json exists");

        $data = json_decode((string)file_get_contents($composerJsonFile), true);
        Assert::assertEquals('universal-agent-os/agent-os', $data['name'] ?? '', "Package identity is universal-agent-os/agent-os");
        Assert::assertTrue(isset($data['autoload']['psr-4']['UniversalAgentOS\\']), "Declares PSR-4 UniversalAgentOS\\ namespace mapping");
        Assert::assertTrue(in_array('bin/agent-os', (array)($data['bin'] ?? []), true), "Maps bin/agent-os executable in composer.json");

        $resolver = new PathResolver();
        Assert::assertTrue(is_dir($resolver->getPackageResourcesDir()), "Package resources directory resolved cleanly");

        $storageManager = new ProjectStorageManager($resolver);
        Assert::assertTrue($storageManager->isInitialized(), "Host workspace .agent-os/ is initialized and verified");

        // Assert vendor resources are read-only
        $vendorDir = $resolver->getPackageResourcesDir();
        Assert::assertFalse(file_exists($vendorDir . '/mutable-state.json'), "No mutable state found in immutable package resources");
    }
}
