<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

/**
 * Value object representing the outcome of a Gate 4 clearance verification.
 */
readonly class Gate4Response
{
    /**
     * @param bool $cleared Whether clearance was granted
     * @param string $decision Decision code: 'CLEARANCE_GRANTED', 'CLEARANCE_REJECTED', 'EXPIRED'
     * @param string $reason Human-readable explanation
     * @param string|null $authorizer Identity of human authorizer if cleared
     * @param string|null $tokenRaw Raw token JSON string if granted
     * @param string|null $nonce Consumed nonce
     * @param array<string, mixed> $auditDetails Details for evidence log
     */
    public function __construct(
        public bool $cleared,
        public string $decision,
        public string $reason,
        public ?string $authorizer = null,
        public ?string $tokenRaw = null,
        public ?string $nonce = null,
        public array $auditDetails = []
    ) {
    }

    public static function granted(
        string $authorizer,
        string $nonce,
        string $tokenRaw,
        array $auditDetails = []
    ): self {
        return new self(
            true,
            'CLEARANCE_GRANTED',
            'Gate 4 cryptographic clearance verified successfully.',
            $authorizer,
            $tokenRaw,
            $nonce,
            $auditDetails
        );
    }

    public static function rejected(string $reason, array $auditDetails = []): self
    {
        return new self(
            false,
            'CLEARANCE_REJECTED',
            $reason,
            null,
            null,
            null,
            $auditDetails
        );
    }
}
