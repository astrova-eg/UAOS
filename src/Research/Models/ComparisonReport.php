<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use InvalidArgumentException;
use JsonSerializable;

/**
 * Value object representing a structured technology comparison report.
 */
readonly class ComparisonReport implements JsonSerializable
{
    public const STATUS_COMPLETE = 'COMPLETE';
    public const STATUS_INSUFFICIENT_EVIDENCE = 'INSUFFICIENT_EVIDENCE';
    public const STATUS_INCOMPARABLE_ALTERNATIVES = 'INCOMPARABLE_ALTERNATIVES';

    public const VALID_STATUSES = [
        self::STATUS_COMPLETE,
        self::STATUS_INSUFFICIENT_EVIDENCE,
        self::STATUS_INCOMPARABLE_ALTERNATIVES,
    ];

    /**
     * @param string $comparisonId Unique comparison identifier (e.g. CMP-20261115-01)
     * @param string $domain Domain or technology category (e.g. redis_client_driver)
     * @param string $evaluatedAt ISO 8601 UTC timestamp of evaluation
     * @param array<int, string> $candidates Evaluated alternative names
     * @param array<string, float> $criteriaWeights Weights allocated per criterion (sum <= 1.0)
     * @param array<string, array<string, mixed>> $candidateEvaluations Detailed scores, pros, cons, evidence
     * @param array<string, mixed> $recommendation Selected candidate and rationale
     * @param array<int, string> $limitations Caveats, missing data, or uncertainties
     * @param string $status Overall comparison status
     */
    public function __construct(
        public string $comparisonId,
        public string $domain,
        public string $evaluatedAt,
        public array $candidates,
        public array $criteriaWeights,
        public array $candidateEvaluations,
        public array $recommendation,
        public array $limitations = [],
        public string $status = self::STATUS_COMPLETE
    ) {
        if (trim($this->comparisonId) === '') {
            throw new InvalidArgumentException('comparisonId cannot be empty.');
        }

        if (trim($this->domain) === '') {
            throw new InvalidArgumentException('domain cannot be empty.');
        }

        TimestampValidator::assertValid($this->evaluatedAt, 'evaluatedAt');

        if (count($this->candidates) < 2) {
            throw new InvalidArgumentException('At least two candidate alternatives are required for comparison.');
        }

        if (!in_array($this->status, self::VALID_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid comparison status '{$this->status}'.");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'comparison_id' => $this->comparisonId,
            'domain' => $this->domain,
            'evaluated_at' => $this->evaluatedAt,
            'candidates' => $this->candidates,
            'criteria_weights' => $this->criteriaWeights,
            'candidate_evaluations' => $this->candidateEvaluations,
            'recommendation' => $this->recommendation,
            'limitations' => $this->limitations,
            'status' => $this->status,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
