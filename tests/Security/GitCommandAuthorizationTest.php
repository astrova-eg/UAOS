<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Exceptions\AgentOSException;
use UniversalAgentOS\Exceptions\GovernanceViolationException;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\KeyManager;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Security\ToolGateway;
use UniversalAgentOS\Tests\Assert;

class GitCommandAuthorizationTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);
        $nonceStore = new PersistentNonceStore($resolver);
        $verifier = new Gate4Verifier($resolver, $nonceStore, $validator);
        $keyManager = new KeyManager($resolver);
        $gateway = new ToolGateway($resolver, $validator, $verifier, null, $keyManager);

        $secret = $keyManager->getKey();
        $projectId = basename($resolver->getProjectRootDir());
        $rootHash = hash('sha256', $validator->normalizePath($resolver->getProjectRootDir()));

        // Helper to generate valid Gate 4 clearance token for shell commands
        $issueValidToken = function (string $exe) use ($verifier, $secret, $projectId, $rootHash): string {
            $payload = [
                'project_id' => $projectId,
                'project_root_hash' => $rootHash,
                'task_id' => 'COMMAND-' . strtoupper($exe),
                'stage' => 4,
                'cleared_stage' => 10,
                'timestamp' => time(),
                'expires_at' => time() + 3600,
                'nonce' => bin2hex(random_bytes(16)),
                'authorizer' => ['identity' => 'security-admin', 'auth_method' => 'human_hmac'],
                'prerequisites' => [],
            ];
            return $verifier->issueToken($payload, $secret);
        };

        // 1. git push without authorization
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin main');
        }, "1. git push without authorization blocked fail-closed");

        // 2. git push with invalid authorization
        Assert::assertThrows(AgentOSException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin main', null, '{"tampered":"token"}');
        }, "2. git push with invalid token rejected fail-closed");

        // 3. git push with valid authorization
        $validToken = $issueValidToken('git');
        $pushResult = $gateway->executeShell('git push origin main', null, $validToken);
        Assert::assertEquals('git push origin main', $pushResult['command'], "3. git push with valid Gate 4 token accepted for execution");

        // 4. git push --force
        Assert::assertTrue($gateway->isDangerousCommand('git push origin main --force'), "4a. git push --force detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin main --force');
        }, "4b. git push --force execution rejected");

        // 5. git push --force-with-lease
        Assert::assertTrue($gateway->isDangerousCommand('git push origin main --force-with-lease'), "5a. git push --force-with-lease detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin main --force-with-lease');
        }, "5b. git push --force-with-lease execution rejected");

        // 6. git push --delete
        Assert::assertTrue($gateway->isDangerousCommand('git push origin --delete feature-branch'), "6a. git push --delete detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin --delete feature-branch');
        }, "6b. git push --delete execution rejected");

        // 7. git push --tags requires Gate 4 clearance
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push --tags');
        }, "7. git push --tags without Gate 4 authorization rejected fail-closed");

        // 8. Destructive refspecs (+ref and :ref)
        Assert::assertTrue($gateway->isDangerousCommand('git push origin +refs/heads/main'), "8a. Force refspec +refs/heads/main detected as dangerous");
        Assert::assertTrue($gateway->isDangerousCommand('git push origin :remote-branch'), "8b. Deletion refspec :remote-branch detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin :feature');
        }, "8c. Deletion refspec execution rejected");

        // 9. git reset --hard
        Assert::assertTrue($gateway->isDangerousCommand('git reset --hard HEAD~1'), "9a. git reset --hard detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git reset --hard HEAD~1');
        }, "9b. git reset --hard execution rejected");

        // 10. git clean -fd
        Assert::assertTrue($gateway->isDangerousCommand('git clean -fd'), "10a. git clean -fd detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git clean -fd');
        }, "10b. git clean -fd execution rejected");

        // 11. git branch -D
        Assert::assertTrue($gateway->isDangerousCommand('git branch -D feature-to-kill'), "11a. git branch -D detected as dangerous");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git branch -D feature-to-kill');
        }, "11b. git branch -D execution rejected");

        // 12. Unauthorized git rebase, git merge, git reset
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git rebase origin/main');
        }, "12a. Unauthorized git rebase rejected");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git merge feature');
        }, "12b. Unauthorized git merge rejected");
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git remote set-url origin https://evil.com/repo.git');
        }, "12c. Unauthorized git remote set-url rejected");

        // 13. Read-only Git commands without authorization succeed
        $statusResult = $gateway->executeShell('git status --short');
        Assert::assertEquals('git status --short', $statusResult['command'], "13a. git status executes without Gate 4 requirement");
        $logResult = $gateway->executeShell('git log -n 1 --oneline');
        Assert::assertEquals('git log -n 1 --oneline', $logResult['command'], "13b. git log executes without Gate 4 requirement");

        // 14. Quoted arguments containing spaces, empty args, and escaped quotes
        $tokens = $gateway->tokenizeCommand('git commit -m "feat: initial commit with spaces" --author="Test <test@example.com>"');
        Assert::assertEquals(5, count($tokens), "14a. Tokenizer split command into 5 tokens");
        Assert::assertEquals('feat: initial commit with spaces', $tokens[3], "14b. Quoted argument with spaces preserved intact");
        Assert::assertEquals('--author=Test <test@example.com>', $tokens[4], "14c. Quoted parameter flag preserved intact");

        $emptyArgTokens = $gateway->tokenizeCommand('php script.php ""');
        Assert::assertEquals(3, count($emptyArgTokens), "14d. Empty quoted argument yielded empty token");
        Assert::assertEquals('', $emptyArgTokens[2], "14e. Empty string token preserved");

        $escapedQuoteTokens = $gateway->tokenizeCommand('git commit -m "feat: \\"quoted\\" message"');
        Assert::assertEquals('feat: "quoted" message', $escapedQuoteTokens[3], "14f. Escaped quote correctly parsed inside quotes");

        // 15. Shell chaining and argument injection attempts
        Assert::assertTrue($gateway->isDangerousCommand('git status ; del /f *'), "15a. Command chaining with semicolon blocked");
        Assert::assertTrue($gateway->isDangerousCommand('git status && rm -rf /'), "15b. Command chaining with && blocked");
        Assert::assertTrue($gateway->isDangerousCommand('git status | iex'), "15c. Pipeline injection | iex blocked");
        Assert::assertTrue($gateway->isDangerousCommand('git commit -m "$(calc.exe)"'), "15d. Subshell command substitution $(...) blocked");
        Assert::assertTrue($gateway->isDangerousCommand('git commit -m "`calc.exe`"'), "15e. Backtick command substitution blocked");

        // 16. Confirmation that denied operations never reach process execution
        // Verified by observing that rejected commands throw GovernanceViolationException before process spawning
        Assert::assertThrows(GovernanceViolationException::class, function () use ($gateway) {
            $gateway->executeShell('git push origin +master');
        }, "16. Denied destructive operations throw before process spawning");
    }
}
