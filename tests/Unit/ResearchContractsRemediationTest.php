<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use RuntimeException;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TimestampValidator;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;
use UniversalCore\Validation\RdeiSchemaValidator;

require_once __DIR__ . '/../../.agents/validation/src/RdeiSchemaValidator.php';
require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

/**
 * Focused regression tests for Phase 4.1 remediation:
 * - Authentication secret hygiene in SourceDefinition
 * - Strict ISO-8601 timestamp validation
 * - ResearchDossier canonical hashing & restoration integrity
 * - Access mechanisms validation boundary verification
 */
class ResearchContractsRemediationTest
{
    public static function run(): void
    {
        self::testAuthenticationSecretHygiene();
        self::testStrictTimestampValidation();
        self::testCanonicalHashingAndIntegrity();
        self::testAccessMechanismsValidationBoundary();
    }

    private static function testAuthenticationSecretHygiene(): void
    {
        $base = [
            'source_id' => 'src-sec-nvd',
            'official_name' => 'National Vulnerability Database',
            'canonical_url' => 'https://nvd.nist.gov',
            'domains' => ['C'],
            'subdomains' => ['C.01'],
            'authority_tier' => 1,
        ];

        // 1. Missing, null, empty env_var_key with requires_auth = true
        foreach ([null, '', '   '] as $badKey) {
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($base, $badKey) {
                $p = $base;
                $p['auth_profile'] = ['requires_auth' => true, 'auth_type' => 'api_key_header'];
                if ($badKey !== null) $p['auth_profile']['env_var_key'] = $badKey;
                SourceDefinition::fromArray($p);
            }, "Rejects missing/null/empty env_var_key when requires_auth=true");
        }

