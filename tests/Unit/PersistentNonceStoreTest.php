<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Exceptions\NonceReplayException;
use UniversalAgentOS\Tests\Assert;

class PersistentNonceStoreTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $store = new PersistentNonceStore($resolver);

        $nonce = 'test_nonce_' . bin2hex(random_bytes(8));
        $taskId = 'TASK-NONCE-001';
        $expiresAt = time() + 3600;

        Assert::assertFalse($store->isConsumed($nonce), "Fresh nonce is not marked consumed");

        $consumed = $store->consumeNonce($nonce, $taskId, $expiresAt);
        Assert::assertTrue($consumed, "Fresh nonce is consumed successfully");
        Assert::assertTrue($store->isConsumed($nonce), "Consumed nonce is verified consumed");

        // Replay attempt throws NonceReplayException
        Assert::assertThrows(NonceReplayException::class, function () use ($store, $nonce, $taskId, $expiresAt) {
            $store->consumeNonce($nonce, $taskId, $expiresAt);
        }, "Consuming duplicate nonce throws NonceReplayException");

        // Expired nonce consumption rejected
        $expiredNonce = 'expired_nonce_' . bin2hex(random_bytes(8));
        Assert::assertThrows(NonceReplayException::class, function () use ($store, $expiredNonce, $taskId) {
            $store->consumeNonce($expiredNonce, $taskId, time() - 100);
        }, "Consuming expired nonce throws NonceReplayException");
    }
}
