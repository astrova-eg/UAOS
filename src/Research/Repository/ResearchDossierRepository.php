<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Repository;

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Runtime\PathResolver;

/**
 * High-integrity persistent repository for immutable, sealed Research Dossiers.
 *
 * Enforces atomic disk writes, strict storage root containment, cryptographic integrity
 * verification, and dual-tier SQLite cache indexing with automatic directory fallback.
 */
class ResearchDossierRepository implements DossierRepositoryInterface
{
    private string $storageDir;
    private string $dossiersDir;
    private string $locksDir;
    private ?SQLiteKnowledgeStore $knowledgeStore;

    public function __construct(
        ?PathResolverInterface $pathResolver = null,
        ?string $storageDir = null,
        ?SQLiteKnowledgeStore $knowledgeStore = null,
        bool $enableKnowledgeStore = true
    ) {
        $this->storageDir = ($storageDir !== null && trim($storageDir) !== '')
            ? rtrim(str_replace('/', DIRECTORY_SEPARATOR, $storageDir), DIRECTORY_SEPARATOR)
            : ($pathResolver ?? new PathResolver())->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'research';

        $this->dossiersDir = $this->storageDir . DIRECTORY_SEPARATOR . 'dossiers';
        $this->locksDir = $this->storageDir . DIRECTORY_SEPARATOR . 'locks';

        if (!is_dir($this->dossiersDir)) { @mkdir($this->dossiersDir, 0755, true); }
        if (!is_dir($this->locksDir)) { @mkdir($this->locksDir, 0755, true); }

        if (!$enableKnowledgeStore) {
            $this->knowledgeStore = null;
        } elseif ($knowledgeStore !== null) {
            $this->knowledgeStore = $knowledgeStore;
        } else {
            $sqlitePath = $this->storageDir . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'research_cache.sqlite';
            $this->knowledgeStore = new SQLiteKnowledgeStore($sqlitePath);
            if (!$this->knowledgeStore->isAvailable()) { $this->knowledgeStore = null; }
        }
    }

    public function save(ResearchDossier $dossier): string
    {
        $this->assertSafeIdentifier($dossier->dossierId, 'dossierId');
        $this->assertSafeIdentifier($dossier->taskId, 'taskId');

        $targetPath = $this->resolveDossierPath($dossier->dossierId);
        $lockPath = $this->resolveLockPath($dossier->dossierId);

        try {
            $json = json_encode(
                $dossier->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException("Serialization failure for dossier '{$dossier->dossierId}': " . $e->getMessage(), 0, $e);
        }

        $lockFp = @fopen($lockPath, 'cb');
        if ($lockFp === false || !flock($lockFp, LOCK_EX)) {
            if ($lockFp !== false) { fclose($lockFp); }
            throw new RuntimeException("Storage lock failure persisting dossier: '{$dossier->dossierId}'.");
        }

        $tempFile = $this->dossiersDir . DIRECTORY_SEPARATOR . '.' . $dossier->dossierId . '.' . bin2hex(random_bytes(8)) . '.tmp';

        try {
            $fp = @fopen($tempFile, 'wb');
            if ($fp === false) {
                throw new RuntimeException("Storage I/O failure persisting dossier: '{$dossier->dossierId}'.");
            }
            try {
                $written = fwrite($fp, $json);
                if ($written === false || $written < strlen($json)) {
                    throw new RuntimeException("Storage I/O incomplete write for dossier: '{$dossier->dossierId}'.");
                }
                fflush($fp);
            } finally {
                fclose($fp);
            }

            @chmod($tempFile, 0644);

            $this->publishFile($tempFile, $targetPath, $dossier->dossierId);

            if ($this->knowledgeStore !== null && $this->knowledgeStore->isAvailable()) {
                try {
                    $this->knowledgeStore->indexDossier($dossier, $dossier->dossierId . '.json');
                } catch (\Throwable) {
                    // Secondary index failure does not invalidate canonical primary storage write
                }
            }
        } finally {
            if (is_file($tempFile)) { @unlink($tempFile); }
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }

        return $dossier->dossierId;
    }

    public function get(string $dossierId): ?ResearchDossier
    {
        $targetFile = $this->resolveDossierPath($dossierId);
        $lockPath = $this->resolveLockPath($dossierId);

        $lockFp = @fopen($lockPath, 'cb');
        if ($lockFp !== false) { @flock($lockFp, LOCK_SH); }

        try {
            if (!file_exists($targetFile)) { return null; }
            $contents = @file_get_contents($targetFile);
            if ($contents === false) {
                throw new RuntimeException("Storage access error reading dossier record: '{$dossierId}'.");
            }
        } finally {
            if ($lockFp !== false) {
                @flock($lockFp, LOCK_UN);
                @fclose($lockFp);
            }
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException("Stored dossier record '{$dossierId}' is malformed or corrupted.", 0, $e);
        }

        if (!is_array($data)) {
            throw new RuntimeException("Stored dossier record '{$dossierId}' is malformed or corrupted.");
        }

        try {
            $dossier = ResearchDossier::fromArray($data);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                "Stored dossier record '{$dossierId}' failed integrity verification or is corrupted: " . $e->getMessage(),
                0,
                $e
            );
        }

        if ($dossier->isSealed() && !$dossier->verifyIntegrity()) {
            throw new InvalidArgumentException("Stored dossier record '{$dossierId}' failed cryptographic integrity verification.");
        }

        return $dossier;
    }

