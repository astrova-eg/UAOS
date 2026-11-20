<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\SkillEngine;
use UniversalAgentOS\Tests\Assert;

class SkillEngineTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $engine = new SkillEngine($resolver);

        $available = $engine->listAvailableSkills();
        Assert::assertTrue(count($available) >= 20, "Discovered at least 20 shared engineering skills");

        // Stack-aware filtering: filament vs vue-inertia
        $profileFilament = [
            'technologies' => [
                'frontend_framework' => 'filament',
                'database_engine' => 'mysql',
            ],
        ];

        $res1 = $engine->resolveSkills('ARCH-001', 'Build dashboard UI', $profileFilament);
        Assert::assertTrue(isset($res1['audit']['vue-inertia-standards']), "Audit tracked vue-inertia skill");
        Assert::assertEquals('EXCLUDED', $res1['audit']['vue-inertia-standards']['decision'], "Vue skill excluded for Filament project");

        $profileVue = [
            'technologies' => [
                'frontend_framework' => 'vue_inertia',
                'database_engine' => 'mysql',
            ],
        ];

        $res2 = $engine->resolveSkills('ARCH-001', 'Build dashboard UI', $profileVue);
        Assert::assertTrue(isset($res2['audit']['filament-livewire-standards']), "Audit tracked filament skill");
        Assert::assertEquals('EXCLUDED', $res2['audit']['filament-livewire-standards']['decision'], "Filament skill excluded for Vue project");
    }
}
