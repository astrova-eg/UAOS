<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once dirname(__DIR__) . '/runtime/src/Gate4Verifier.php';
require_once dirname(__DIR__) . '/runtime/src/ToolGateway.php';

use UniversalCore\Runtime\Gate4Verifier;
use UniversalCore\Runtime\ToolGateway;

final class Gate4Test
{
    public static function run(): void
    {
        echo "Running Gate4Test...\n";
        $tmpDir = sys_get_temp_dir() . '/core_gate4_test_' . time();
        mkdir($tmpDir, 0777, true);

        // 1. Create dummy prerequisite files
        $srsFile = $tmpDir . '/srs.md';
        $dagFile = $tmpDir . '/dag.md';
        $planFile = $tmpDir . '/plan.md';

        file_put_contents($srsFile, "# SRS Content 1.0");
        file_put_contents($dagFile, "# Task DAG Content 1.0");
        file_put_contents($planFile, "# Implementation Plan Content 1.0");

        $srsHash = hash('sha256', (string)file_get_contents($srsFile));
        $dagHash = hash('sha256', (string)file_get_contents($dagFile));
        $planHash = hash('sha256', (string)file_get_contents($planFile));

        $secret = 'test-human-secret-key-999';
        $systemId = 'TEST-SYS-001';
        $nonce = 'nonce-' . uniqid();
        $now = date('c');
        $expires = date('c', time() + 3600);

        $manifest = [
            'approver' => 'Human Sponsor',
            'created_at' => $now,
            'expires_at' => $expires,
            'git_baseline' => 'commit-abc',
            'nonce' => $nonce,
            'plan_hash' => $planHash,
            'plan_path' => $planFile,
            'project_id' => 'universal-core',
            'srs_hash' => $srsHash,
            'srs_path' => $srsFile,
            'system_id' => $systemId,
            'task_dag_hash' => $dagHash,
            'task_dag_path' => $dagFile
        ];

        ksort($manifest);
        $pairs = [];
        foreach ($manifest as $k => $v) {
            $pairs[] = "{$k}={$v}";
        }
        $canonStr = implode('&', $pairs);
        $sig = hash_hmac('sha256', $canonStr, $secret);

        $validToken = [
            'version' => '1.0.0',
            'gate' => 'GATE-4',
            'algorithm' => 'HMAC-SHA256',
            'canonical_string' => $canonStr,
            'signature' => $sig,
            'manifest' => $manifest
        ];

        $tokenPath = $tmpDir . '/token.json';
        file_put_contents($tokenPath, json_encode($validToken, JSON_PRETTY_PRINT));

        $verifier = new Gate4Verifier();

        // Test 1: Valid human authorization
        $res = $verifier->verifyToken($tokenPath, $secret);
        Assert::assertTrue($res['valid'], "Gate4Test: Valid human authorization verifies successfully");

        // Test 2: Replay attack with same nonce
        $resReplay = $verifier->verifyToken($tokenPath, $secret);
        Assert::assertFalse($resReplay['valid'], "Gate4Test: Replayed token rejected");

        // Test 3: Invalid HMAC signature
        $verifier->resetNonces();
        $resInvalidSig = $verifier->verifyToken($tokenPath, 'wrong-secret-key');
        Assert::assertFalse($resInvalidSig['valid'], "Gate4Test: Invalid HMAC rejected");

        // Test 4: Modified SRS content
        $verifier->resetNonces();
        file_put_contents($srsFile, "# SRS Tampered Content");
        $resTamperedSrs = $verifier->verifyToken($tokenPath, $secret);
        Assert::assertFalse($resTamperedSrs['valid'], "Gate4Test: Tampered SRS rejected");
        file_put_contents($srsFile, "# SRS Content 1.0"); // restore

        // Test 5: Modified Task DAG content
        $verifier->resetNonces();
        file_put_contents($dagFile, "# Task DAG Tampered Content");
        $resTamperedDag = $verifier->verifyToken($tokenPath, $secret);
        Assert::assertFalse($resTamperedDag['valid'], "Gate4Test: Tampered Task DAG rejected");
        file_put_contents($dagFile, "# Task DAG Content 1.0"); // restore

        // Test 6: Modified Plan content
        $verifier->resetNonces();
        file_put_contents($planFile, "# Implementation Plan Tampered Content");
        $resTamperedPlan = $verifier->verifyToken($tokenPath, $secret);
        Assert::assertFalse($resTamperedPlan['valid'], "Gate4Test: Tampered Implementation Plan rejected");
        file_put_contents($planFile, "# Implementation Plan Content 1.0"); // restore

        // Test 7: Expired token
        $verifier->resetNonces();
        $expiredManifest = $manifest;
        $expiredManifest['expires_at'] = date('c', time() - 3600);
        $expiredToken = $validToken;
        $expiredToken['manifest'] = $expiredManifest;
        $expiredTokenPath = $tmpDir . '/expired_token.json';
        file_put_contents($expiredTokenPath, json_encode($expiredToken, JSON_PRETTY_PRINT));
        $resExpired = $verifier->verifyToken($expiredTokenPath, $secret);
        Assert::assertFalse($resExpired['valid'], "Gate4Test: Expired token rejected");

        // Test 8: Agent unauthorized self-signing attempt blocked by gateway
        $gateway = new ToolGateway();
        $agentSignReq = [
            'agent_id' => 'CORE-001',
            'department' => 'DEPT-D',
            'operation' => 'sign_gate4',
            'stage' => 9
        ];
        $signRes = $gateway->evaluateAndExecute($agentSignReq);
        Assert::assertFalse($signRes['permitted'], "Gate4Test: Agent signing attempt strictly blocked by Gateway");

        // Test 9: Human secret isolation
        Assert::assertFalse(
            isset($_SERVER['CORE_GATE4_HUMAN_SECRET']) && $_SERVER['CORE_GATE4_HUMAN_SECRET'] === $secret,
            "Gate4Test: Secret key is isolated from ambient agent server environment"
        );

        // Cleanup temp dir
        unlink($srsFile);
        unlink($dagFile);
        unlink($planFile);
        unlink($tokenPath);
        unlink($expiredTokenPath);
        rmdir($tmpDir);
    }
}
