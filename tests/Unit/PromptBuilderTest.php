<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\PromptBuilder;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Tests\Assert;

class PromptBuilderTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $builder = new PromptBuilder($resolver);

        $task = new TaskEnvelope('TASK-PB-001', 'Build authentic architecture model', 'ARCH-001');
        $profile = [
            'project_name' => 'DemoApp',
            'technologies' => [
                'backend_framework' => 'laravel',
                'database_engine' => 'postgresql',
                'frontend_framework' => 'filament',
            ],
        ];

        $skills = [
            'secure-coding' => "Rule 1: Always sanitize input.\n",
        ];

        $prompt = $builder->buildPrompt('Spec ARCH-001', 'Charter Leadership', $task, $profile, $skills);

        Assert::assertStringContains('SECTION 1: SHARED AGENT GOVERNANCE CORE', $prompt, "Contains Section 1");
        Assert::assertStringContains('SECTION 2.5: ACTIVE PROJECT TECHNOLOGY PROFILE', $prompt, "Contains Section 2.5 Profile");
        Assert::assertStringContains('database_engine: "postgresql"', $prompt, "Profile injected postgresql correctly");
        Assert::assertStringContains('frontend_framework: "filament"', $prompt, "Profile injected filament correctly");
        Assert::assertStringContains('SECTION 6: RELEVANT ENGINEERING SKILLS', $prompt, "Contains Section 6 Skills");
        Assert::assertStringContains('Rule 1: Always sanitize input.', $prompt, "Injected skill content");
    }
}
