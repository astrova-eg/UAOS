<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Repository;

use PDO;
use PDOException;
use UniversalAgentOS\Research\Models\ResearchDossier;

/**
 * High-performance query index and cache for Research Dossiers backed by SQLite.
 */
class SQLiteKnowledgeStore
{
    private string $sqlitePath;
    private ?PDO $pdo = null;

    public function __construct(string $sqlitePath)
    {
        $this->sqlitePath = $sqlitePath;
        $this->initialize();
    }

    public function isAvailable(): bool
    {
        return $this->pdo !== null;
    }

    public function getSqlitePath(): string
    {
        return $this->sqlitePath;
    }

    public function indexDossier(ResearchDossier $dossier, string $filePath): void
    {
        if ($this->pdo === null) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare(
                'INSERT OR REPLACE INTO research_index ('
                . 'dossier_id, task_id, project_identity, research_depth, researched_at, '
                . 'expires_at, gate_0_status, dossier_hash, is_sealed, file_path, indexed_at'
                . ') VALUES ('
                . ':dossier_id, :task_id, :project_identity, :research_depth, :researched_at, '
                . ':expires_at, :gate_0_status, :dossier_hash, :is_sealed, :file_path, :indexed_at'
                . ')'
            );

            $stmt->execute([
                ':dossier_id' => $dossier->dossierId,
                ':task_id' => $dossier->taskId,
                ':project_identity' => $dossier->projectIdentity,
                ':research_depth' => $dossier->researchDepth,
                ':researched_at' => $dossier->researchedAt,
                ':expires_at' => $dossier->expiresAt,
                ':gate_0_status' => $dossier->gate0Status,
                ':dossier_hash' => $dossier->dossierHash,
                ':is_sealed' => $dossier->isSealed() ? 1 : 0,
                ':file_path' => $filePath,
                ':indexed_at' => time(),
            ]);
        } catch (PDOException $e) {
            if ($this->isCorruptException($e)) {
                $this->healCorruption();
            }
        }
    }

    /**
     * @return array<int, string> Dossier IDs matching taskId, ordered by researched_at DESC
     */
    public function findDossierIdsByTaskId(string $taskId): array
    {
        if ($this->pdo === null) {
            return [];
        }

        try {
            $stmt = $this->pdo->prepare(
                'SELECT dossier_id FROM research_index WHERE task_id = :task_id ORDER BY researched_at DESC'
            );
            $stmt->execute([':task_id' => $taskId]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
        } catch (PDOException $e) {
            if ($this->isCorruptException($e)) {
                $this->healCorruption();
            }
            return [];
        }
    }

    public function hasDossier(string $dossierId): bool
    {
        if ($this->pdo === null) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare('SELECT 1 FROM research_index WHERE dossier_id = :dossier_id LIMIT 1');
            $stmt->execute([':dossier_id' => $dossierId]);
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            if ($this->isCorruptException($e)) {
                $this->healCorruption();
            }
            return false;
        }
    }

    public function removeDossier(string $dossierId): void
    {
        if ($this->pdo === null) {
            return;
        }

        try {
            $stmt = $this->pdo->prepare('DELETE FROM research_index WHERE dossier_id = :dossier_id');
            $stmt->execute([':dossier_id' => $dossierId]);
        } catch (PDOException $e) {
            if ($this->isCorruptException($e)) {
                $this->healCorruption();
            }
        }
    }

    public function countDossiers(): int
    {
        if ($this->pdo === null) {
            return 0;
        }

        try {
            $stmt = $this->pdo->query('SELECT COUNT(*) FROM research_index');
            return $stmt !== false ? (int)$stmt->fetchColumn() : 0;
        } catch (PDOException $e) {
            if ($this->isCorruptException($e)) {
                $this->healCorruption();
            }
            return 0;
        }
    }

    public function rebuildFromDirectory(string $dossiersDir): int
    {
        if (!is_dir($dossiersDir) || $this->pdo === null) {
            return 0;
        }

        $files = scandir($dossiersDir);
        if ($files === false) {
            return 0;
        }

        $count = 0;
        foreach ($files as $file) {
            if (!str_ends_with($file, '.json') || str_starts_with($file, '.')) {
                continue;
            }

            $content = @file_get_contents($dossiersDir . DIRECTORY_SEPARATOR . $file);
            if ($content === false) {
                continue;
            }

            try {
                $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($data)) {
                    $dossier = ResearchDossier::fromArray($data);
                    $this->indexDossier($dossier, $file);
                    $count++;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $count;
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    private function initialize(): void
    {
        if (!class_exists(PDO::class) || !in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->pdo = null;
            return;
        }

        $dir = dirname($this->sqlitePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        try {
            $this->pdo = new PDO('sqlite:' . $this->sqlitePath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->exec('PRAGMA journal_mode = WAL;');
            $this->pdo->exec('PRAGMA busy_timeout = 5000;');
            $this->pdo->exec(
                'CREATE TABLE IF NOT EXISTS research_index ('
                . 'dossier_id TEXT PRIMARY KEY, '
                . 'task_id TEXT NOT NULL, '
                . 'project_identity TEXT NOT NULL, '
                . 'research_depth TEXT NOT NULL, '
                . 'researched_at TEXT NOT NULL, '
                . 'expires_at TEXT, '
                . 'gate_0_status TEXT NOT NULL, '
                . 'dossier_hash TEXT NOT NULL, '
                . 'is_sealed INTEGER NOT NULL, '
                . 'file_path TEXT NOT NULL, '
                . 'indexed_at INTEGER NOT NULL'
                . ');'
                . 'CREATE INDEX IF NOT EXISTS idx_research_task_id ON research_index(task_id);'
                . 'CREATE INDEX IF NOT EXISTS idx_research_researched_at ON research_index(researched_at);'
                . 'CREATE INDEX IF NOT EXISTS idx_research_expires_at ON research_index(expires_at);'
            );
        } catch (PDOException) {
            $this->pdo = null;
        }
    }

    private function isCorruptException(PDOException $e): bool
    {
        $msg = strtolower($e->getMessage());
        return str_contains($msg, 'corrupt')
            || str_contains($msg, 'malformed')
            || str_contains($msg, 'not a database')
            || str_contains($msg, 'disk i/o error');
    }

    private function healCorruption(): void
    {
        $this->close();
        @unlink($this->sqlitePath);
        @unlink($this->sqlitePath . '-wal');
        @unlink($this->sqlitePath . '-shm');
        $this->initialize();
    }
}
