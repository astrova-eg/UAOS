<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use UniversalAgentOS\Contracts\Gate4VerifierInterface;
use UniversalAgentOS\Contracts\NonceStoreInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\PathValidatorInterface;
use UniversalAgentOS\Exceptions\Gate4VerificationException;

/**
 * Gate 4 cryptographic human clearance verification engine.
 */
class Gate4Verifier implements Gate4VerifierInterface
{
    private PathResolverInterface $pathResolver;
    private NonceStoreInterface $nonceStore;
    private PathValidatorInterface $pathValidator;

    public function __construct(
        PathResolverInterface $pathResolver,
        NonceStoreInterface $nonceStore,
        PathValidatorInterface $pathValidator
    ) {
        $this->pathResolver = $pathResolver;
        $this->nonceStore = $nonceStore;
        $this->pathValidator = $pathValidator;
    }

    public function issueToken(array $tokenPayload, string $secret): string
    {
        $payloadToSign = $tokenPayload;
        unset($payloadToSign['hmac_sha256'], $payloadToSign['signature']);
        ksort($payloadToSign);

        $canonicalJson = (string)json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $canonicalJson, $secret);

        $tokenPayload['hmac_sha256'] = $signature;
        return (string)json_encode($tokenPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function verifyToken(string $tokenJson, Gate4Request $request, string $secret): Gate4Response
    {
        $decoded = $this->parseTokenPayload($tokenJson);

        $this->assertContextualBinding($decoded, $request);

        $now = time();
        $expiresAt = (int)($decoded['expires_at'] ?? 0);
        if ($now > $expiresAt) {
            throw new Gate4VerificationException("Gate 4 token expired at timestamp {$expiresAt} (current: {$now}).");
        }

        $this->assertPrerequisiteIntegrity($decoded, $request);

        $providedSig = (string)($decoded['hmac_sha256'] ?? $decoded['signature'] ?? '');
        $payloadToVerify = $decoded;
        unset($payloadToVerify['hmac_sha256'], $payloadToVerify['signature']);
        ksort($payloadToVerify);
        $canonicalJson = (string)json_encode($payloadToVerify, JSON_UNESCAPED_SLASHES);
        $expectedSig = hash_hmac('sha256', $canonicalJson, $secret);

        if (!hash_equals($expectedSig, $providedSig)) {
            throw new Gate4VerificationException('Cryptographic HMAC-SHA256 signature verification failed.');
        }

        $nonce = (string)($decoded['nonce'] ?? '');
        if ($nonce === '') {
            throw new Gate4VerificationException('Token missing mandatory 128-bit nonce.');
        }

        $this->assertNotRevoked($nonce);

        // Atomic nonce consumption (throws NonceReplayException on reuse)
        $this->nonceStore->consumeNonce($nonce, $request->taskId, $expiresAt);

        $authorizer = is_array($decoded['authorizer'] ?? null)
            ? (string)($decoded['authorizer']['identity'] ?? 'unknown')
            : (string)($decoded['authorizer'] ?? 'unknown');

        return Gate4Response::granted(
            $authorizer,
            $nonce,
            json_encode($decoded, JSON_UNESCAPED_SLASHES) ?: '',
            ['task_id' => $request->taskId, 'cleared_stage' => $request->clearedStage]
        );
    }

    public function computeArtifactHashes(array $prerequisitePaths): array
    {
        $hashes = [];
        foreach ($prerequisitePaths as $key => $relPath) {
            $absPath = $this->pathResolver->resolveProjectPath($relPath);
            if (file_exists($absPath)) {
                $content = file_get_contents($absPath);
                $hashes[$key] = hash('sha256', $content !== false ? $content : '');
            } else {
                $hashes[$key] = 'NOT_FOUND';
            }
        }
        return $hashes;
    }

    private function parseTokenPayload(string $tokenInput): array
    {
        $raw = trim($tokenInput);
        if (file_exists($raw)) {
            $fileContent = file_get_contents($raw);
            $raw = $fileContent !== false ? $fileContent : '';
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new Gate4VerificationException('Invalid Gate 4 token format: payload must be valid JSON.');
        }
        return $decoded;
    }

    private function assertContextualBinding(array $decoded, Gate4Request $request): void
    {
        if (isset($decoded['project_id']) && $decoded['project_id'] !== $request->projectId) {
            throw new Gate4VerificationException(
                "Project ID mismatch: token '{$decoded['project_id']}' != expected '{$request->projectId}'"
            );
        }

        if (isset($decoded['task_id']) && $decoded['task_id'] !== $request->taskId) {
            throw new Gate4VerificationException(
                "Task ID mismatch: token '{$decoded['task_id']}' != expected '{$request->taskId}'"
            );
        }

        $rootHash = hash('sha256', $this->pathValidator->normalizePath($this->pathResolver->getProjectRootDir()));
        if (isset($decoded['project_root_hash']) && !hash_equals($rootHash, (string)$decoded['project_root_hash'])) {
            throw new Gate4VerificationException('Project root path hash mismatch: token bound to different project.');
        }
    }

    private function assertPrerequisiteIntegrity(array $decoded, Gate4Request $request): void
    {
        if (empty($request->prerequisiteArtifacts)) {
            return;
        }

        $tokenHashes = (array)($decoded['prerequisites'] ?? []);
        $currentHashes = $this->computeArtifactHashes($request->prerequisiteArtifacts);

        foreach ($currentHashes as $key => $currentHash) {
            $tokenHashKey = str_ends_with($key, '_hash') ? $key : "{$key}_hash";
            if (isset($tokenHashes[$tokenHashKey])) {
                if (!hash_equals((string)$tokenHashes[$tokenHashKey], $currentHash)) {
                    throw new Gate4VerificationException(
                        "Prerequisite artifact '{$key}' was modified after Gate 4 authorization was granted."
                    );
                }
            }
        }
    }

    private function assertNotRevoked(string $nonce): void
    {
        $revocationsFile = $this->pathResolver->getProjectStateDir() . DIRECTORY_SEPARATOR . 'revocations.json';
        if (file_exists($revocationsFile)) {
            $raw = file_get_contents($revocationsFile);
            $list = is_string($raw) ? json_decode($raw, true) : [];
            if (is_array($list) && in_array($nonce, $list, true)) {
                throw new Gate4VerificationException("Gate 4 token nonce '{$nonce}' has been explicitly revoked.");
            }
        }
    }
}
