<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\ResearchDossier;

/**
 * Persistence, indexing, and cryptographic verification contract for Research Dossiers.
 *
 * Governs the persistence of immutable, sealed JSON research dossiers and index caching
 * strictly isolated within host workspace storage ({ProjectRoot}/.agent-os/research/).
 */
interface DossierRepositoryInterface
{
    /**
     * Persists a research dossier and returns its unique canonical dossier ID.
     *
     * @param ResearchDossier $dossier Sealed research dossier entity
     * @return string Canonical dossier identifier (e.g. DOS-20261115-4a7b9c)
     */
    public function save(ResearchDossier $dossier): string;

    /**
     * Retrieves a research dossier by its canonical dossier ID.
     *
     * @param string $dossierId Unique dossier identifier
     * @return ResearchDossier|null Dossier instance or null if not found
     */
    public function get(string $dossierId): ?ResearchDossier;

    /**
     * Retrieves the active valid research dossier associated with a specific task ID.
     *
     * @param string $taskId Unique task identifier
     * @return ResearchDossier|null Matching dossier or null if not found
     */
    public function findByTaskId(string $taskId): ?ResearchDossier;

    /**
     * Checks if a research dossier exists in persistent storage.
     *
     * @param string $dossierId Unique dossier identifier
     * @return bool True if dossier exists
     */
    public function has(string $dossierId): bool;

    /**
     * Verifies the cryptographic SHA-256 integrity hash of a dossier against its contents.
     *
     * @param ResearchDossier $dossier Research dossier entity
     * @return bool True if physical content hash matches recorded dossier_hash
     */
    public function verifyIntegrity(ResearchDossier $dossier): bool;
}
