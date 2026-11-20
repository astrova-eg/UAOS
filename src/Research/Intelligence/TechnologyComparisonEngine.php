<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use UniversalAgentOS\Research\Models\ComparisonReport;
use UniversalAgentOS\Research\Models\ExtractedClaim;

/**
 * Multi-criteria technology comparison engine evaluating engineering alternatives using verified evidence.
 */
class TechnologyComparisonEngine
{
    public const CRITERIA_SECURITY_POSTURE = 'security_posture';
    public const CRITERIA_UPSTREAM_STABILITY = 'upstream_stability';
    public const CRITERIA_TARGET_COMPATIBILITY = 'target_compatibility';
    public const CRITERIA_MAINTENANCE_ACTIVITY = 'maintenance_activity';
    public const CRITERIA_RELEASE_MATURITY = 'release_maturity';
    public const CRITERIA_PERFORMANCE = 'performance_characteristics';
    public const CRITERIA_SCALABILITY = 'scalability';
    public const CRITERIA_DEVELOPER_EXPERIENCE = 'developer_experience';
    public const CRITERIA_DOCUMENTATION_QUALITY = 'documentation_quality';
    public const CRITERIA_ECOSYSTEM_COMMUNITY = 'ecosystem_community';
    public const CRITERIA_SOFTWARE_LICENSING = 'software_licensing';
    public const CRITERIA_OPERATIONAL_COMPLEXITY = 'operational_complexity';
    public const CRITERIA_SUSTAINABILITY = 'long_term_sustainability';
    public const CRITERIA_VENDOR_LOCK_IN = 'vendor_lock_in';
    public const CRITERIA_MIGRATION_COSTS = 'migration_upgrade_costs';
    public const CRITERIA_TCO = 'total_cost_of_ownership';
    public const CRITERIA_PROJECT_CONSTRAINTS = 'project_constraints';

    public const ALL_CRITERIA = [
        self::CRITERIA_SECURITY_POSTURE, self::CRITERIA_UPSTREAM_STABILITY, self::CRITERIA_TARGET_COMPATIBILITY,
        self::CRITERIA_MAINTENANCE_ACTIVITY, self::CRITERIA_RELEASE_MATURITY, self::CRITERIA_PERFORMANCE,
        self::CRITERIA_SCALABILITY, self::CRITERIA_DEVELOPER_EXPERIENCE, self::CRITERIA_DOCUMENTATION_QUALITY,
        self::CRITERIA_ECOSYSTEM_COMMUNITY, self::CRITERIA_SOFTWARE_LICENSING, self::CRITERIA_OPERATIONAL_COMPLEXITY,
        self::CRITERIA_SUSTAINABILITY, self::CRITERIA_VENDOR_LOCK_IN, self::CRITERIA_MIGRATION_COSTS,
        self::CRITERIA_TCO, self::CRITERIA_PROJECT_CONSTRAINTS,
    ];

    public const NATURE_VERIFIED_FACT = 'VERIFIED_FACT';
    public const NATURE_SOURCED_CLAIM = 'SOURCED_CLAIM';
    public const NATURE_ASSUMPTION = 'ASSUMPTION';
    public const NATURE_UNAVAILABLE = 'UNAVAILABLE';

