<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Models;

use JsonSerializable;

/**
 * Immutable audit log entry recording lifecycle, validation, or evaluation events.
 */
readonly class SourceAuditRecord implements JsonSerializable
{
    public const ACTION_REGISTER = 'register';
    public const ACTION_UPDATE = 'update';
    public const ACTION_TRANSITION = 'lifecycle_transition';
    public const ACTION_VALIDATION = 'validation';
    public const ACTION_TRUST_EVALUATION = 'trust_evaluation';
    public const ACTION_FRESHNESS_EVALUATION = 'freshness_evaluation';
    public const ACTION_ELIGIBILITY_EVALUATION = 'eligibility_evaluation';
    public const ACTION_VERSION_CHANGE = 'version_change';

    public function __construct(
        public string $recordId,
        public string $sourceId,
        public string $action,
        public string $timestamp,
        public string $actor = 'system',
        public ?string $fromState = null,
        public ?string $toState = null,
        public string $reason = '',
        public array $details = []
    ) {}

    public static function create(
        string $sourceId,
        string $action,
        string $actor = 'system',
        ?string $fromState = null,
        ?string $toState = null,
        string $reason = '',
        array $details = []
    ): self {
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $recordId = 'AUD-' . hash('xxh128', $sourceId . $action . $timestamp . random_bytes(4));

        return new self(
            recordId: $recordId,
            sourceId: $sourceId,
            action: $action,
            timestamp: $timestamp,
            actor: $actor,
            fromState: $fromState,
            toState: $toState,
            reason: $reason,
            details: $details
        );
    }

    public function toArray(): array
    {
        return [
            'record_id' => $this->recordId,
            'source_id' => $this->sourceId,
            'action' => $this->action,
            'timestamp' => $this->timestamp,
            'actor' => $this->actor,
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'reason' => $this->reason,
            'details' => $this->details,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            recordId: (string)($data['record_id'] ?? ''),
            sourceId: (string)($data['source_id'] ?? ''),
            action: (string)($data['action'] ?? ''),
            timestamp: (string)($data['timestamp'] ?? ''),
            actor: (string)($data['actor'] ?? 'system'),
            fromState: isset($data['from_state']) ? (string)$data['from_state'] : null,
            toState: isset($data['to_state']) ? (string)$data['to_state'] : null,
            reason: (string)($data['reason'] ?? ''),
            details: (array)($data['details'] ?? [])
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
