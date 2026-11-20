<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Repository;

use InvalidArgumentException;
use JsonException;
use RuntimeException;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Research\Contracts\SourceRepositoryInterface;
use UniversalAgentOS\Research\Contracts\SourceValidatorInterface;
use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceLifecycleState;
use UniversalAgentOS\Research\Validation\SourceDefinitionValidator;
use UniversalAgentOS\Runtime\PathResolver;

/**
 * High-integrity persistent repository for engineering source definitions and audit trails.
 */
class SourceRepository implements SourceRepositoryInterface
{
    private string $storageDir;
    private string $sourcesDir;
    private string $historyDir;
    private string $auditDir;
    private string $lifecycleDir;
    private string $locksDir;
    private SourceValidatorInterface $validator;

    public function __construct(
        ?PathResolverInterface $pathResolver = null,
        ?string $storageDir = null,
        ?SourceValidatorInterface $validator = null
    ) {
        $this->storageDir = ($storageDir !== null && trim($storageDir) !== '')
            ? rtrim(str_replace('/', DIRECTORY_SEPARATOR, $storageDir), DIRECTORY_SEPARATOR)
            : ($pathResolver ?? new PathResolver())->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'research';

        $this->sourcesDir = $this->storageDir . DIRECTORY_SEPARATOR . 'sources';
        $this->historyDir = $this->sourcesDir . DIRECTORY_SEPARATOR . 'history';
        $this->auditDir = $this->sourcesDir . DIRECTORY_SEPARATOR . 'audit';
        $this->lifecycleDir = $this->sourcesDir . DIRECTORY_SEPARATOR . 'lifecycle';
        $this->locksDir = $this->storageDir . DIRECTORY_SEPARATOR . 'locks';
        $this->validator = $validator ?? new SourceDefinitionValidator();

        foreach ([$this->sourcesDir, $this->historyDir, $this->auditDir, $this->lifecycleDir, $this->locksDir] as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }
    }