    /**
     * Executes a structured comparison of candidate technologies.
     *
     * @param string $comparisonId Unique comparison ID
     * @param string $domain Domain name (e.g. database_driver)
     * @param array<int, string> $candidates Candidate alternative names
     * @param array<string, array<string, mixed>> $evidenceByCandidate Evidence mapped by candidate name
     * @param array<string, float> $criteriaWeights Optional custom criteria weights
     * @param array<string, mixed> $activeProfile Active target project profile
     */
    public function compare(
        string $comparisonId,
        string $domain,
        array $candidates,
        array $evidenceByCandidate,
        array $criteriaWeights = [],
        array $activeProfile = []
    ): ComparisonReport {
        if (count($candidates) < 2) {
            throw new InvalidArgumentException('Technology comparison mandates at least 2 candidates.');
        }

        $now = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
        $weights = $this->normalizeWeights($criteriaWeights);

        $evaluations = [];
        $totalEvidenceCount = 0;
        $incomparableReasons = [];
        $limitations = [];

        foreach ($candidates as $candidate) {
            $candidateEvidence = $evidenceByCandidate[$candidate] ?? [];
            $res = $this->evaluateCandidate($candidate, $candidateEvidence, $weights, $activeProfile);
            $evaluations[$candidate] = $res['evaluation'];
            $totalEvidenceCount += $res['evidence_count'];
            if (!empty($res['incomparable_reason'])) {
                $incomparableReasons[] = $res['incomparable_reason'];
            }
            if (!empty($res['limitations'])) {
                $limitations = array_merge($limitations, $res['limitations']);
            }
        }

        // Determine overall status
        $status = ComparisonReport::STATUS_COMPLETE;
        if (!empty($incomparableReasons)) {
            $status = ComparisonReport::STATUS_INCOMPARABLE_ALTERNATIVES;
            $limitations[] = 'Incomparable candidates detected: ' . implode('; ', $incomparableReasons);
        } elseif ($totalEvidenceCount === 0) {
            $status = ComparisonReport::STATUS_INSUFFICIENT_EVIDENCE;
            $limitations[] = 'Insufficient evidence: Zero verified claims available across candidates. No scores fabricated.';
        }

        $recommendation = $this->buildRecommendation($candidates, $evaluations, $status, $limitations);

        return new ComparisonReport(
            comparisonId: $comparisonId,
            domain: $domain,
            evaluatedAt: $now,
            candidates: $candidates,
            criteriaWeights: $weights,
            candidateEvaluations: $evaluations,
            recommendation: $recommendation,
            limitations: array_values(array_unique($limitations)),
            status: $status
        );
    }

    /**
     * @param array<string, mixed> $candidateEvidence
     * @param array<string, float> $weights
     * @param array<string, mixed> $activeProfile
     * @return array<string, mixed>
     */
    private function evaluateCandidate(string $name, array $candidateEvidence, array $weights, array $activeProfile): array
    {
        $scores = [];
        $criterionEvidence = [];
        $pros = [];
        $cons = [];
        $refs = [];
        $evidenceCount = 0;
        $limitations = [];
        $incomparable = null;

        foreach ($weights as $criterion => $w) {
            $data = $candidateEvidence[$criterion] ?? null;
            if ($data === null) {
                $criterionEvidence[$criterion] = [
                    'nature' => self::NATURE_UNAVAILABLE,
                    'score' => null,
                    'detail' => 'No verified evidence provided for this criterion.',
                ];
                continue;
            }

            $score = isset($data['score']) ? (float)$data['score'] : null;
            $nature = $data['nature'] ?? self::NATURE_SOURCED_CLAIM;
            $detail = $data['detail'] ?? '';
            $claimRefs = $data['evidence_references'] ?? [];

            if ($score !== null) {
                $scores[$criterion] = $score;
                $evidenceCount++;
            }

            $criterionEvidence[$criterion] = [
                'nature' => $nature,
                'score' => $score,
                'detail' => $detail,
                'references' => $claimRefs,
            ];

            foreach ($claimRefs as $ref) $refs[] = $ref;
            if (!empty($data['pros'])) {
                foreach ((array)$data['pros'] as $p) $pros[] = (string)$p;
            }
            if (!empty($data['cons'])) {
                foreach ((array)$data['cons'] as $c) $cons[] = (string)$c;
            }
        }

        // Check compatibility with active profile
        if (!empty($activeProfile['php_version']) && isset($candidateEvidence['php_min_version'])) {
            $req = (string)$candidateEvidence['php_min_version'];
            if (version_compare((string)$activeProfile['php_version'], $req, '<')) {
                $incomparable = "Candidate '{$name}' requires PHP {$req}, which exceeds active target {$activeProfile['php_version']}.";
            }
        }

        // Calculate weighted score without fabricating missing criteria
        $weightedScore = null;
        if (!empty($scores)) {
            $sumScore = 0.0;
            $sumWeight = 0.0;
            foreach ($scores as $crit => $val) {
                $w = $weights[$crit] ?? 0.0;
                $sumScore += $val * $w;
                $sumWeight += $w;
            }
            if ($sumWeight > 0.0) {
                $weightedScore = round($sumScore / $sumWeight, 2);
            }
        }

        return [
            'evidence_count' => $evidenceCount,
            'incomparable_reason' => $incomparable,
            'limitations' => $limitations,
            'evaluation' => [
                'scores' => $scores,
                'criterion_breakdown' => $criterionEvidence,
                'weighted_score' => $weightedScore,
                'pros' => array_values(array_unique($pros)),
                'cons' => array_values(array_unique($cons)),
                'evidence_references' => array_values(array_unique($refs)),
            ],
        ];
    }

