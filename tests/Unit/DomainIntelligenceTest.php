<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Intelligence\DomainIntelligenceEngine;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\DomainStrategy;
use UniversalAgentOS\Tests\Assert;

class DomainIntelligenceTest
{
    public static function run(): void
    {
        $engine = new DomainIntelligenceEngine();

        // 1. All 16 domains A-P exist with approved identities
        $all = $engine->getAllDomains();
        Assert::assertEquals(16, count($all), 'Exactly 16 domains registered (A-P)');

        $expectedLetters = range('A', 'P');
        foreach ($expectedLetters as $letter) {
            Assert::assertTrue(isset($all[$letter]), "Domain {$letter} exists in catalog");
            $strategy = $engine->getDomainStrategy($letter);
            Assert::assertTrue($strategy instanceof DomainStrategy, "Domain {$letter} returns DomainStrategy");
            Assert::assertEquals($letter, $strategy->domainCode, "Domain {$letter} has matching code");
            Assert::assertTrue(strlen($strategy->name) > 3, "Domain {$letter} has non-empty name");
            Assert::assertTrue(!empty($strategy->primarySourceCategories), "Domain {$letter} has primary source categories");
            Assert::assertTrue(in_array($strategy->minimumDepth, DepthProfile::VALID_LEVELS, true), "Domain {$letter} has valid min depth");
            Assert::assertTrue($strategy->minimumQuorum >= 2, "Domain {$letter} has min quorum >= 2");
            Assert::assertTrue($strategy->defaultTtlHours > 0, "Domain {$letter} has positive default TTL");
            Assert::assertTrue(!empty($strategy->verificationRules), "Domain {$letter} has verification rules");
        }

        // 2. Specific domain strategy verifications
        $stratA = $engine->getDomainStrategy('A');
        Assert::assertEquals('Software Engineering & Computer Science', $stratA->name, 'Domain A has approved name');
        Assert::assertEquals('pol-overlay-software-arch', $stratA->overlayPolicy, 'Domain A has software arch overlay');

        $stratB = $engine->getDomainStrategy('B');
        Assert::assertEquals('Cybersecurity & Information Security', $stratB->name, 'Domain B has approved name');
        Assert::assertEquals(6, $stratB->defaultTtlHours, 'Domain B has 6-hour TTL for CVEs');
        Assert::assertTrue(in_array('four_tier_vulnerability_applicability', $stratB->verificationRules, true), 'Domain B has 4-tier vuln rule');

        $stratC = $engine->getDomainStrategy('C');
        Assert::assertEquals(DepthProfile::LEVEL_L4, $stratC->minimumDepth, 'Domain C requires L4');
        Assert::assertTrue(in_array('standard_dominance_nist_rfc_iso', $stratC->verificationRules, true), 'Domain C has NIST/RFC dominance');

        $stratD = $engine->getDomainStrategy('D');
        Assert::assertEquals(DepthProfile::LEVEL_L4, $stratD->minimumDepth, 'Domain D requires L4');

        $stratH = $engine->getDomainStrategy('H');
        Assert::assertEquals('Medicine, Healthcare & Life Sciences', $stratH->name, 'Domain H has approved name');
        Assert::assertTrue(in_array('clinical_status_gating_no_preprints', $stratH->verificationRules, true), 'Domain H has clinical gating');

        $stratI = $engine->getDomainStrategy('I');
        Assert::assertEquals('Law, Regulations & Compliance', $stratI->name, 'Domain I has approved name');
        Assert::assertTrue(in_array('temporal_validity_in_force_sunset_check', $stratI->verificationRules, true), 'Domain I has in-force validity rule');

        // 3. Multi-domain tasks with strictness precedence
        $multi = $engine->resolveMultiDomainStrategy(['A', 'B', 'C']);
        Assert::assertEquals(['A', 'B', 'C'], $multi['resolved_domains'], 'Multi-domain resolves all 3 domains');
        Assert::assertEquals(DepthProfile::LEVEL_L4, $multi['minimum_depth'], 'Strictness selects highest depth (L4 from C)');
        Assert::assertEquals(3, $multi['minimum_quorum'], 'Strictness selects highest quorum (3 from B/C)');
        Assert::assertEquals(6, $multi['minimum_ttl_hours'], 'Strictness selects shortest TTL (6h from B)');
        Assert::assertTrue(count($multi['strictness_notes']) >= 2, 'Multi-domain includes strictness notes');
        Assert::assertTrue(in_array('four_tier_vulnerability_applicability', $multi['combined_verification_rules'], true), 'Multi-domain retains vuln rule');
        Assert::assertTrue(in_array('standard_dominance_nist_rfc_iso', $multi['combined_verification_rules'], true), 'Multi-domain retains NIST rule');

        // 4. Unknown domain handling
        $withUnknown = $engine->resolveMultiDomainStrategy(['A', 'Z', 'UNKNOWN']);
        Assert::assertEquals(['A'], $withUnknown['resolved_domains'], 'Resolves valid domains');
        Assert::assertEquals(['Z', 'UNKNOWN'], $withUnknown['unknown_domains'], 'Captures unknown domains explicitly');

        $allUnknown = $engine->resolveMultiDomainStrategy(['X', 'Y']);
        Assert::assertEquals(['A'], $allUnknown['resolved_domains'], 'Falls back safely to Domain A');
        Assert::assertEquals(['pol-base-global'], $allUnknown['applied_overlays'], 'Applies base global policy');

        // Direct get of unknown domain throws InvalidArgumentException
        $unknownCaught = false;
        try {
            $engine->getDomainStrategy('Z');
        } catch (InvalidArgumentException) {
            $unknownCaught = true;
        }
        Assert::assertTrue($unknownCaught, 'Unknown domain get throws InvalidArgumentException');

        // 5. Empty domain context safe handling
        $emptyResolution = $engine->resolveMultiDomainStrategy([]);
        Assert::assertEquals(['A'], $emptyResolution['resolved_domains'], 'Empty domain list safely defaults to Domain A');

        // 6. Natural language domain classification
        $hitsCrypto = $engine->classifyDomainsFromPrompt('Implement Argon2 password hashing and AES-256 cipher encryption');
        Assert::assertTrue(in_array('C', $hitsCrypto, true), 'Classified Domain C from crypto keywords');

        $hitsHealthSec = $engine->classifyDomainsFromPrompt('Clinical trial patient records and medical device vulnerability firewall');
        Assert::assertTrue(in_array('H', $hitsHealthSec, true), 'Classified Domain H from clinical');
        Assert::assertTrue(in_array('B', $hitsHealthSec, true), 'Classified Domain B from vulnerability');
    }
}
