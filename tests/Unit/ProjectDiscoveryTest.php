<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Discovery\ProjectDiscoveryEngine;
use UniversalAgentOS\Tests\Assert;

class ProjectDiscoveryTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $engine = new ProjectDiscoveryEngine($resolver);

        $facts = $engine->discover($resolver->getProjectRootDir());
        Assert::assertTrue(isset($facts['backend_framework']), "Discovered backend_framework fact");
        Assert::assertTrue(isset($facts['architecture']), "Discovered architecture fact");
        Assert::assertTrue(isset($facts['database']), "Discovered database fact");

        $profile = $engine->generateProfile($resolver->getProjectRootDir(), [
            'frontend_framework' => 'filament',
        ]);

        Assert::assertEquals('2.0.0', $profile['schema_version'], "Profile generated with schema 2.0.0");
        Assert::assertEquals('filament', $profile['technologies']['frontend_framework'], "Manual override applied cleanly");
        Assert::assertEquals('manual_override', $profile['provenance']['frontend_framework']['status'], "Provenance marks manual override");

        $errors = $engine->validateProfile($profile);
        Assert::assertCount(0, $errors, "Generated profile validates with 0 errors");
    }
}
