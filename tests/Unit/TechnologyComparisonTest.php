<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Intelligence\TechnologyComparisonEngine;
use UniversalAgentOS\Research\Models\ComparisonReport;
use UniversalAgentOS\Tests\Assert;

class TechnologyComparisonTest
{
    public static function run(): void
    {
        $engine = new TechnologyComparisonEngine();

        // 1. Evidence-backed comparison with structured scores, pros, cons, and citations
        $evidence = [
            'ext-redis' => [
                TechnologyComparisonEngine::CRITERIA_SECURITY_POSTURE => [
                    'score' => 9.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'detail' => 'Zero unpatched CVEs in last 24 months',
                    'evidence_references' => ['EV-REDIS-CVE-01'],
                    'pros' => ['Robust security record'],
                ],
                TechnologyComparisonEngine::CRITERIA_TARGET_COMPATIBILITY => [
                    'score' => 9.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'detail' => 'Full support for PHP 8.2 and 8.3',
                    'evidence_references' => ['EV-PHP-NET-EXT-REDIS'],
                ],
                TechnologyComparisonEngine::CRITERIA_PERFORMANCE => [
                    'score' => 10.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'detail' => 'C extension provides sub-millisecond roundtrips',
                    'evidence_references' => ['EV-BENCH-REDIS-01'],
                    'pros' => ['High throughput'],
                ],
                TechnologyComparisonEngine::CRITERIA_OPERATIONAL_COMPLEXITY => [
                    'score' => 7.0,
                    'nature' => TechnologyComparisonEngine::NATURE_SOURCED_CLAIM,
                    'detail' => 'Requires compilation or dll on Windows host',
                    'cons' => ['Host extension installation needed'],
                ],
                TechnologyComparisonEngine::CRITERIA_MAINTENANCE_ACTIVITY => [
                    'score' => 9.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'evidence_references' => ['EV-GH-EXT-REDIS'],
                ],
            ],
            'predis' => [
                TechnologyComparisonEngine::CRITERIA_SECURITY_POSTURE => [
                    'score' => 8.0,
                    'nature' => TechnologyComparisonEngine::NATURE_SOURCED_CLAIM,
                    'evidence_references' => ['EV-PREDIS-SEC-01'],
                ],
                TechnologyComparisonEngine::CRITERIA_TARGET_COMPATIBILITY => [
                    'score' => 10.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'detail' => 'Pure PHP package compatible with all environments',
                    'pros' => ['Zero native compilation required'],
                ],
                TechnologyComparisonEngine::CRITERIA_PERFORMANCE => [
                    'score' => 6.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'detail' => 'Higher CPU and memory overhead under high concurrency',
                    'evidence_references' => ['EV-BENCH-PREDIS-01'],
                    'cons' => ['Lower throughput under load'],
                ],
                TechnologyComparisonEngine::CRITERIA_OPERATIONAL_COMPLEXITY => [
                    'score' => 10.0,
                    'nature' => TechnologyComparisonEngine::NATURE_VERIFIED_FACT,
                    'pros' => ['Composer install and run immediately'],
                ],
                TechnologyComparisonEngine::CRITERIA_MAINTENANCE_ACTIVITY => [
                    'score' => 8.0,
                    'nature' => TechnologyComparisonEngine::NATURE_SOURCED_CLAIM,
                ],
            ],
        ];

        $report = $engine->compare(
            comparisonId: 'CMP-20261117-001',
            domain: 'redis_client_driver',
            candidates: ['ext-redis', 'predis'],
            evidenceByCandidate: $evidence,
            criteriaWeights: [
                TechnologyComparisonEngine::CRITERIA_SECURITY_POSTURE => 0.20,
                TechnologyComparisonEngine::CRITERIA_TARGET_COMPATIBILITY => 0.25,
                TechnologyComparisonEngine::CRITERIA_PERFORMANCE => 0.25,
                TechnologyComparisonEngine::CRITERIA_OPERATIONAL_COMPLEXITY => 0.15,
                TechnologyComparisonEngine::CRITERIA_MAINTENANCE_ACTIVITY => 0.15,
            ]
        );

        Assert::assertEquals('CMP-20261117-001', $report->comparisonId, 'Matches comparison ID');
        Assert::assertEquals(ComparisonReport::STATUS_COMPLETE, $report->status, 'Status is COMPLETE');
        Assert::assertEquals('ext-redis', $report->recommendation['selected'], 'ext-redis selected by score');
        Assert::assertTrue(!empty($report->recommendation['rejected_alternatives']), 'Includes rejected alternatives');

        // Check provenance preservation
        $evalRedis = $report->candidateEvaluations['ext-redis'];
        Assert::assertTrue(in_array('EV-REDIS-CVE-01', $evalRedis['evidence_references'], true), 'Preserves CVE citation');
        Assert::assertTrue(in_array('EV-BENCH-REDIS-01', $evalRedis['evidence_references'], true), 'Preserves benchmark citation');
        Assert::assertTrue(!empty($evalRedis['pros']), 'Preserves pros');
        Assert::assertTrue(!empty($evalRedis['cons']), 'Preserves cons');

        // 2. Unsupported scoring prevention (zero evidence -> STATUS_INSUFFICIENT_EVIDENCE)
        $noEvidenceReport = $engine->compare(
            comparisonId: 'CMP-20261117-002',
            domain: 'unknown_lib',
            candidates: ['lib-a', 'lib-b'],
            evidenceByCandidate: []
        );
        Assert::assertEquals(ComparisonReport::STATUS_INSUFFICIENT_EVIDENCE, $noEvidenceReport->status, 'Insufficient evidence status');
        Assert::assertTrue($noEvidenceReport->recommendation['selected'] === null, 'No candidate selected when evidence is insufficient');
        Assert::assertTrue($noEvidenceReport->candidateEvaluations['lib-a']['weighted_score'] === null, 'Zero score fabricated for unscored candidate');

        // 3. Incomparable alternatives detection (e.g. PHP version requirement mismatch)
        $incompEvidence = [
            'lib-future' => [
                'php_min_version' => '9.0',
                TechnologyComparisonEngine::CRITERIA_PERFORMANCE => ['score' => 9.0],
            ],
            'lib-current' => [
                'php_min_version' => '8.2',
                TechnologyComparisonEngine::CRITERIA_PERFORMANCE => ['score' => 8.0],
            ],
        ];
        $incompReport = $engine->compare(
            comparisonId: 'CMP-20261117-003',
            domain: 'parser',
            candidates: ['lib-future', 'lib-current'],
            evidenceByCandidate: $incompEvidence,
            activeProfile: ['php_version' => '8.2']
        );
        Assert::assertEquals(ComparisonReport::STATUS_INCOMPARABLE_ALTERNATIVES, $incompReport->status, 'Incomparable alternatives detected');
        Assert::assertTrue($incompReport->recommendation['selected'] === null, 'No candidate selected when alternatives incomparable');
        Assert::assertTrue(count($incompReport->limitations) >= 1, 'Records limitation explaining incompatibility');

        // 4. Invalid input verification (< 2 candidates throws exception)
        $singleCandidateCaught = false;
        try {
            $engine->compare('CMP-ERR', 'dom', ['only-one'], []);
        } catch (InvalidArgumentException) {
            $singleCandidateCaught = true;
        }
        Assert::assertTrue($singleCandidateCaught, 'Single candidate throws InvalidArgumentException');
    }
}
