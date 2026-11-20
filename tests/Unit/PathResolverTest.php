<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Tests\Assert;

class PathResolverTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();

        Assert::assertTrue(is_dir($resolver->getPackageResourcesDir()), "Package resources dir resolves to real directory");
        Assert::assertTrue(file_exists($resolver->getPackageResourcesDir() . '/registry/agent-registry.json'), "Agent registry located in resources");

        Assert::assertTrue(is_dir($resolver->getProjectRootDir()), "Project root dir resolves to real directory");
        Assert::assertTrue(str_contains($resolver->getProjectStorageDir(), '.agent-os'), "Storage dir is under .agent-os");

        $resolvedProj = $resolver->resolveProjectPath('composer.json');
        Assert::assertTrue(file_exists($resolvedProj), "Resolves project root file composer.json");

        $resolvedResource = $resolver->resolveResourcePath('governance/SHARED-AGENT-GOVERNANCE-CORE.md');
        Assert::assertTrue(file_exists($resolvedResource), "Resolves package resource file correctly");
    }
}
