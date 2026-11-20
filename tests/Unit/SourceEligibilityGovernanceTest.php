<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Intelligence\SourceEligibilityEvaluator;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;
use UniversalAgentOS\Research\Models\SourceLifecycleState;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class SourceEligibilityGovernanceTest
{
    public static function run(): void
    {
        $evaluator = new SourceEligibilityEvaluator();
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $refTime = time();

        // 1. High/Moderate trust score with fresh evidence is ELIGIBLE
        $validData = $fixture;
        $validData['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', $refTime - 3600); // 1h ago
        $validData['access_mechanisms'][0]['verified_at'] = $validData['last_verified_at'];
        $validData['access_mechanisms'][0]['verification_status'] = 'fully_tested';
        $validData['verification_profile']['connectivity_tested'] = true;
        $validData['verification_profile']['endpoint_verified'] = true;
        $validSource = SourceDefinition::fromArray($validData);

        $decision = $evaluator->evaluateEligibility($validSource, ['reference_time' => $refTime]);
        Assert::assertTrue($decision->isFullyEligible(), "Source with fresh evidence and high trust is ELIGIBLE");
        Assert::assertEquals(SourceEligibilityDecision::STATUS_ELIGIBLE, $decision->status, "Status is eligible");
        Assert::assertTrue($decision->isEligible, "is_eligible is true");

        // 2. Hard Policy: Revoked source is always STATUS_REVOKED regardless of tier
        $revokedDecision = $evaluator->evaluateEligibility($validSource, [
            'reference_time' => $refTime,
            'lifecycle_state' => SourceLifecycleState::STATE_REVOKED,
        ]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_REVOKED, $revokedDecision->status, "Revoked source is STATUS_REVOKED");
        Assert::assertFalse($revokedDecision->isEligible, "Revoked source is not eligible");
        Assert::assertTrue(count($revokedDecision->violations) > 0, "Revoked source records violation");

        // 3. Hard Policy: Suspended source is always STATUS_SUSPENDED
        $suspendedDecision = $evaluator->evaluateEligibility($validSource, [
            'reference_time' => $refTime,
            'lifecycle_state' => SourceLifecycleState::STATE_SUSPENDED,
        ]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_SUSPENDED, $suspendedDecision->status, "Suspended source is STATUS_SUSPENDED");
        Assert::assertFalse($suspendedDecision->isEligible, "Suspended source is not eligible");

        // 4. Hard Policy: Deprecated source is always STATUS_DEPRECATED
        $deprecatedSource = SourceDefinition::fromArray(array_merge($validData, ['status' => SourceDefinition::STATUS_DEPRECATED]));
        $depDecision = $evaluator->evaluateEligibility($deprecatedSource, ['reference_time' => $refTime]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_DEPRECATED, $depDecision->status, "Deprecated source is STATUS_DEPRECATED");
        Assert::assertFalse($depDecision->isEligible, "Deprecated source is not eligible");

        // 5. Hard Policy: Invalid schema definition fails into STATUS_INELIGIBLE
        $invalidData = $validData;
        $invalidData['category_type'] = 'invalid_category_type';
        $invalidSource = SourceDefinition::fromArray($invalidData);
        $invalidDecision = $evaluator->evaluateEligibility($invalidSource, ['reference_time' => $refTime]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_INELIGIBLE, $invalidDecision->status, "Schema invalid source is INELIGIBLE");
        Assert::assertFalse($invalidDecision->isEligible, "Invalid source is not eligible");

        // 6. Hard Policy: Expired evidence in critical domain (C = Security) triggers INELIGIBLE
        $expiredSecurity = $validData;
        $expiredSecurity['domains'] = ['C'];
        $expiredSecurity['subdomains'] = ['C.01'];
        $expiredSecurity['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', $refTime - (20 * 3600)); // 20h ago on 6h TTL
        $expiredSecurity['access_mechanisms'][0]['verified_at'] = $expiredSecurity['last_verified_at'];
        $expSecSource = SourceDefinition::fromArray($expiredSecurity);
        $expDecision = $evaluator->evaluateEligibility($expSecSource, ['reference_time' => $refTime]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_INELIGIBLE, $expDecision->status, "Expired security source is INELIGIBLE");
        Assert::assertFalse($expDecision->isEligible, "Expired security source is not eligible");

        // 7. Low trust score yields CONDITIONALLY_ELIGIBLE with mandatory corroboration condition
        $lowTrustData = $validData;
        $lowTrustData['authority_tier'] = 4; // Tier 4 (score 0.55 => composite in [0.50, 0.70) => LOW)
        $lowTrustData['classification_evidence'] = ''; // Missing classification evidence
        $lowTrustData['access_mechanisms'][0]['verification_status'] = 'untested';
        $lowTrustData['verification_profile']['connectivity_tested'] = false;
        $lowTrustData['verification_profile']['endpoint_verified'] = false;
        $lowTrustSource = SourceDefinition::fromArray($lowTrustData);
        $condDecision = $evaluator->evaluateEligibility($lowTrustSource, ['reference_time' => $refTime]);
        Assert::assertEquals(SourceEligibilityDecision::STATUS_CONDITIONALLY_ELIGIBLE, $condDecision->status, "Low trust yields CONDITIONALLY_ELIGIBLE");
        Assert::assertTrue($condDecision->isConditionallyEligible(), "isConditionallyEligible() is true");
        Assert::assertTrue(count($condDecision->conditions) > 0, "Records mandatory corroboration condition");
    }
}
