<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Exceptions\Gate4VerificationException;
use UniversalAgentOS\Tests\Assert;

class Gate4VerifierTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);
        $nonceStore = new PersistentNonceStore($resolver);
        $verifier = new Gate4Verifier($resolver, $nonceStore, $validator);

        $secret = 'test_secret_key_32_bytes_long_123';
        $taskId = 'TASK-G4-TEST-001';
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
            'authorizer' => ['identity' => 'test-admin', 'auth_method' => 'os_credential'],
            'prerequisites' => [],
        ];

        $signedToken = $verifier->issueToken($tokenPayload, $secret);
        Assert::assertStringContains('hmac_sha256', $signedToken, "Token contains calculated hmac_sha256 signature");

        $request = new Gate4Request(
            projectId: $projectId,
            projectRoot: $resolver->getProjectRootDir(),
            taskId: $taskId
        );

        $response = $verifier->verifyToken($signedToken, $request, $secret);
        Assert::assertTrue($response->cleared, "Valid Gate 4 token clearance granted");
        Assert::assertEquals('CLEARANCE_GRANTED', $response->decision, "Decision code is CLEARANCE_GRANTED");
        Assert::assertEquals('test-admin', $response->authorizer, "Authorizer identity is preserved");

        // Tampered token signature fails
        $tampered = str_replace('test-admin', 'malicious-user', $signedToken);
        Assert::assertThrows(Gate4VerificationException::class, function () use ($verifier, $tampered, $request, $secret) {
            $verifier->verifyToken($tampered, $request, $secret);
        }, "Tampered token payload fails signature verification");

        // Expired token fails
        $expiredPayload = $tokenPayload;
        $expiredPayload['nonce'] = bin2hex(random_bytes(16));
        $expiredPayload['expires_at'] = time() - 300;
        $expiredToken = $verifier->issueToken($expiredPayload, $secret);
        Assert::assertThrows(Gate4VerificationException::class, function () use ($verifier, $expiredToken, $request, $secret) {
            $verifier->verifyToken($expiredToken, $request, $secret);
        }, "Expired Gate 4 token rejected");

        // Project ID mismatch fails
        $wrongProjPayload = $tokenPayload;
        $wrongProjPayload['nonce'] = bin2hex(random_bytes(16));
        $wrongProjPayload['project_id'] = 'unauthorized-project';
        $wrongProjToken = $verifier->issueToken($wrongProjPayload, $secret);
        Assert::assertThrows(Gate4VerificationException::class, function () use ($verifier, $wrongProjToken, $request, $secret) {
            $verifier->verifyToken($wrongProjToken, $request, $secret);
        }, "Project ID mismatch rejected");

        // Task ID mismatch fails
        $wrongTaskPayload = $tokenPayload;
        $wrongTaskPayload['nonce'] = bin2hex(random_bytes(16));
        $wrongTaskPayload['task_id'] = 'DIFFERENT-TASK-999';
        $wrongTaskToken = $verifier->issueToken($wrongTaskPayload, $secret);
        Assert::assertThrows(Gate4VerificationException::class, function () use ($verifier, $wrongTaskToken, $request, $secret) {
            $verifier->verifyToken($wrongTaskToken, $request, $secret);
        }, "Task ID mismatch rejected");

        // Revoked nonce fails
        $revokedNonce = bin2hex(random_bytes(16));
        $revokedPayload = $tokenPayload;
        $revokedPayload['nonce'] = $revokedNonce;
        $revokedToken = $verifier->issueToken($revokedPayload, $secret);
        $revocationsFile = $resolver->getProjectStateDir() . '/revocations.json';
        file_put_contents($revocationsFile, json_encode([$revokedNonce]));
        try {
            Assert::assertThrows(Gate4VerificationException::class, function () use ($verifier, $revokedToken, $request, $secret) {
                $verifier->verifyToken($revokedToken, $request, $secret);
            }, "Revoked Gate 4 nonce rejected");
        } finally {
            @unlink($revocationsFile);
        }
    }
}