    public function findByTaskId(string $taskId): ?ResearchDossier
    {
        $this->assertSafeIdentifier($taskId, 'taskId');

        $candidateIds = [];
        if ($this->knowledgeStore !== null && $this->knowledgeStore->isAvailable()) {
            try {
                $candidateIds = $this->knowledgeStore->findDossierIdsByTaskId($taskId);
            } catch (\Throwable) {
                $candidateIds = [];
            }
        }

        if (empty($candidateIds)) {
            $candidateIds = $this->scanDossierIdsByTaskId($taskId);
        }

        foreach ($candidateIds as $candidateId) {
            try {
                $dossier = $this->get($candidateId);
                if ($dossier !== null && !$dossier->isExpired()) {
                    return $dossier;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    public function has(string $dossierId): bool
    {
        return is_file($this->resolveDossierPath($dossierId));
    }

    public function verifyIntegrity(ResearchDossier $dossier): bool
    {
        return $dossier->isSealed() && $dossier->verifyIntegrity();
    }

    public function getStorageDir(): string { return $this->storageDir; }
    public function getDossiersDir(): string { return $this->dossiersDir; }
    public function getLocksDir(): string { return $this->locksDir; }
    public function getKnowledgeStore(): ?SQLiteKnowledgeStore { return $this->knowledgeStore; }

    private function publishFile(string $tempFile, string $targetPath, string $dossierId): void
    {
        for ($attempt = 1; $attempt <= 10; $attempt++) {
            if (@rename($tempFile, $targetPath)) { return; }
            if ($attempt < 10) { usleep(20000); }
        }
        throw new RuntimeException("Atomic rename failed for dossier record: '{$dossierId}'.");
    }

    private function resolvePathInDir(string $dir, string $id, string $ext, string $type): string
    {
        $this->assertSafeIdentifier($id, $type);
        $path = $dir . DIRECTORY_SEPARATOR . $id . $ext;
        $normDir = str_replace('\\', '/', $dir);
        $normPath = str_replace('\\', '/', $path);
        if (!str_starts_with($normPath, rtrim($normDir, '/') . '/')) {
            throw new InvalidArgumentException("Storage containment boundary escape detected.");
        }
        return $path;
    }

    private function resolveDossierPath(string $dossierId): string
    {
        return $this->resolvePathInDir($this->dossiersDir, $dossierId, '.json', 'dossierId');
    }

    private function resolveLockPath(string $dossierId): string
    {
        return $this->resolvePathInDir($this->locksDir, $dossierId, '.lock', 'dossierId');
    }

    private function assertSafeIdentifier(string $id, string $type): void
    {
        if (trim($id) === '') {
            throw new InvalidArgumentException("Identifier {$type} cannot be empty.");
        }
        if (str_contains($id, "\0")) {
            throw new InvalidArgumentException("Identifier {$type} contains prohibited null byte.");
        }
        if (str_contains($id, '/') || str_contains($id, '\\') || str_contains($id, '..')) {
            throw new InvalidArgumentException("Identifier {$type} contains prohibited traversal tokens.");
        }
        if (preg_match('/(%00|%2e%2e|::\$DATA|~[0-9])/i', $id) === 1 || preg_match('/^[a-zA-Z0-9_\-]+$/', $id) !== 1) {
            throw new InvalidArgumentException("Identifier {$type} contains prohibited characters.");
        }
    }

    /**
     * @return array<int, string> Candidate dossier IDs ordered by researched_at DESC
     */
    private function scanDossierIdsByTaskId(string $taskId): array
    {
        if (!is_dir($this->dossiersDir) || ($files = scandir($this->dossiersDir)) === false) {
            return [];
        }

        $candidates = [];
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !str_ends_with($file, '.json') || str_starts_with($file, '.')) {
                continue;
            }

            $content = @file_get_contents($this->dossiersDir . DIRECTORY_SEPARATOR . $file);
            if ($content === false) { continue; }

            try {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($data) && ($data['task_id'] ?? null) === $taskId) {
                    $candidates[] = [
                        'dossier_id' => (string)($data['dossier_id'] ?? substr($file, 0, -5)),
                        'researched_at' => (string)($data['researched_at'] ?? ''),
                    ];
                }
            } catch (\Throwable) {
                continue;
            }
        }

        usort($candidates, static fn ($a, $b) => strcmp($b['researched_at'], $a['researched_at']));
        return array_column($candidates, 'dossier_id');
    }
}
