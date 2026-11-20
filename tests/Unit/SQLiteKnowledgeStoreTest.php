<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Repository\SQLiteKnowledgeStore;
use UniversalAgentOS\Tests\Assert;

class SQLiteKnowledgeStoreTest
{
    public static function run(): void
    {
        self::testSQLiteKnowledgeStoreDirectly();
        self::testDirectoryScanningFallbackWithoutSqlite();
    }

    private static function createTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_test_sql_' . bin2hex(random_bytes(6));
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        return $dir;
    }

    private static function removeDir(string $dir): void
    {
        if (!is_dir($dir)) { return; }
        foreach ((array)scandir($dir) as $item) {
            if ($item === '.' || $item === '..') { continue; }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? self::removeDir($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private static function createSampleDossier(string $dossierId, string $taskId, string $statement = 'claim'): ResearchDossier
    {
        $claim = new ExtractedClaim('CLM-01', $statement, ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
        return new ResearchDossier($dossierId, $taskId, 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z', null, [], [$claim]);
    }

    private static function testSQLiteKnowledgeStoreDirectly(): void
    {
        $tempDir = self::createTempDir();
        try {
            $sqlitePath = $tempDir . DIRECTORY_SEPARATOR . 'index' . DIRECTORY_SEPARATOR . 'test.sqlite';
            $store = new SQLiteKnowledgeStore($sqlitePath);
            Assert::assertTrue($store->isAvailable(), "SQLiteKnowledgeStore is available");
            Assert::assertEquals($sqlitePath, $store->getSqlitePath(), "getSqlitePath matches");

            $d1 = self::createSampleDossier('DOS-SQL-01', 'TASK-SQL-01');
            $store->indexDossier($d1, 'DOS-SQL-01.json');
            Assert::assertTrue($store->hasDossier('DOS-SQL-01'), "hasDossier returns true");
            Assert::assertEquals(1, $store->countDossiers(), "countDossiers is 1");
            Assert::assertEquals(['DOS-SQL-01'], $store->findDossierIdsByTaskId('TASK-SQL-01'), "findDossierIdsByTaskId returns indexed ID");

            $store->removeDossier('DOS-SQL-01');
            Assert::assertFalse($store->hasDossier('DOS-SQL-01'), "hasDossier returns false after remove");
            Assert::assertEquals(0, $store->countDossiers(), "countDossiers is 0 after remove");

            $dossiersDir = $tempDir . DIRECTORY_SEPARATOR . 'dossiers';
            mkdir($dossiersDir, 0755, true);
            file_put_contents($dossiersDir . DIRECTORY_SEPARATOR . 'DOS-SQL-02.json', json_encode($d1->toArray()));
            Assert::assertEquals(1, $store->rebuildFromDirectory($dossiersDir), "rebuildFromDirectory indexes 1 file");
            Assert::assertTrue($store->hasDossier('DOS-SQL-01'), "Rebuilt index contains restored dossier");
            $store->close();
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testDirectoryScanningFallbackWithoutSqlite(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir, enableKnowledgeStore: false);
            Assert::assertEquals(null, $repo->getKnowledgeStore(), "knowledgeStore is explicitly null");
            $d1 = self::createSampleDossier('DOS-NOSQL-01', 'TASK-NOSQL-01', 'Fallback claim');
            $repo->save($d1);

            Assert::assertTrue($repo->has('DOS-NOSQL-01'), "has() works via flat-file check");
            Assert::assertTrue($repo->get('DOS-NOSQL-01') !== null, "get() works via flat-file check");
            $byTask = $repo->findByTaskId('TASK-NOSQL-01');
            Assert::assertTrue($byTask !== null, "findByTaskId() works via directory scan fallback");
            Assert::assertEquals('DOS-NOSQL-01', $byTask->dossierId, "Matches dossierId via directory scan");
        } finally {
            self::removeDir($tempDir);
        }
    }
}