    public function save(SourceDefinition $source): string
    {
        $this->assertSafeIdentifier($source->sourceId);
        $targetPath = $this->sourcesDir . DIRECTORY_SEPARATOR . $source->sourceId . '.json';
        $lockPath = $this->locksDir . DIRECTORY_SEPARATOR . 'source-' . $source->sourceId . '.lock';

        try {
            $json = json_encode(
                $source->toArray(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            );
        } catch (JsonException $e) {
            throw new RuntimeException("Serialization failure for source '{$source->sourceId}': " . $e->getMessage(), 0, $e);
        }

        $lockFp = @fopen($lockPath, 'cb');
        if ($lockFp === false || !flock($lockFp, LOCK_EX)) {
            if ($lockFp !== false) {
                fclose($lockFp);
            }
            throw new RuntimeException("Lock failure persisting source '{$source->sourceId}'.");
        }

        try {
            if (file_exists($targetPath)) {
                $this->snapshotRevision($source->sourceId, $targetPath);
            }

            $tempFile = $this->sourcesDir . DIRECTORY_SEPARATOR . '.' . $source->sourceId . '.' . bin2hex(random_bytes(8)) . '.tmp';
            $written = @file_put_contents($tempFile, $json);
            if ($written === false || $written < strlen($json)) {
                @unlink($tempFile);
                throw new RuntimeException("Disk write failure persisting source '{$source->sourceId}'.");
            }
            @chmod($tempFile, 0644);

            if (DIRECTORY_SEPARATOR === '\\' && file_exists($targetPath)) {
                @unlink($targetPath);
            }
            if (!@rename($tempFile, $targetPath)) {
                @unlink($tempFile);
                throw new RuntimeException("Atomic rename failed persisting source '{$source->sourceId}'.");
            }

            return $targetPath;
        } finally {
            flock($lockFp, LOCK_UN);
            fclose($lockFp);
        }
    }

    public function get(string $sourceId): ?SourceDefinition
    {
        $this->assertSafeIdentifier($sourceId);
        $path = $this->sourcesDir . DIRECTORY_SEPARATOR . $sourceId . '.json';
        if (!file_exists($path)) {
            return null;
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        try {
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) {
                return null;
            }
            return SourceDefinition::fromArray($data);
        } catch (\Throwable) {
            return null;
        }
    }

    public function has(string $sourceId): bool
    {
        $this->assertSafeIdentifier($sourceId);
        return file_exists($this->sourcesDir . DIRECTORY_SEPARATOR . $sourceId . '.json');
    }

    public function remove(string $sourceId): bool
    {
        $this->assertSafeIdentifier($sourceId);
        $path = $this->sourcesDir . DIRECTORY_SEPARATOR . $sourceId . '.json';
        $statePath = $this->lifecycleDir . DIRECTORY_SEPARATOR . $sourceId . '.state';
        @unlink($statePath);
        if (file_exists($path)) {
            return @unlink($path);
        }
        return false;
    }

    public function getLifecycleState(string $sourceId): string
    {
        $this->assertSafeIdentifier($sourceId);
        $statePath = $this->lifecycleDir . DIRECTORY_SEPARATOR . $sourceId . '.state';
        if (file_exists($statePath)) {
            $val = trim((string)@file_get_contents($statePath));
            if ($val !== '' && SourceLifecycleState::isValid($val)) {
                return $val;
            }
        }
        $source = $this->get($sourceId);
        if ($source !== null && $source->status === SourceDefinition::STATUS_DEPRECATED) {
            return SourceLifecycleState::STATE_DEPRECATED;
        }
        return SourceLifecycleState::STATE_ACTIVE;
    }

    public function setLifecycleState(string $sourceId, string $state): void
    {
        $this->assertSafeIdentifier($sourceId);
        if (!SourceLifecycleState::isValid($state)) {
            throw new InvalidArgumentException("Invalid lifecycle state '{$state}'.");
        }
        $statePath = $this->lifecycleDir . DIRECTORY_SEPARATOR . $sourceId . '.state';
        @file_put_contents($statePath, $state, LOCK_EX);
    }

    public function recordAudit(SourceAuditRecord $record): void
    {
        $this->assertSafeIdentifier($record->sourceId);
        $auditPath = $this->auditDir . DIRECTORY_SEPARATOR . $record->sourceId . '.jsonl';

        try {
            $line = json_encode($record->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) . "\n";
            @file_put_contents($auditPath, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            throw new RuntimeException("Failed recording audit for source '{$record->sourceId}': " . $e->getMessage(), 0, $e);
        }
    }

    public function getAuditHistory(string $sourceId): array
    {
        $this->assertSafeIdentifier($sourceId);
        $auditPath = $this->auditDir . DIRECTORY_SEPARATOR . $sourceId . '.jsonl';
        if (!file_exists($auditPath)) {
            return [];
        }

        $records = [];
        $lines = @file($auditPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        foreach ($lines as $line) {
            try {
                $data = json_decode($line, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($data)) {
                    $records[] = SourceAuditRecord::fromArray($data);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $records;
    }

    public function all(array $criteria = []): array
    {
        $files = @scandir($this->sourcesDir);
        if ($files === false) {
            return [];
        }

        $sources = [];
        foreach ($files as $f) {
            if (!str_ends_with($f, '.json') || str_starts_with($f, '.')) {
                continue;
            }
            $sourceId = substr($f, 0, -5);
            $source = $this->get($sourceId);
            if ($source === null) {
                continue;
            }

            if ($this->matchesCriteria($source, $criteria)) {
                $sources[] = $source;
            }
        }

        return $sources;
    }

    private function matchesCriteria(SourceDefinition $source, array $criteria): bool
    {
        if (isset($criteria['domain']) && !in_array($criteria['domain'], $source->domains, true)) {
            return false;
        }
        if (isset($criteria['status']) && $source->status !== $criteria['status']) {
            return false;
        }
        if (isset($criteria['lifecycle_state']) && $this->getLifecycleState($source->sourceId) !== $criteria['lifecycle_state']) {
            return false;
        }
        if (isset($criteria['category_type']) && $source->categoryType !== $criteria['category_type']) {
            return false;
        }
        if (isset($criteria['authority_tier']) && $source->authorityTier !== (int)$criteria['authority_tier']) {
            return false;
        }
        return true;
    }

    private function snapshotRevision(string $sourceId, string $currentFile): void
    {
        $sourceHistoryDir = $this->historyDir . DIRECTORY_SEPARATOR . $sourceId;
        if (!is_dir($sourceHistoryDir)) {
            @mkdir($sourceHistoryDir, 0755, true);
        }
        $snapshotName = gmdate('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.json';
        @copy($currentFile, $sourceHistoryDir . DIRECTORY_SEPARATOR . $snapshotName);
    }

    private function assertSafeIdentifier(string $id): void
    {
        if (trim($id) === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $id) || str_contains($id, '..')) {
            throw new InvalidArgumentException("Unsafe or invalid identifier: '{$id}'.");
        }
    }
}
