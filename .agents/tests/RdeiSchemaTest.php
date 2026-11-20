<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once __DIR__ . '/RdeiSchemaFixtures.php';
require_once dirname(__DIR__) . '/validation/src/RdeiSchemaValidator.php';

use UniversalCore\Validation\RdeiSchemaValidator;

final class RdeiSchemaTest
{
    private static RdeiSchemaValidator $validator;

    public static function run(): void
    {
        echo "Running RdeiSchemaTest...\n";
        self::$validator = new RdeiSchemaValidator();

        self::testSourceDefinitionValidation();
        self::testMechanismVerificationAndTimestamps();
        self::testDateIntegrityAndInvalidTimestamps();
        self::testAuthenticationInheritanceAndOverrides();
        self::testVerificationContradictionDetection();
        self::testOverlayDomainConsistencyAndWildcards();
        self::testOverlayPrecedenceAndVersioning();
        self::testGlobalDomainAndPolicyIdentityConstraints();
        self::testOverlayFreshnessConsistency();
        self::testPolicyGraphInheritanceAndCycles();
        self::testCanonicalExamples();
    }

    private static function testSourceDefinitionValidation(): void
    {
        $valid = RdeiSchemaFixtures::getValidSourceDef();
        Assert::assertEquals(0, count(self::$validator->validateSourceDefinition($valid)), "Valid source definition passes without errors");

        $invalid = $valid;
        unset($invalid['official_name']);
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Missing official_name is rejected");

        $invalid = $valid;
        $invalid['source_id'] = 'INVALID_UPPERCASE';
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Uppercase source_id is rejected");

        $invalid = $valid;
        $invalid['canonical_url'] = 'http://insecure.example.com';
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Insecure remote canonical_url is rejected");
    }

    private static function testMechanismVerificationAndTimestamps(): void
    {
        $base = RdeiSchemaFixtures::getValidSourceDef();

        $invalid = $base;
        unset($invalid['access_mechanisms'][0]['evidence_reference']);
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Missing evidence_reference is rejected");

        $invalid = $base;
        unset($invalid['access_mechanisms'][0]['verified_at']);
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Missing verified_at timestamp is rejected");

        $invalid = $base;
        $invalid['access_mechanisms'][0]['verification_status'] = 'arbitrary_status';
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Unknown verification_status enum is rejected");

        $validUntested = $base;
        $validUntested['access_mechanisms'][0]['verification_status'] = 'untested';
        $validUntested['access_mechanisms'][0]['evidence_reference'] = 'Endpoint announced in public RFC draft without active verification';
        Assert::assertEquals(0, count(self::$validator->validateSourceDefinition($validUntested)), "Explicit 'untested' verification status accepted with reference");
    }

    private static function testDateIntegrityAndInvalidTimestamps(): void
    {
        $base = RdeiSchemaFixtures::getValidSourceDef();

        // Invalid source-level last_verified_at cannot bypass or pass validation
        $invalidSourceDate = $base;
        $invalidSourceDate['last_verified_at'] = 'invalid-not-a-date';
        $errors = self::$validator->validateSourceDefinition($invalidSourceDate);
        Assert::assertTrue(count($errors) > 0, "Invalid source last_verified_at string is rejected");
        Assert::assertTrue(str_contains(implode(' ', $errors), "source last_verified_at must be valid ISO 8601"), "Clear error message for invalid source date");

        // Invalid mechanism verified_at cannot bypass or pass validation
        $invalidMechDate = $base;
        $invalidMechDate['access_mechanisms'][0]['verified_at'] = '2026-99-99T99:99:99Z';
        $errors = self::$validator->validateSourceDefinition($invalidMechDate);
        Assert::assertTrue(count($errors) > 0, "Malformed mechanism verified_at timestamp is rejected");
        Assert::assertTrue(str_contains(implode(' ', $errors), "verified_at must be valid ISO 8601"), "Clear error message for invalid mechanism date");

        // Temporal inequality: mechanism verified_at newer than source last_verified_at
        $futureMech = $base;
        $futureMech['last_verified_at'] = '2026-11-10T00:00:00Z';
        $futureMech['access_mechanisms'][0]['verified_at'] = '2026-11-15T00:00:00Z';
        $errors = self::$validator->validateSourceDefinition($futureMech);
        Assert::assertTrue(count($errors) > 0, "Mechanism verified_at newer than source last_verified_at is rejected");
    }

