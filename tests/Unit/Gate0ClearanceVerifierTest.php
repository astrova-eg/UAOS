<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

class Gate0ClearanceVerifierTest
{
    public static function run(): void
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_gate0_test_' . bin2hex(random_bytes(4));
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'research' . DIRECTORY_SEPARATOR . 'dossiers', 0755, true);

        try {
            $resolver = new PathResolver($tmpDir);
            $repo = new ResearchDossierRepository(storageDir: $tmpDir . DIRECTORY_SEPARATOR . 'research', enableKnowledgeStore: false);
            $evidenceCollector = new EvidenceCollector($resolver);
            $verifier = new Gate0ClearanceVerifier($repo, $resolver, $evidenceCollector);

            self::testLevel0Exempt($verifier);
            self::testValidDossierClearance($verifier, $repo);
            self::testMissingDossierBlocked($verifier);
            self::testInsufficientDepthBlocked($verifier);
            self::testIneligibleSourceBlocked($verifier);
            self::testExpiredDossierBlocked($verifier);
            self::testUnresolvedCriticalContradictionBlocked($verifier);
            self::testTamperedIntegrityBlocked($verifier);
            self::testCallerSuppliedStateImmunity($verifier);
            self::testMissingTradeoffMatrixBlocked($verifier);
            self::testCriticalVulnerabilityBlocked($verifier);
        } finally {
            self::recursiveRemove($tmpDir);
        }
    }

    private static function testLevel0Exempt(Gate0ClearanceVerifier $verifier): void
    {
        $res = $verifier->verifyClearance('TASK-L0-001', 1, DepthProfile::l0());
        Assert::assertTrue($res->isExempt(), 'L0 task is exempt from Gate 0 external research');
        Assert::assertEquals(Gate0ClearanceResult::STATUS_EXEMPT, $res->status, 'L0 status is EXEMPT');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_EXEMPT_L0, $res->operationalCondition, 'L0 condition is EXEMPT_L0');
    }

    private static function testValidDossierClearance(Gate0ClearanceVerifier $verifier, ResearchDossierRepository $repo): void
    {
        $claim = new ExtractedClaim(
            claimId: 'CLM-01',
            statement: 'Immutable value objects eliminate side effects across service boundaries.',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.95,
            applicableVersions: ['8.2', '8.3'],
            citations: [['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'test'), 'quote_segment' => 'Immutable objects']]
        );

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-VALID-01',
            taskId: 'TASK-VALID-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim],
            conflictsDetected: [],
            comparisonMatrices: [],
            securityFindings: [],
            recommendations: [],
            gate0Status: ResearchDossier::GATE_STATUS_BLOCKED
        ))->seal();

        $repo->save($dossier);

        $res = $verifier->verifyClearance('TASK-VALID-01', 1, DepthProfile::l1(), $dossier);
        Assert::assertTrue($res->isPassed(), 'Valid sealed dossier achieves Gate 0 PASSED');
        Assert::assertEquals(Gate0ClearanceResult::STATUS_PASSED, $res->status, 'Status is PASSED');
        Assert::assertEquals('DOS-VALID-01', $res->dossierId, 'Dossier ID recorded in clearance result');
        Assert::assertCount(0, $res->violations, 'Zero violations for valid dossier');
    }

    private static function testMissingDossierBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $res = $verifier->verifyClearance('TASK-NONEXISTENT', 1, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Missing dossier for L1+ task fails closed as BLOCKED');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED, $res->operationalCondition, 'Condition is QUORUM_UNSATISFIED');
        Assert::assertTrue(count($res->violations) > 0, 'Violations indicate missing dossier');
    }

    private static function testInsufficientDepthBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-L1-ONLY',
            taskId: 'TASK-DEPTH-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim]
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l3());
        Assert::assertTrue($res->isBlocked(), 'Dossier at depth L1 fails evaluation against required depth L3');
        Assert::assertStringContains('does not satisfy required depth', $res->violations[0], 'Violation identifies depth mismatch');
    }

    private static function testIneligibleSourceBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'REVOKED_SOURCE', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-INELIGIBLE',
            taskId: 'TASK-INELIGIBLE-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'REVOKED_SOURCE', 'is_eligible' => false, 'lifecycle_state' => 'REVOKED']],
            claims: [$claim]
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Dossier with revoked/ineligible source fails closed');
        Assert::assertStringContains('is ineligible or revoked', $res->violations[0], 'Violation identifies ineligible source');
    }

    private static function testExpiredDossierBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-EXPIRED',
            taskId: 'TASK-EXPIRED-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: '2026-01-01T00:00:00Z',
            expiresAt: '2026-01-02T00:00:00Z',
            sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim]
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Expired dossier fails Gate 0');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_VERSION_MISMATCH, $res->operationalCondition, 'Expired condition is VERSION_MISMATCH');
    }

    private static function testUnresolvedCriticalContradictionBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claimA = new ExtractedClaim('CLM-A', 'AES-256-GCM is secure', ExtractedClaim::STATUS_UNRESOLVED_DISPUTE, 0.5, ['8.3'], [
            ['source_id' => 'SEC_A', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-CONFLICT',
            taskId: 'TASK-CONFLICT-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'SEC_A', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claimA],
            conflictsDetected: [[
                'conflict_id' => 'CNF-01',
                'is_critical' => true,
                'blocks_gate_0' => true,
                'description' => 'Opposing cryptographic cipher recommendations',
            ]]
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Unresolved critical contradiction strictly blocks Gate 0');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_CRITICAL_CONTRADICTION, $res->operationalCondition, 'Condition is CRITICAL_CONTRADICTION');
    }

    private static function testTamperedIntegrityBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $tampered = new ResearchDossier(
            dossierId: 'DOS-TAMPERED',
            taskId: 'TASK-TAMPERED-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim],
            dossierHash: '0000000000000000000000000000000000000000000000000000000000000000' // Bad hash
        );

        $res = $verifier->evaluateDossier($tampered, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Tampered integrity hash fails closed under Gate 0');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_SECURITY_HOLD, $res->operationalCondition, 'Integrity failure triggers SECURITY_HOLD');
    }

    private static function testCallerSuppliedStateImmunity(Gate0ClearanceVerifier $verifier): void
    {
        // Dossier claims gate0Status is PASSED, but has zero sources and zero claims
        $forged = (new ResearchDossier(
            dossierId: 'DOS-FORGED',
            taskId: 'TASK-FORGED-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [],
            claims: [],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        ))->seal();

        $res = $verifier->evaluateDossier($forged, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Verifier ignores caller-supplied PASSED status when evidence requirements fail');
    }

    private static function testMissingTradeoffMatrixBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-L3-NO-MATRIX',
            taskId: 'TASK-L3-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L3,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [
                ['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE'],
                ['source_id' => 'LARAVEL_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE'],
                ['source_id' => 'RFC_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE'],
            ],
            claims: [$claim],
            comparisonMatrices: [] // Missing mandatory matrix for L3
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l3());
        Assert::assertTrue($res->isBlocked(), 'L3 task without mandatory trade-off matrix is blocked');
    }

    private static function testCriticalVulnerabilityBlocked(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Valid claim', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.3'], [
            ['source_id' => 'PHP_DOCS', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-VULN',
            taskId: 'TASK-VULN-01',
            projectIdentity: 'TestProject',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'PHP_DOCS', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim],
            securityFindings: [['cve_id' => 'CVE-2026-9999', 'cvss' => 9.8, 'status' => 'CONFIRMED_VULNERABLE']]
        ))->seal();

        $res = $verifier->evaluateDossier($dossier, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'Dossier with critical CVE (CVSS 9.8) is strictly blocked');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_SECURITY_HOLD, $res->operationalCondition, 'Triggers SECURITY_HOLD');
    }

    private static function recursiveRemove(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? self::recursiveRemove($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