        // 2. Invalid environment-variable references
        foreach (['api_key', 'API KEY', 'API-KEY', '1_KEY', 'A1B2C3D4E5F60718293A4B5C', 'GHP_abc123', 'SK_LIVE_xyz'] as $invalidKey) {
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($base, $invalidKey) {
                $p = $base;
                $p['auth_profile'] = ['requires_auth' => true, 'auth_type' => 'api_key_header', 'env_var_key' => $invalidKey];
                SourceDefinition::fromArray($p);
            }, "Rejects invalid env_var_key reference '{$invalidKey}'");
        }

        // 3. Secret-like values in forbidden authentication fields
        foreach (['token' => 'raw_secret', 'password' => 'pass123', 'secret' => 'topsecret', 'api_key' => 'key123'] as $f => $val) {
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($base, $f, $val) {
                $p = $base;
                $p['auth_profile'] = ['requires_auth' => false, 'auth_type' => 'none', $f => $val];
                SourceDefinition::fromArray($p);
            }, "Rejects forbidden field '{$f}' inside auth_profile");
        }

        // 4. Valid env_var_key accepted and serialized safely without secrets
        $validSource = SourceDefinition::fromArray(array_merge($base, [
            'auth_profile' => ['requires_auth' => true, 'auth_type' => 'api_key_header', 'env_var_key' => 'NVD_API_KEY'],
        ]));
        $arr = $validSource->toArray();
        Assert::assertEquals('NVD_API_KEY', $arr['auth_profile']['env_var_key'], "Preserves valid env_var_key reference");
        Assert::assertFalse(isset($arr['auth_profile']['token']), "toArray() contains no credentials or secret values");
        $debug = $validSource->__debugInfo();
        Assert::assertEquals($arr, $debug, "__debugInfo() does not expose secrets");
    }

    private static function testStrictTimestampValidation(): void
    {
        Assert::assertTrue(TimestampValidator::isValid('2026-11-15T18:00:00Z'), "Valid UTC ISO-8601 passes");
        Assert::assertTrue(TimestampValidator::isValid('2026-11-15T18:00:00+02:00'), "Valid offset ISO-8601 passes");
        Assert::assertTrue(TimestampValidator::isValid('2026-11-15T18:00:00.123456-05:00'), "Valid fractional ISO-8601 passes");

        // Invalid calendar date, missing timezone, date-only, natural-language, malformed
        $invalidDates = [
            '2026-02-31T12:00:00Z', '2026-04-31T12:00:00Z', '2026-11-15', '2026-11-15 18:00:00',
            'yesterday', 'tomorrow', 'now', 'invalid-date', '2026-99-99T99:99:99Z', '2026-11-15T25:00:00Z'
        ];
        foreach ($invalidDates as $badDate) {
            Assert::assertFalse(TimestampValidator::isValid($badDate), "Rejects invalid date '{$badDate}'");
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($badDate) {
                new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, $badDate);
            }, "Constructor rejects invalid researchedAt '{$badDate}'");
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($badDate) {
                ResearchDossier::fromArray(['dossier_id' => 'DOS-01', 'task_id' => 'TASK-01', 'researched_at' => $badDate]);
            }, "fromArray() rejects invalid researchedAt '{$badDate}'");
        }

        // Invalid expiresAt cannot pass as non-expired
        $validDossier = new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', '2026-11-15T20:00:00Z');
        Assert::assertTrue($validDossier->isExpired(strtotime('2026-11-15T21:00:00Z')), "Recognizes expired timestamp");
        Assert::assertFalse($validDossier->isExpired(strtotime('2026-11-15T19:00:00Z')), "Recognizes active non-expired timestamp");
    }

    private static function testCanonicalHashingAndIntegrity(): void
    {
        $claim = new ExtractedClaim('CLM-01', 'Claim statement', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95, ['8.2'], [
            ['source_id' => 'src-01', 'content_hash' => hash('sha256', 'content'), 'quote_segment' => 'Quote']
        ]);

        // 1. Equivalent associative data with different key insertion order produces identical hashes
        $d1 = new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', sourcesConsulted: [
            ['z_key' => 'last', 'a_key' => 'first', 'm_key' => ['beta' => 2, 'alpha' => 1]]
        ], claims: [$claim]);
        $d2 = new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', sourcesConsulted: [
            ['a_key' => 'first', 'm_key' => ['alpha' => 1, 'beta' => 2], 'z_key' => 'last']
        ], claims: [$claim]);
        Assert::assertEquals($d1->computeHash(), $d2->computeHash(), "Equivalent associative data produces identical hash");

        // 2. Meaningful list reordering changes the hash
        $claim2 = new ExtractedClaim('CLM-02', 'Second claim', ExtractedClaim::STATUS_CORROBORATED_PRACTICE, 0.88, ['8.3'], [
            ['source_id' => 'src-02', 'content_hash' => hash('sha256', 'content2'), 'quote_segment' => 'Quote2']
        ]);
        $dList1 = new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', claims: [$claim, $claim2]);
        $dList2 = new ResearchDossier('DOS-01', 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', claims: [$claim2, $claim]);
        Assert::assertTrue($dList1->computeHash() !== $dList2->computeHash(), "List reordering changes hash");

        // 3. Top-level and nested claims, conflicts, security findings modifications invalidate hash
        $sealed = $d1->seal();
        Assert::assertTrue($sealed->verifyIntegrity(), "Sealed dossier verifies integrity");

        $tamperedTop = $sealed->toArray();
        $tamperedTop['gate_0_status'] = ResearchDossier::GATE_STATUS_EXEMPT;
        Assert::assertThrows(InvalidArgumentException::class, static function () use ($tamperedTop) {
            ResearchDossier::fromArray($tamperedTop);
        }, "Restoring modified top-level field is rejected by integrity verification");

        $tamperedArray = $sealed->toArray();
        $tamperedArray['claims'][0]['statement'] = 'Tampered statement';
        Assert::assertThrows(InvalidArgumentException::class, static function () use ($tamperedArray) {
            ResearchDossier::fromArray($tamperedArray);
        }, "Restoring tampered claims is rejected by integrity verification");

        $tamperedConflicts = $sealed->toArray();
        $tamperedConflicts['conflicts_detected'] = [['conflict_id' => 'C1', 'status' => 'TAMPERED']];
        Assert::assertThrows(InvalidArgumentException::class, static function () use ($tamperedConflicts) {
            ResearchDossier::fromArray($tamperedConflicts);
        }, "Restoring tampered conflicts is rejected by integrity verification");

        // 4. JSON encoding failure handled safely
        Assert::assertThrows(RuntimeException::class, static function () {
            $badUtf8 = new ResearchDossier('DOS-01', 'TASK-01', "core\xB1\x31", DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z');
            $badUtf8->computeHash();
        }, "JSON encoding failure throws RuntimeException safely");

        // 5. Valid sealed dossier restores successfully
        $restored = ResearchDossier::fromArray($sealed->toArray());
        Assert::assertTrue($restored->verifyIntegrity(), "Valid sealed dossier restores successfully");
        Assert::assertTrue($restored->isSealed(), "Restored dossier is recognized as sealed");

        // 6. Unsealed dossier behavior preserved
        $unsealed = ResearchDossier::fromArray($d1->toArray());
        Assert::assertFalse($unsealed->isSealed(), "Unsealed dossier remains unsealed");
        Assert::assertFalse($unsealed->verifyIntegrity(), "Unsealed dossier fails verification");
    }

    private static function testAccessMechanismsValidationBoundary(): void
    {
        $validator = new RdeiSchemaValidator();
        $base = RdeiSchemaFixtures::getValidSourceDef();

        // 1. Missing evidence_reference is rejected
        $badMech1 = $base;
        unset($badMech1['access_mechanisms'][0]['evidence_reference']);
        $errors = $validator->validateSourceDefinition($badMech1);
        Assert::assertTrue(count($errors) > 0, "Missing evidence_reference is rejected");

        // 2. Missing or invalid verified_at is rejected
        $badMech2 = $base;
        $badMech2['access_mechanisms'][0]['verified_at'] = '2026-99-99T99:99:99Z';
        $errors = $validator->validateSourceDefinition($badMech2);
        Assert::assertTrue(count($errors) > 0, "Invalid mechanism verified_at is rejected");

        // 3. Invalid mechanism-specific verification status
        $badMech3 = $base;
        $badMech3['access_mechanisms'][0]['verification_status'] = 'invalid_nonexistent_status';
        $errors = $validator->validateSourceDefinition($badMech3);
        Assert::assertTrue(count($errors) > 0, "Invalid verification_status is rejected");

        // 4. Invalid mechanism authentication override (requires_auth=false with env_var_key)
        $badMech4 = $base;
        $badMech4['access_mechanisms'][0]['auth_requirement'] = [
            'requires_auth' => false,
            'auth_type' => 'none',
            'env_var_key' => 'UNWANTED_KEY',
        ];
        $errors = $validator->validateSourceDefinition($badMech4);
        Assert::assertTrue(count($errors) > 0, "Mechanism override requires_auth=false forbids env_var_key");
    }
}