    private static function testAuthenticationInheritanceAndOverrides(): void
    {
        $base = RdeiSchemaFixtures::getValidSourceDef();

        // Source requires auth, mechanism overrides to public (requires_auth: false)
        $override = $base;
        $override['access_mechanisms'][] = [
            'mechanism_type' => 'http_direct',
            'endpoint_url' => 'https://example.org/public-feed',
            'is_active' => true,
            'verification_status' => 'docs_verified',
            'evidence_reference' => 'Public landing page accessible without credentials',
            'verified_at' => '2026-11-15T18:00:00Z',
            'auth_requirement' => ['requires_auth' => false, 'auth_type' => 'none']
        ];
        Assert::assertEquals(0, count(self::$validator->validateSourceDefinition($override)), "Public mechanism override on authenticated source accepted");

        // Public mechanism cannot specify env_var_key
        $invalid = $override;
        $invalid['access_mechanisms'][1]['auth_requirement']['env_var_key'] = 'UNWANTED_KEY';
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Mechanism with requires_auth=false cannot specify env_var_key");

        // Authenticated source requires valid env_var_key
        $invalid = $base;
        $invalid['auth_profile']['env_var_key'] = '';
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Empty env_var_key on authenticated source is rejected");
    }

    private static function testVerificationContradictionDetection(): void
    {
        $base = RdeiSchemaFixtures::getValidSourceDef();

        $invalid = $base;
        $invalid['verification_profile']['auth_tested'] = true;
        $invalid['verification_profile']['connectivity_tested'] = false;
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Contradiction auth_tested=true with connectivity_tested=false is rejected");

        $invalid = $base;
        $invalid['verification_profile']['connectivity_tested'] = true;
        $invalid['verification_profile']['endpoint_verified'] = false;
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Contradiction connectivity_tested=true with endpoint_verified=false is rejected");

        $invalid = $base;
        $invalid['access_mechanisms'][0]['verification_status'] = 'connectivity_tested';
        $invalid['verification_profile']['connectivity_tested'] = false;
        Assert::assertTrue(count(self::$validator->validateSourceDefinition($invalid)) > 0, "Mechanism claiming connectivity_tested with false in profile is rejected");
    }

    private static function testOverlayDomainConsistencyAndWildcards(): void
    {
        $base = RdeiSchemaFixtures::getValidDomainOverlay();
        Assert::assertEquals(0, count(self::$validator->validateEvidenceOverlay($base)), "Valid domain overlay passes");

        $invalid = RdeiSchemaFixtures::getValidSubdomainOverlay();
        $invalid['target_subdomains'] = ['A.01'];
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Target subdomain mismatch against target_domain is rejected");

        $invalid = RdeiSchemaFixtures::getValidSubdomainOverlay();
        $invalid['target_subdomains'] = ['B.02', 'B.02'];
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Duplicate subdomains in overlay are rejected");

        $invalid = RdeiSchemaFixtures::getValidDomainOverlay();
        $invalid['target_subdomains'] = ['*', 'B.02'];
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Ambiguous wildcard combination ['*', 'B.02'] is rejected");
    }

