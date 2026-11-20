<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Exceptions\NonceReplayException;
use UniversalAgentOS\Tests\Assert;

class ConcurrencyNonceTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $store = new PersistentNonceStore($resolver);

        $nonce = 'concurrent_nonce_' . bin2hex(random_bytes(8));
        $taskId = 'TASK-CONCURRENT-001';
        $expiresAt = time() + 3600;

        // Process 1 consumes
        $success1 = $store->consumeNonce($nonce, $taskId, $expiresAt);
        Assert::assertTrue($success1, "First consumer successfully acquires valid nonce");

        // Independent Process 2 attempts simultaneous consume
        $store2 = new PersistentNonceStore($resolver);
        $rejected = false;
        try {
            $store2->consumeNonce($nonce, 'TASK-CONCURRENT-002', $expiresAt);
        } catch (NonceReplayException $e) {
            $rejected = true;
        }

        Assert::assertTrue($rejected, "Second concurrent consumer is rejected with NonceReplayException");
    }
}
