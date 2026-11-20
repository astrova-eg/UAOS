<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Storage repository contract for persistent source definitions and audit trails.
 */
interface SourceRepositoryInterface
{
    /**
     * Persists a source definition atomically.
     *
     * @param SourceDefinition $source Source to persist
     * @return string Persisted file path or storage reference
     */
    public function save(SourceDefinition $source): string;

    /**
     * Retrieves a persisted source definition by source ID.
     */
    public function get(string $sourceId): ?SourceDefinition;

    /**
     * Checks if a source ID is persisted.
     */
    public function has(string $sourceId): bool;

    /**
     * Deletes or archives a persisted source definition.
     */
    public function remove(string $sourceId): bool;

    /**
     * Appends an audit record to the persistent audit log for the given source.
     */
    public function recordAudit(SourceAuditRecord $record): void;

    /**
     * Retrieves all audit records for a source.
     *
     * @return array<int, SourceAuditRecord>
     */
    public function getAuditHistory(string $sourceId): array;

    /**
     * Returns all stored source definitions matching criteria.
     *
     * @param array<string, mixed> $criteria Filter criteria
     * @return array<int, SourceDefinition>
     */
    public function all(array $criteria = []): array;

    /**
     * Gets the governed lifecycle state (active, suspended, deprecated, revoked, archived).
     */
    public function getLifecycleState(string $sourceId): string;

    /**
     * Sets the governed lifecycle state for a source.
     */
    public function setLifecycleState(string $sourceId, string $state): void;
}