    /**
     * @param array<int, string> $candidates
     * @param array<string, array<string, mixed>> $evaluations
     * @param array<int, string> $limitations
     * @return array<string, mixed>
     */
    private function buildRecommendation(array $candidates, array $evaluations, string $status, array $limitations): array
    {
        if ($status !== ComparisonReport::STATUS_COMPLETE) {
            return [
                'selected' => null,
                'rationale' => "Cannot recommend candidate due to status '{$status}'. Review recorded limitations.",
                'rejected_alternatives' => array_map(fn($c) => ['name' => $c, 'rejection_reason' => 'Comparison inconclusive'], $candidates),
            ];
        }

        $bestCandidate = null;
        $bestScore = -1.0;
        foreach ($candidates as $c) {
            $score = $evaluations[$c]['weighted_score'] ?? null;
            if ($score !== null && $score > $bestScore) {
                $bestScore = $score;
                $bestCandidate = $c;
            }
        }

        if ($bestCandidate === null) {
            return [
                'selected' => null,
                'rationale' => 'Inconclusive: No candidate achieved verifiable score.',
                'rejected_alternatives' => [],
            ];
        }

        $rejected = [];
        foreach ($candidates as $c) {
            if ($c !== $bestCandidate) {
                $score = $evaluations[$c]['weighted_score'] ?? 'unscored';
                $rejected[] = [
                    'name' => $c,
                    'rejection_reason' => "Weighted score ({$score}) lower than selected {$bestCandidate} ({$bestScore}) based on verified evidence.",
                ];
            }
        }

        return [
            'selected' => $bestCandidate,
            'rationale' => "Candidate '{$bestCandidate}' demonstrated highest weighted score ({$bestScore}) across verified evidence criteria.",
            'rejected_alternatives' => $rejected,
        ];
    }

    /**
     * @param array<string, float> $weights
     * @return array<string, float>
     */
    private function normalizeWeights(array $weights): array
    {
        if (empty($weights)) {
            return [
                self::CRITERIA_SECURITY_POSTURE => 0.25,
                self::CRITERIA_TARGET_COMPATIBILITY => 0.25,
                self::CRITERIA_PERFORMANCE => 0.20,
                self::CRITERIA_OPERATIONAL_COMPLEXITY => 0.15,
                self::CRITERIA_MAINTENANCE_ACTIVITY => 0.15,
            ];
        }

        $sum = array_sum($weights);
        if ($sum <= 0.0) {
            throw new InvalidArgumentException('Sum of criteria weights must be greater than zero.');
        }

        $normalized = [];
        foreach ($weights as $k => $v) {
            if ($v < 0.0) {
                throw new InvalidArgumentException("Weight for '{$k}' cannot be negative: {$v}");
            }
            $normalized[$k] = round($v / $sum, 4);
        }
        return $normalized;
    }
}
