<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Exceptions\NonceReplayException;
use UniversalAgentOS\Tests\Assert;

class Gate4ReplayTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);
        $nonceStore = new PersistentNonceStore($resolver);
        $verifier = new Gate4Verifier($resolver, $nonceStore, $validator);

        $secret = 'test_secret_for_replay_checks_123';
        $taskId = 'TASK-REPLAY-001';
        $projectId = basename($resolver->getProjectRootDir());
        $rootHash = hash('sha256', $validator->normalizePath($resolver->getProjectRootDir()));

        $tokenPayload = [
            'project_id' => $projectId,
            'project_root_hash' => $rootHash,
            'task_id' => $taskId,
            'stage' => 4,
            'cleared_stage' => 10,
            'timestamp' => time(),
            'expires_at' => time() + 3600,
            'nonce' => bin2hex(random_bytes(16)),
            'authorizer' => ['identity' => 'reviewer'],
        ];

        $signedToken = $verifier->issueToken($tokenPayload, $secret);
        $request = new Gate4Request(
            projectId: $projectId,
            projectRoot: $resolver->getProjectRootDir(),
            taskId: $taskId
        );

        // First verification succeeds
        $resp1 = $verifier->verifyToken($signedToken, $request, $secret);
        Assert::assertTrue($resp1->cleared, "Initial Gate 4 clearance succeeded");

        // Discrete replay verification attempt fails
        Assert::assertThrows(NonceReplayException::class, function () use ($verifier, $signedToken, $request, $secret) {
            $verifier->verifyToken($signedToken, $request, $secret);
        }, "Replaying consumed Gate 4 token throws NonceReplayException");
    }
}
