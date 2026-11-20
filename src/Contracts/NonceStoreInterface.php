<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Durable persistence store providing atomic exactly-once nonce consumption.
 */
interface NonceStoreInterface
{
    /**
     * Atomically consumes a nonce for a given task.
     *
     * @param string $nonce 128-bit hex nonce string
     * @param string $taskId Unique authoritative task identifier
     * @param int $expiresAt Expiration timestamp (seconds since epoch)
     * @return bool True if freshly consumed
     * @throws \UniversalAgentOS\Exceptions\NonceReplayException If nonce was already consumed
     */
    public function consumeNonce(string $nonce, string $taskId, int $expiresAt): bool;

    /**
     * Checks if a nonce has already been consumed.
     */
    public function isConsumed(string $nonce): bool;

    /**
     * Safely prunes expired nonces where expires_at < current timestamp.
     *
     * @param int $now Current timestamp
     * @return int Number of pruned nonces
     */
    public function pruneExpired(int $now): int;

    /**
     * Diagnostic health check of the underlying storage backend.
     *
     * @return array<string, mixed>
     */
    public function getDiagnostics(): array;
}
