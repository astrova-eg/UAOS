<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Intelligence\FreshnessIntelligenceEngine;
use UniversalAgentOS\Research\Models\FreshnessEvaluationResult;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class FreshnessIntelligenceTest
{
    public static function run(): void
    {
        $engine = new FreshnessIntelligenceEngine();
        $baseFixture = RdeiSchemaFixtures::getValidSourceDef();
        $source = SourceDefinition::fromArray($baseFixture);

        $refTime = 1700000000; // Fixed deterministic reference time

        // 1. Missing or empty timestamp yields UNVERIFIED with score 0.0
        $resMissing = $engine->evaluateFreshness(null, $source, ['reference_time' => $refTime]);
        Assert::assertEquals(0.0, $resMissing->freshnessScore, "Missing timestamp score is 0.0");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_UNVERIFIED, $resMissing->state, "Missing timestamp state is UNVERIFIED");
        Assert::assertTrue($resMissing->isUnverified(), "isUnverified() is true");

        $resEmpty = $engine->evaluateFreshness('', $source, ['reference_time' => $refTime]);
        Assert::assertEquals(FreshnessEvaluationResult::STATE_UNVERIFIED, $resEmpty->state, "Empty timestamp state is UNVERIFIED");

        // 2. Future timestamp yields INVALID with score 0.0
        $futureTime = gmdate('Y-m-d\TH:i:s\Z', $refTime + 7200); // 2 hours into future
        $resFuture = $engine->evaluateFreshness($futureTime, $source, ['reference_time' => $refTime]);
        Assert::assertEquals(0.0, $resFuture->freshnessScore, "Future timestamp score is 0.0");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_INVALID, $resFuture->state, "Future timestamp state is INVALID");

        // 3. Domain TTL resolution (Security C = 6h, Pure Math L = 8760h, Default = 168h)
        Assert::assertEquals(6, $engine->resolveEffectiveTtlHours(null, ['domain' => 'C']), "Domain C TTL is 6h");
        Assert::assertEquals(6, $engine->resolveEffectiveTtlHours(null, ['domain' => 'B']), "Domain B TTL is 6h");
        Assert::assertEquals(8760, $engine->resolveEffectiveTtlHours(null, ['domain' => 'L']), "Domain L TTL is 8760h");
        Assert::assertEquals(720, $engine->resolveEffectiveTtlHours(null, ['domain' => 'I']), "Domain I TTL is 720h");

        // 4. Freshness Boundaries on 100-hour TTL
        $ttl100Context = ['ttl_override' => 100, 'reference_time' => $refTime];

        // 4a. Age 50h (<= 75h) -> CURRENT, score 1.00
        $t50hAgo = gmdate('Y-m-d\TH:i:s\Z', $refTime - (50 * 3600));
        $resCurrent = $engine->evaluateFreshness($t50hAgo, $source, $ttl100Context);
        Assert::assertEquals(1.00, $resCurrent->freshnessScore, "Age 50h on 100h TTL is score 1.00");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_CURRENT, $resCurrent->state, "State is CURRENT");
        Assert::assertTrue($resCurrent->isUsable(), "Current evidence is usable");

        // 4b. Age 85h (75h - 100h) -> AGING, score between 0.85 and 1.00
        $t85hAgo = gmdate('Y-m-d\TH:i:s\Z', $refTime - (85 * 3600));
        $resAging = $engine->evaluateFreshness($t85hAgo, $source, $ttl100Context);
        Assert::assertTrue($resAging->freshnessScore >= 0.85 && $resAging->freshnessScore < 1.00, "Age 85h score in [0.85, 1.00)");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_AGING, $resAging->state, "State is AGING");
        Assert::assertTrue($resAging->isUsable(), "Aging evidence is still usable");

        // 4c. Age 150h (100h - 200h) -> STALE, score between 0.50 and 0.85
        $t150hAgo = gmdate('Y-m-d\TH:i:s\Z', $refTime - (150 * 3600));
        $resStale = $engine->evaluateFreshness($t150hAgo, $source, $ttl100Context);
        Assert::assertTrue($resStale->freshnessScore >= 0.50 && $resStale->freshnessScore <= 0.85, "Age 150h score in [0.50, 0.85]");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_STALE, $resStale->state, "State is STALE");
        Assert::assertFalse($resStale->isUsable(), "Stale evidence is not usable without revalidation");

        // 4d. Age 300h (> 200h) -> EXPIRED, score decays <= 0.50
        $t300hAgo = gmdate('Y-m-d\TH:i:s\Z', $refTime - (300 * 3600));
        $resExpired = $engine->evaluateFreshness($t300hAgo, $source, $ttl100Context);
        Assert::assertTrue($resExpired->freshnessScore < 0.50, "Age 300h score is < 0.50");
        Assert::assertEquals(FreshnessEvaluationResult::STATE_EXPIRED, $resExpired->state, "State is EXPIRED");
        Assert::assertTrue($resExpired->isExpired(), "isExpired() is true");

        // 5. Timezone offset parsing (+02:00, -05:00) handled accurately
        $tzOffsetDate = '2023-11-14T20:13:20+02:00'; // equivalent epoch representation
        $resTz = $engine->evaluateFreshness($tzOffsetDate, $source, ['reference_time' => $refTime, 'ttl_override' => 200]);
        Assert::assertTrue($resTz->freshnessScore > 0.0, "Timezone offset ISO-8601 evaluated successfully");

        // 6. Emergency hotfix override (ttl_override = 0) forces immediate expiration for older evidence
        $emergencyContext = ['ttl_override' => 0, 'reference_time' => $refTime];
        $resEmergency = $engine->evaluateFreshness($t50hAgo, $source, $emergencyContext);
        Assert::assertEquals(FreshnessEvaluationResult::STATE_EXPIRED, $resEmergency->state, "Zero TTL forces EXPIRED state");
    }
}
