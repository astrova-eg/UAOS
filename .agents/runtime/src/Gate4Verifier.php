<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class Gate4Verifier
{
    private string $projectRoot;
    /** @var array<string, bool> */
    private array $usedNonces = [];

    public function __construct(?string $projectRoot = null)
    {
        $this->projectRoot = $projectRoot ?? dirname(__DIR__, 3);
    }

    /**
     * @param string $tokenPath
     * @param string|null $secretKey
     * @return array{valid: bool, error: string|null, details: array<string, mixed>}
     */
    public function verifyToken(string $tokenPath, ?string $secretKey = null): array
    {
        if (!file_exists($tokenPath)) {
            return [
                'valid' => false,
                'error' => "Gate 4 token file not found: {$tokenPath}",
                'details' => []
            ];
        }

        $raw = file_get_contents($tokenPath);
        if ($raw === false) {
            return [
                'valid' => false,
                'error' => "Failed to read Gate 4 token file: {$tokenPath}",
                'details' => []
            ];
        }

        // Strip UTF-8 BOM if present
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw) ?? $raw;

        $token = json_decode($raw, true);
        if (!is_array($token) || !isset($token['manifest'], $token['signature'])) {
            return [
                'valid' => false,
                'error' => "Malformed Gate 4 token structure.",
                'details' => []
            ];
        }

        $algorithm = (string)($token['algorithm'] ?? '');
        if ($algorithm !== 'HMAC-SHA256') {
            return [
                'valid' => false,
                'error' => "Unsupported algorithm '{$algorithm}'. Expected HMAC-SHA256.",
                'details' => []
            ];
        }

        $manifest = $token['manifest'];
        if (!is_array($manifest)) {
            return [
                'valid' => false,
                'error' => "Manifest block missing or not an object.",
                'details' => []
            ];
        }

        // 1. Check expiration
        $expiresAtStr = (string)($manifest['expires_at'] ?? '');
        $expiresAt = strtotime($expiresAtStr);
        if ($expiresAt === false || time() > $expiresAt) {
            return [
                'valid' => false,
                'error' => "Gate 4 token expired at {$expiresAtStr}.",
                'details' => ['expires_at' => $expiresAtStr]
            ];
        }

        // 2. Check nonce replay
        $nonce = (string)($manifest['nonce'] ?? '');
        if ($nonce === '') {
            return [
                'valid' => false,
                'error' => "Gate 4 token missing nonce.",
                'details' => []
            ];
        }
        if (isset($this->usedNonces[$nonce])) {
            return [
                'valid' => false,
                'error' => "Replay attack detected: Nonce '{$nonce}' has already been processed.",
                'details' => ['nonce' => $nonce]
            ];
        }

        // 3. Physical Prerequisite Hashes Recalculation
        $srsCheck = $this->verifyFileHash(
            (string)($manifest['srs_path'] ?? ''),
            (string)($manifest['srs_hash'] ?? '')
        );
        if (!$srsCheck['match']) {
            return [
                'valid' => false,
                'error' => "SRS hash mismatch or missing file: {$srsCheck['error']}",
                'details' => ['srs' => $srsCheck]
            ];
        }

        $dagCheck = $this->verifyFileHash(
            (string)($manifest['task_dag_path'] ?? ''),
            (string)($manifest['task_dag_hash'] ?? '')
        );
        if (!$dagCheck['match']) {
            return [
                'valid' => false,
                'error' => "Task DAG hash mismatch or missing file: {$dagCheck['error']}",
                'details' => ['dag' => $dagCheck]
            ];
        }

        $planCheck = $this->verifyFileHash(
            (string)($manifest['plan_path'] ?? ''),
            (string)($manifest['plan_hash'] ?? '')
        );
        if (!$planCheck['match']) {
            return [
                'valid' => false,
                'error' => "Plan hash mismatch or missing file: {$planCheck['error']}",
                'details' => ['plan' => $planCheck]
            ];
        }

        // 4. Reconstruct Canonical String and Verify Signature
        ksort($manifest);
        $pairs = [];
        foreach ($manifest as $k => $v) {
            $pairs[] = "{$k}={$v}";
        }
        $canonicalString = implode('&', $pairs);

        if ($secretKey !== null && $secretKey !== '') {
            $expectedSig = hash_hmac('sha256', $canonicalString, $secretKey);
            $providedSig = (string)$token['signature'];
            if (!hash_equals($expectedSig, $providedSig)) {
                return [
                    'valid' => false,
                    'error' => "HMAC-SHA256 signature verification failed. Token is invalid or forged.",
                    'details' => ['expected_sig' => $expectedSig, 'provided_sig' => $providedSig]
                ];
            }
        }

        // Mark nonce as active in session
        $this->usedNonces[$nonce] = true;

        return [
            'valid' => true,
            'error' => null,
            'details' => [
                'system_id' => $manifest['system_id'] ?? 'unknown',
                'approver' => $manifest['approver'] ?? 'unknown',
                'created_at' => $manifest['created_at'] ?? '',
                'expires_at' => $expiresAtStr,
                'nonce' => $nonce,
                'signature' => $token['signature']
            ]
        ];
    }

    /**
     * @param string $relativePath
     * @param string $expectedHash
     * @return array{match: bool, error: string|null, computed_hash: string|null}
     */
    private function verifyFileHash(string $relativePath, string $expectedHash): array
    {
        $normalized = str_replace('\\', '/', $relativePath);
        $fullPath = $this->projectRoot . '/' . ltrim($normalized, '/');

        if (!file_exists($fullPath)) {
            // Also check relative to workspace directly
            if (file_exists($relativePath)) {
                $fullPath = $relativePath;
            } else {
                return ['match' => false, 'error' => "File does not exist: {$relativePath}", 'computed_hash' => null];
            }
        }

        $raw = file_get_contents($fullPath);
        if ($raw === false) {
            return ['match' => false, 'error' => "Cannot read file: {$fullPath}", 'computed_hash' => null];
        }

        $computed = hash('sha256', $raw);
        if (!hash_equals(strtolower($expectedHash), strtolower($computed))) {
            return [
                'match' => false,
                'error' => "Computed {$computed} does not match expected {$expectedHash}",
                'computed_hash' => $computed
            ];
        }

        return ['match' => true, 'error' => null, 'computed_hash' => $computed];
    }

    public function resetNonces(): void
    {
        $this->usedNonces = [];
    }
}
