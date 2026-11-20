<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Security\Gate4Response;

/**
 * Gate 4 cryptographic human authorization verification engine.
 */
interface Gate4VerifierInterface
{
    /**
     * Creates and signs an authorization token.
     *
     * @param array<string, mixed> $tokenPayload
     * @param string $secret HMAC key or private signing key
     */
    public function issueToken(array $tokenPayload, string $secret): string;

    /**
     * Verifies a raw Gate 4 authorization token against request context and prereq hashes.
     *
     * @param string $tokenJson Raw JSON string or file path containing token
     * @param Gate4Request $request Authoritative request context
     * @param string $secret Verification key
     * @return Gate4Response Clearance decision
     * @throws \UniversalAgentOS\Exceptions\Gate4VerificationException If verification fails
     */
    public function verifyToken(string $tokenJson, Gate4Request $request, string $secret): Gate4Response;

    /**
     * Recalculates cryptographic SHA-256 hashes of prerequisite artifacts.
     *
     * @param array<string, string> $prerequisitePaths Map of artifact key => relative file path
     * @return array<string, string> Map of artifact key => sha256 hash
     */
    public function computeArtifactHashes(array $prerequisitePaths): array;
}
