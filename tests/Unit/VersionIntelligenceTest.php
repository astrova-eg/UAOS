<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Intelligence\VersionIntelligenceEngine;
use UniversalAgentOS\Research\Models\VersionEvaluationResult;
use UniversalAgentOS\Tests\Assert;

class VersionIntelligenceTest
{
    public static function run(): void
    {
        $engine = new VersionIntelligenceEngine();

        // 1. Scheme Detection
        Assert::assertEquals(VersionEvaluationResult::SCHEME_SEMVER, $engine->detectScheme('8.2.0'), "8.2.0 is SemVer");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_SEMVER, $engine->detectScheme('v2.1.0-rc.1'), "v2.1.0-rc.1 is SemVer");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_CALVER, $engine->detectScheme('2026.11'), "2026.11 is CalVer");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_CALVER, $engine->detectScheme('2026.04.1'), "2026.04.1 is CalVer");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_DATE, $engine->detectScheme('2026-11-15'), "2026-11-15 is Date scheme");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_ORDINAL, $engine->detectScheme('4'), "4 is Ordinal");
        Assert::assertEquals(VersionEvaluationResult::SCHEME_UNKNOWN, $engine->detectScheme('random-string-version'), "random-string-version is Unknown");

        // 2. Deterministic Comparison
        Assert::assertEquals(0, $engine->compare('1.2.3', '1.2.3'), "1.2.3 == 1.2.3");
        Assert::assertEquals(-1, $engine->compare('1.2.3', '1.2.4'), "1.2.3 < 1.2.4");
        Assert::assertEquals(1, $engine->compare('2.0.0', '1.9.9'), "2.0.0 > 1.9.9");
        Assert::assertEquals(1, $engine->compare('v8.2.0', '8.1.0'), "v8.2.0 > 8.1.0 (strips prefix)");

        // 3. SemVer parsing breakdown
        $resSemver = $engine->evaluateVersion('8.2.14');
        Assert::assertTrue($resSemver->isSemVer(), "isSemVer() is true");
        Assert::assertEquals(8, $resSemver->parsedParts['major'], "Major is 8");
        Assert::assertEquals(2, $resSemver->parsedParts['minor'], "Minor is 2");
        Assert::assertEquals(14, $resSemver->parsedParts['patch'], "Patch is 14");
        Assert::assertTrue($resSemver->isSupported, "Standard version is supported");

        // 4. End-of-Life and Deprecation Detection
        $eolContext = [
            'eol_versions' => ['7.4.0', '8.0.0'],
            'deprecated_versions' => ['8.1.0'],
        ];

        $resEol = $engine->evaluateVersion('8.0.0', null, $eolContext);
        Assert::assertTrue($resEol->isEndOfLife(), "8.0.0 recognized as End-of-Life");
        Assert::assertFalse($resEol->isSupported, "EOL version is not supported");
        Assert::assertTrue($resEol->isOutdated, "EOL version marked outdated");

        $resDep = $engine->evaluateVersion('8.1.0', null, $eolContext);
        Assert::assertEquals(VersionEvaluationResult::STATE_DEPRECATED, $resDep->state, "8.1.0 recognized as deprecated");
        Assert::assertTrue($resDep->isOutdated, "Deprecated version marked outdated");

        // 5. Outdated Comparison against target version
        $targetContext = ['target_version' => '8.2.0'];
        $resOld = $engine->evaluateVersion('8.1.0', null, $targetContext);
        Assert::assertTrue($resOld->isOutdated, "8.1.0 is outdated relative to target 8.2.0");

        $resNew = $engine->evaluateVersion('8.3.0', null, $targetContext);
        Assert::assertFalse($resNew->isOutdated, "8.3.0 is not outdated relative to target 8.2.0");

        // 6. Safe handling of unknown versions
        $resUnknown = $engine->evaluateVersion('proprietary-alpha-preview');
        Assert::assertEquals(VersionEvaluationResult::SCHEME_UNKNOWN, $resUnknown->scheme, "Unknown scheme handled safely");
        Assert::assertEquals(VersionEvaluationResult::STATE_UNKNOWN, $resUnknown->state, "State is unknown");
        Assert::assertFalse($resUnknown->isOutdated, "Unknown version not assumed outdated");
    }
}
