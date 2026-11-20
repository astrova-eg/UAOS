<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\ToolGateway;
use UniversalAgentOS\Exceptions\GovernanceViolationException;
use UniversalAgentOS\Tests\Assert;

class ToolGatewayTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);
        $gateway = new ToolGateway($resolver, $validator);

        // 1. Reading existing file
        $content = $gateway->readFile('composer.json');
        Assert::assertStringContains('universal-agent-os', $content, "ToolGateway reads valid project file");

        // 2. Dangerous commands blocked under Invariant 5
        Assert::assertTrue($gateway->isDangerousCommand('rm -rf /'), "rm -rf detected as dangerous");
        Assert::assertTrue($gateway->isDangerousCommand('DROP TABLE users;'), "DROP TABLE detected as dangerous");
        Assert::assertTrue($gateway->isDangerousCommand('git push origin main --force'), "git push --force detected");

        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('rm -rf /var/www');
        }, "Executing dangerous command throws GovernanceViolationException");

        // 3. Safe command executes
        $safeResult = $gateway->executeShell('php -v');
        Assert::assertEquals(0, $safeResult['exit_code'], "Safe command exits with code 0");
        Assert::assertStringContains('PHP', $safeResult['stdout'], "Safe command stdout contains PHP banner");
    }
}
