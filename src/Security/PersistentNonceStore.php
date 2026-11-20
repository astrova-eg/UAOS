<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use PDO;
use PDOException;
use UniversalAgentOS\Contracts\NonceStoreInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Exceptions\NonceReplayException;

/**
 * Durable atomic nonce persistence store backed by SQLite WAL mode and file-locked fallback.
 */
class PersistentNonceStore implements NonceStoreInterface
{
    private PathResolverInterface $pathResolver;
    private ?PDO $pdo = null;
    private string $sqlitePath;
    private string $jsonPath;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $stateDir = $this->pathResolver->getProjectStateDir();
        if (!is_dir($stateDir)) {
            mkdir($stateDir, 0755, true);
        }
        $this->sqlitePath = $stateDir . DIRECTORY_SEPARATOR . 'nonces.sqlite';
        $this->jsonPath = $stateDir . DIRECTORY_SEPARATOR . 'nonces.json';
        $this->initializeBackend();
    }

    public function consumeNonce(string $nonce, string $taskId, int $expiresAt): bool
    {
        $now = time();
        if ($now > $expiresAt) {
            throw new NonceReplayException("Nonce '{$nonce}' has expired at timestamp {$expiresAt}");
        }

        if ($this->pdo !== null) {
            return $this->consumeWithSqlite($nonce, $taskId, $now, $expiresAt);
        }

        return $this->consumeWithFileLock($nonce, $taskId, $now, $expiresAt);
    }

    public function isConsumed(string $nonce): bool
    {
        if ($this->pdo !== null) {
            $stmt = $this->pdo->prepare('SELECT 1 FROM consumed_nonces WHERE nonce = :nonce');
            $stmt->execute([':nonce' => $nonce]);
            return $stmt->fetchColumn() !== false;
        }

        return $this->isConsumedInFile($nonce);
    }

    public function pruneExpired(int $now): int
    {
        if ($this->pdo !== null) {
            $stmt = $this->pdo->prepare('DELETE FROM consumed_nonces WHERE expires_at < :now');
            $stmt->execute([':now' => $now]);
            return $stmt->rowCount();
        }

        return $this->pruneExpiredInFile($now);
    }

    public function getDiagnostics(): array
    {
        return [
            'backend' => $this->pdo !== null ? 'sqlite_wal' : 'json_flock',
            'sqlite_path' => $this->sqlitePath,
            'json_path' => $this->jsonPath,
            'sqlite_available' => class_exists(PDO::class) && in_array('sqlite', PDO::getAvailableDrivers(), true),
        ];
    }

    private function initializeBackend(): void
    {
        if (class_exists(PDO::class) && in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            try {
                $this->pdo = new PDO('sqlite:' . $this->sqlitePath);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->exec('PRAGMA journal_mode = WAL;');
                $this->pdo->exec('PRAGMA busy_timeout = 5000;');
                $this->pdo->exec(
                    'CREATE TABLE IF NOT EXISTS consumed_nonces ('
                    . 'nonce TEXT PRIMARY KEY, '
                    . 'task_id TEXT NOT NULL, '
                    . 'consumed_at INTEGER NOT NULL, '
                    . 'expires_at INTEGER NOT NULL'
                    . ');'
                );
                return;
            } catch (PDOException) {
                $this->pdo = null;
            }
        }

        if (!file_exists($this->jsonPath)) {
            file_put_contents($this->jsonPath, json_encode([]));
        }
    }

    private function consumeWithSqlite(string $nonce, string $taskId, int $now, int $expiresAt): bool
    {
        try {
            $this->pdo->exec('BEGIN IMMEDIATE;');
            $stmt = $this->pdo->prepare(
                'INSERT INTO consumed_nonces (nonce, task_id, consumed_at, expires_at) '
                . 'VALUES (:nonce, :task_id, :now, :expires_at)'
            );
            $stmt->execute([
                ':nonce' => $nonce,
                ':task_id' => $taskId,
                ':now' => $now,
                ':expires_at' => $expiresAt,
            ]);
            $this->pdo->exec('COMMIT;');
            return true;
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->exec('ROLLBACK;');
            }
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed') || str_contains($e->getMessage(), '19')) {
                throw new NonceReplayException("Replay attack detected: Nonce '{$nonce}' has already been consumed.");
            }
            throw new NonceReplayException("Nonce consumption failed: " . $e->getMessage(), 0, $e);
        }
    }

    private function consumeWithFileLock(string $nonce, string $taskId, int $now, int $expiresAt): bool
    {
        $handle = fopen($this->jsonPath, 'c+');
        if ($handle === false) {
            throw new NonceReplayException("Cannot open nonce journal file: {$this->jsonPath}");
        }

        try {
            if (!flock($handle, LOCK_EX)) {
                throw new NonceReplayException("Failed acquiring exclusive file lock on nonce journal.");
            }

            $size = filesize($this->jsonPath);
            $content = $size > 0 ? (string)fread($handle, $size) : '[]';
            $records = json_decode($content, true);
            if (!is_array($records)) {
                $records = [];
            }

            if (isset($records[$nonce])) {
                throw new NonceReplayException("Replay attack detected: Nonce '{$nonce}' has already been consumed.");
            }

            $records[$nonce] = [
                'task_id' => $taskId,
                'consumed_at' => $now,
                'expires_at' => $expiresAt,
            ];

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string)json_encode($records, JSON_PRETTY_PRINT));
            fflush($handle);
            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    private function isConsumedInFile(string $nonce): bool
    {
        if (!file_exists($this->jsonPath)) {
            return false;
        }
        $raw = file_get_contents($this->jsonPath);
        $records = is_string($raw) ? json_decode($raw, true) : [];
        return is_array($records) && isset($records[$nonce]);
    }

    private function pruneExpiredInFile(int $now): int
    {
        if (!file_exists($this->jsonPath)) {
            return 0;
        }
        $handle = fopen($this->jsonPath, 'c+');
        if ($handle === false) {
            return 0;
        }
        try {
            if (!flock($handle, LOCK_EX)) {
                return 0;
            }
            $size = filesize($this->jsonPath);
            $content = $size > 0 ? (string)fread($handle, $size) : '[]';
            $records = json_decode($content, true);
            if (!is_array($records)) {
                return 0;
            }
            $initialCount = count($records);
            $records = array_filter($records, static fn ($rec) => isset($rec['expires_at']) && $rec['expires_at'] >= $now);
            $pruned = $initialCount - count($records);
            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, (string)json_encode($records, JSON_PRETTY_PRINT));
            fflush($handle);
            return $pruned;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
