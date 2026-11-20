<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Exceptions\Gate4VerificationException;

/**
 * Manages Gate 4 cryptographic authorization keys, key generation, rotation, and storage.
 */
class KeyManager
{
    private PathResolverInterface $pathResolver;
    private string $secretsDir;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->secretsDir = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . '.secrets';
        if (!is_dir($this->secretsDir)) {
            mkdir($this->secretsDir, 0700, true);
        }
    }

    /**
     * Generates a new 256-bit cryptographically secure random key.
     */
    public function generateKey(string $kid = 'default'): string
    {
        $rawBytes = random_bytes(32);
        $hexKey = bin2hex($rawBytes);
        $keyFile = $this->getKeyFilePath($kid);

        file_put_contents($keyFile, $hexKey);
        @chmod($keyFile, 0600);

        return $hexKey;
    }

    /**
     * Retrieves an existing key by Key ID (kid) or from environment.
     */
    public function getKey(string $kid = 'default'): string
    {
        // 1. Environment variable lookup priority
        $envKey = getenv('AGENT_OS_GATE4_SECRET');
        if (is_string($envKey) && $envKey !== '') {
            return trim($envKey);
        }

        // Backward compatibility fallback
        $legacyEnv = getenv('CORE_GATE4_HUMAN_SECRET');
        if (is_string($legacyEnv) && $legacyEnv !== '') {
            return trim($legacyEnv);
        }

        // 2. Secret file lookup
        $keyFile = $this->getKeyFilePath($kid);
        if (file_exists($keyFile)) {
            $raw = file_get_contents($keyFile);
            if (is_string($raw) && trim($raw) !== '') {
                return trim($raw);
            }
        }

        // Auto-generate default key if missing
        return $this->generateKey($kid);
    }

    /**
     * Revokes a key by deleting its secret file.
     */
    public function revokeKey(string $kid): bool
    {
        $keyFile = $this->getKeyFilePath($kid);
        if (file_exists($keyFile)) {
            return unlink($keyFile);
        }
        return false;
    }

    private function getKeyFilePath(string $kid): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9_\-]/', '', $kid) ?: 'default';
        return $this->secretsDir . DIRECTORY_SEPARATOR . "gate4-{$sanitized}.key";
    }
}