    private static function testOverlayPrecedenceAndVersioning(): void
    {
        $base = RdeiSchemaFixtures::getValidDomainOverlay();

        $invalid = $base;
        $invalid['precedence_rank'] = 75;
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Precedence rank 75 is rejected (only 0, 50, 100 allowed)");

        $invalid = $base;
        $invalid['precedence_rank'] = 50;
        $invalid['target_subdomains'] = ['B.02'];
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Precedence 50 without wildcard subdomains is rejected");

        $invalid = RdeiSchemaFixtures::getValidSubdomainOverlay();
        $invalid['precedence_rank'] = 100;
        $invalid['target_subdomains'] = ['*'];
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Precedence 100 with wildcard subdomain is rejected");

        foreach (['1.0', 'v1.0.0', '01.0.0', '1.0.0.0', 'beta'] as $badVer) {
            $invalid = $base;
            $invalid['version'] = $badVer;
            Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "Malformed SemVer '{$badVer}' is rejected");
        }
        $validVer = $base;
        $validVer['version'] = '2.1.0-alpha.1';
        Assert::assertEquals(0, count(self::$validator->validateEvidenceOverlay($validVer)), "SemVer with pre-release tag '2.1.0-alpha.1' accepted");
    }

    private static function testGlobalDomainAndPolicyIdentityConstraints(): void
    {
        // 1. Valid GLOBAL base policy accepted
        $validGlobal = RdeiSchemaFixtures::getGlobalBasePolicyExample();
        Assert::assertEquals(0, count(self::$validator->validateEvidenceOverlay($validGlobal)), "Valid GLOBAL base policy passes");

        // 2. GLOBAL with rank 50 rejected
        $badRank50 = $validGlobal;
        $badRank50['precedence_rank'] = 50;
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($badRank50)) > 0, "GLOBAL domain with precedence 50 is rejected");

        // 3. GLOBAL with rank 100 rejected
        $badRank100 = $validGlobal;
        $badRank100['precedence_rank'] = 100;
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($badRank100)) > 0, "GLOBAL domain with precedence 100 is rejected");

        // 4. Precedence rank 0 with regular Domain A/B rejected
        $badDomain0 = $validGlobal;
        $badDomain0['target_domain'] = 'A';
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($badDomain0)) > 0, "Precedence rank 0 with regular domain 'A' is rejected");

        // 5. pol-base-global with regular Domain B rejected
        $badIdDomain = RdeiSchemaFixtures::getValidDomainOverlay();
        $badIdDomain['policy_id'] = 'pol-base-global';
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($badIdDomain)) > 0, "pol-base-global with domain 'B' is rejected");

        // 6. pol-overlay-* with GLOBAL rejected
        $badOverlayGlobal = $validGlobal;
        $badOverlayGlobal['policy_id'] = 'pol-overlay-sec-global';
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($badOverlayGlobal)) > 0, "pol-overlay-* with domain 'GLOBAL' is rejected");
    }

    private static function testOverlayFreshnessConsistency(): void
    {
        $base = RdeiSchemaFixtures::getValidDomainOverlay();

        $invalid = $base;
        $invalid['freshness_overrides']['default_ttl_hours'] = 200;
        $invalid['freshness_overrides']['max_ttl_hours'] = 100;
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "default_ttl_hours > max_ttl_hours is rejected");

        $invalid = $base;
        $invalid['freshness_overrides']['default_ttl_hours'] = 24;
        $invalid['freshness_overrides']['max_ttl_hours'] = 168;
        $invalid['freshness_overrides']['emergency_ttl_hours'] = 48;
        Assert::assertTrue(count(self::$validator->validateEvidenceOverlay($invalid)) > 0, "emergency_ttl_hours > default_ttl_hours is rejected");
    }

    private static function testPolicyGraphInheritanceAndCycles(): void
    {
        $overlayA = RdeiSchemaFixtures::getValidDomainOverlay();
        $overlayB = RdeiSchemaFixtures::getValidSubdomainOverlay();

        // 1. Valid acyclic graph passes
        Assert::assertEquals(0, count(self::$validator->validatePolicyGraph([$overlayA, $overlayB])), "Acyclic policy graph validates successfully");

        // 2. Direct self-inheritance rejected
        $selfInherit = $overlayA;
        $selfInherit['parent_policy_id'] = $selfInherit['policy_id'];
        Assert::assertTrue(count(self::$validator->validatePolicyGraph([$selfInherit])) > 0, "Direct self-inheritance detected and rejected");

        // 3. Missing parent policy rejected
        $missingParent = $overlayB;
        $missingParent['parent_policy_id'] = 'pol-overlay-nonexistent';
        Assert::assertTrue(count(self::$validator->validatePolicyGraph([$overlayA, $missingParent])) > 0, "Missing parent policy detected and rejected");

        // 4. Two-policy cycle A -> B -> A rejected
        $cycleA = $overlayA;
        $cycleB = $overlayB;
        $cycleA['parent_policy_id'] = $cycleB['policy_id'];
        $cycleB['parent_policy_id'] = $cycleA['policy_id'];
        Assert::assertTrue(count(self::$validator->validatePolicyGraph([$cycleA, $cycleB])) > 0, "Two-policy cycle A -> B -> A detected and rejected");

        // 5. Three-policy cycle A -> B -> C -> A rejected
        $pA = $overlayA;
        $pB = $overlayB;
        $pC = $overlayB;
        $pA['policy_id'] = 'pol-overlay-node-a';
        $pB['policy_id'] = 'pol-overlay-node-b';
        $pC['policy_id'] = 'pol-overlay-node-c';
        $pA['parent_policy_id'] = 'pol-overlay-node-b';
        $pB['parent_policy_id'] = 'pol-overlay-node-c';
        $pC['parent_policy_id'] = 'pol-overlay-node-a';
        Assert::assertTrue(count(self::$validator->validatePolicyGraph([$pA, $pB, $pC])) > 0, "Three-policy cycle A -> B -> C -> A detected and rejected");
    }

    private static function testCanonicalExamples(): void
    {
        Assert::assertEquals(0, count(self::$validator->validateSourceDefinition(RdeiSchemaFixtures::getNvdExample())), "Canonical NVD example validates with 0 errors");
        Assert::assertEquals(0, count(self::$validator->validateSourceDefinition(RdeiSchemaFixtures::getEurLexExample())), "Canonical EUR-Lex example validates with 0 errors");
        Assert::assertEquals(0, count(self::$validator->validateEvidenceOverlay(RdeiSchemaFixtures::getGlobalBasePolicyExample())), "Canonical Global Base Policy validates with 0 errors");
    }
}
