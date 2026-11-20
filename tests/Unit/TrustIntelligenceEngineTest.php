<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Intelligence\TrustIntelligenceEngine;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TrustPolicyConfiguration;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class TrustIntelligenceEngineTest
{
    public static function run(): void
    {
        $engine = new TrustIntelligenceEngine();
        $fixture = RdeiSchemaFixtures::getValidSourceDef();

        // 1. Authority Tier scoring across all 6 tiers
        $expectedTiers = [
            1 => 1.00,
            2 => 0.85,
            3 => 0.70,
            4 => 0.55,
            5 => 0.30,
            6 => 0.05,
        ];
        foreach ($expectedTiers as $tier => $expectedScore) {
            $def = $fixture;
            $def['authority_tier'] = $tier;
            $src = SourceDefinition::fromArray($def);
            Assert::assertEquals($expectedScore, $engine->calculateAuthorityScore($src), "Tier {$tier} maps to {$expectedScore}");
        }

        // 2. High confidence evaluation for Tier 1 source with fresh evidence and full verification
        $t1Data = $fixture;
        $t1Data['authority_tier'] = 1;
        $t1Data['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', time() - 3600); // 1h ago
        $t1Data['access_mechanisms'][0]['verification_status'] = 'fully_tested';
        $t1Data['access_mechanisms'][0]['verified_at'] = $t1Data['last_verified_at'];
        $t1Src = SourceDefinition::fromArray($t1Data);

        $trustRes = $engine->evaluateSourceTrust($t1Src, ['reference_time' => time()]);
        Assert::assertTrue($trustRes->compositeScore >= 0.85, "Tier 1 fully tested source achieves HIGH confidence score (>= 0.85)");
        Assert::assertEquals('HIGH', $trustRes->confidenceTier, "Confidence tier is HIGH");
        Assert::assertCount(0, $trustRes->missingFactors, "No missing factors for complete definition");

        // 3. Explainability: factor explanations and policy version
        Assert::assertEquals(TrustPolicyConfiguration::DEFAULT_POLICY_VERSION, $trustRes->policyVersion, "Policy version recorded");
        Assert::assertTrue(isset($trustRes->factorExplanations['authority']), "Authority explanation present");
        Assert::assertTrue(isset($trustRes->factorExplanations['freshness']), "Freshness explanation present");
        Assert::assertTrue(isset($trustRes->factorExplanations['verification_status']), "Verification status explanation present");

        // 4. Missing evidence handled safely (not negative, not elevated)
        $incomplete = $fixture;
        $incomplete['classification_evidence'] = '';
        $incomplete['owner'] = [];
        $incomplete['access_mechanisms'] = [];
        $incSrc = SourceDefinition::fromArray($incomplete);
        $incRes = $engine->evaluateSourceTrust($incSrc, ['reference_time' => time()]);
        Assert::assertTrue(in_array('classification_evidence', $incRes->missingFactors, true), "Records missing classification evidence");
        Assert::assertTrue(in_array('owner_institution', $incRes->missingFactors, true), "Records missing owner institution");
        Assert::assertTrue($incRes->compositeScore < $trustRes->compositeScore, "Missing evidence reduces overall trust score safely");

        // 5. Conflict penalty application
        $unreachable = $fixture;
        $unreachable['status'] = SourceDefinition::STATUS_UNREACHABLE;
        $unreachSrc = SourceDefinition::fromArray($unreachable);
        $unreachRes = $engine->evaluateSourceTrust($unreachSrc, ['reference_time' => time()]);
        Assert::assertEquals(0.50, $unreachRes->conflictPenalty, "Unreachable source incurs 0.50 penalty");

        // 6. ExtractedClaim TrustEvaluatorInterface contract integration
        $claim = new ExtractedClaim(
            claimId: 'CLM-TRUST-01',
            statement: 'PHP 8.2 introduced readonly classes',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.95,
            applicableVersions: ['8.2', '8.3'],
            citations: [['source_id' => $t1Src->sourceId, 'content_hash' => hash('sha256', 'sample'), 'quote_segment' => 'sample']]
        );

        $claimConf = $engine->evaluateClaimConfidence($claim, $t1Src, [
            'reference_time' => time(),
            'corroborating_sources' => ['src-corroborator-01', 'src-corroborator-02'],
        ]);
        Assert::assertTrue($claimConf >= 0.85, "Claim confidence evaluated above 0.85 with corroborating sources");

        $state = $engine->classifyEvidentialState($claim, $t1Src, [
            'reference_time' => time(),
            'corroborating_sources' => ['src-corroborator-01', 'src-corroborator-02'],
        ]);
        Assert::assertEquals(ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, $state, "State is VERIFIED_AUTHORITATIVE");

        // 7. Determinism: identical inputs produce identical scores
        $res1 = $engine->evaluateSourceTrust($t1Src, ['reference_time' => 1700000000]);
        $res2 = $engine->evaluateSourceTrust($t1Src, ['reference_time' => 1700000000]);
        Assert::assertEquals($res1->compositeScore, $res2->compositeScore, "Evaluation is 100% deterministic");
        Assert::assertEquals($res1->confidenceTier, $res2->confidenceTier, "Confidence tier is 100% deterministic");
    }
}
