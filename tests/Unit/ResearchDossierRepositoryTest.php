<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use RuntimeException;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Tests\Assert;

class ResearchDossierRepositoryTest
{
    public static function run(): void
    {
        self::testPersistenceAndRetrieval();
        self::testSealedDossierRoundTrip();
        self::testTamperedSealedDossierRejection();
        self::testUnsealedDossierPreservationWithoutFalseIntegrity();
        self::testMissingDossierBehavior();
        self::testMalformedAndCorruptedStoredRecords();
        self::testUnsafeIdentifiersAndPathTraversal();
        self::testStorageSanitizedErrorMessages();
        self::testAtomicWriteBehavior();
        self::testDuplicateIdentifierBehavior();
        self::testFindByTaskIdWithActiveAndExpiredDossiers();
    }

    private static function createTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_test_rdei_' . bin2hex(random_bytes(6));
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

    private static function createSampleDossier(
        string $dossierId = 'DOS-20261115-4a7b9c',
        string $taskId = 'TASK-20261115-01',
        string $statement = 'Use DateTimeImmutable instead of DateTime',
        ?string $expiresAt = null
    ): ResearchDossier {
        $claim = new ExtractedClaim('CLM-01', $statement, ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95, ['8.2', '8.3'], [
            ['source_id' => 'src-php-manual', 'content_hash' => hash('sha256', 'content'), 'quote_segment' => 'Immutable date time representation']
        ]);
        return new ResearchDossier(
            dossierId: $dossierId,
            taskId: $taskId,
            projectIdentity: 'core',
            researchDepth: DepthProfile::LEVEL_L2,
            researchedAt: '2026-11-15T18:00:00Z',
            expiresAt: $expiresAt,
            sourcesConsulted: [
                ['source_id' => 'src-php-manual', 'name' => 'PHP Manual', 'url' => 'https://php.net']
            ],
            claims: [$claim],
            recommendations: [
                ['recommendation' => 'Adopt immutable dates', 'justification' => 'Prevents unintended mutation']
            ],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        );
    }

    private static function testPersistenceAndRetrieval(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            Assert::assertTrue($repo instanceof DossierRepositoryInterface, "Implements DossierRepositoryInterface");
            $dossier = self::createSampleDossier();
            $id = $repo->save($dossier);
            Assert::assertEquals($dossier->dossierId, $id, "save() returns dossierId");
            Assert::assertTrue($repo->has($id), "has() returns true for persisted dossier");

            $retrieved = $repo->get($id);
            Assert::assertTrue($retrieved !== null, "get() returns retrieved dossier");
            Assert::assertEquals($dossier->dossierId, $retrieved->dossierId, "dossierId matches");
            Assert::assertEquals($dossier->taskId, $retrieved->taskId, "taskId matches");
            Assert::assertEquals($dossier->projectIdentity, $retrieved->projectIdentity, "projectIdentity matches");
            Assert::assertEquals($dossier->researchDepth, $retrieved->researchDepth, "researchDepth matches");
            Assert::assertEquals($dossier->researchedAt, $retrieved->researchedAt, "researchedAt matches");
            Assert::assertEquals(count($dossier->claims), count($retrieved->claims), "claims count matches");
            Assert::assertEquals($dossier->claims[0]->statement, $retrieved->claims[0]->statement, "claim statement matches");
            Assert::assertTrue(file_exists($repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json'), "Dossier JSON file exists on disk");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testSealedDossierRoundTrip(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $sealed = self::createSampleDossier('DOS-20261115-seal01')->seal();
            Assert::assertTrue($sealed->isSealed(), "Dossier is sealed before save");
            Assert::assertTrue($sealed->verifyIntegrity(), "Sealed hash verifies before save");

            $id = $repo->save($sealed);
            $retrieved = $repo->get($id);
            Assert::assertTrue($retrieved !== null, "Retrieved sealed dossier is not null");
            Assert::assertTrue($retrieved->isSealed(), "Retrieved dossier preserves sealed state");
            Assert::assertEquals($sealed->dossierHash, $retrieved->dossierHash, "Retrieved hash matches sealed hash");
            Assert::assertTrue($retrieved->verifyIntegrity(), "Retrieved dossier passes cryptographic verification");
            Assert::assertTrue($repo->verifyIntegrity($retrieved), "Repository verifyIntegrity returns true for sealed dossier");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testTamperedSealedDossierRejection(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $id = $repo->save(self::createSampleDossier('DOS-20261115-tamper01')->seal());
            $file = $repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json';
            $data = json_decode((string)file_get_contents($file), true);
            $data['claims'][0]['statement'] = 'TAMPERED STATEMENT';
            file_put_contents($file, json_encode($data));
            Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->get($id), "get() rejects tampered sealed dossier");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testUnsealedDossierPreservationWithoutFalseIntegrity(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $unsealed = self::createSampleDossier('DOS-20261115-unseal01');
            Assert::assertFalse($unsealed->isSealed(), "Dossier starts unsealed");
            Assert::assertEquals('', $unsealed->dossierHash, "Empty hash for unsealed");

            $id = $repo->save($unsealed);
            $retrieved = $repo->get($id);
            Assert::assertTrue($retrieved !== null, "Retrieved unsealed dossier is not null");
            Assert::assertFalse($retrieved->isSealed(), "Retrieved dossier remains unsealed");
            Assert::assertEquals('', $retrieved->dossierHash, "Retrieved dossier maintains empty hash");
            Assert::assertFalse($retrieved->verifyIntegrity(), "verifyIntegrity on unsealed dossier entity returns false");
            Assert::assertFalse($repo->verifyIntegrity($retrieved), "repo->verifyIntegrity on unsealed dossier returns false");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testMissingDossierBehavior(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            Assert::assertEquals(null, $repo->get('DOS-NONEXISTENT'), "get() returns null for missing dossier");
            Assert::assertFalse($repo->has('DOS-NONEXISTENT'), "has() returns false for missing dossier");
            Assert::assertEquals(null, $repo->findByTaskId('TASK-NONEXISTENT'), "findByTaskId() returns null for missing task");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testMalformedAndCorruptedStoredRecords(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $dossiersDir = $repo->getDossiersDir();
            file_put_contents($dossiersDir . DIRECTORY_SEPARATOR . 'DOS-MALFORMED.json', '{ invalid json syntax ...');
            Assert::assertThrows(RuntimeException::class, fn () => $repo->get('DOS-MALFORMED'), "get() rejects malformed JSON");

            file_put_contents($dossiersDir . DIRECTORY_SEPARATOR . 'DOS-NONARRAY.json', '"just a string"');
            Assert::assertThrows(RuntimeException::class, fn () => $repo->get('DOS-NONARRAY'), "get() rejects non-array JSON");

            file_put_contents($dossiersDir . DIRECTORY_SEPARATOR . 'DOS-BADSCHEMA.json', json_encode(['dossier_id' => 'DOS-BADSCHEMA']));
            Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->get('DOS-BADSCHEMA'), "get() rejects missing task_id");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testUnsafeIdentifiersAndPathTraversal(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $unsafe = ['../../etc/passwd', '..\\..\\win.ini', 'DOS-01/../t', 'DOS-01\\..\\t', "DOS-01\0null", 'DOS~1', 'DOS%2e%2e01', 'DOS%00b', 'DOS::$DATA', '', '   ', 'DOS SPACES', 'DOS/SLASH', 'DOS\\BACK'];
            foreach ($unsafe as $badId) {
                Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->get($badId), "get() rejects '{$badId}'");
                Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->has($badId), "has() rejects '{$badId}'");
                Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->findByTaskId($badId), "findByTaskId() rejects '{$badId}'");
                Assert::assertThrows(InvalidArgumentException::class, fn () => $repo->save(new ResearchDossier($badId, 'TASK-01', 'core', DepthProfile::LEVEL_L1, '2026-11-15T18:00:00Z')), "save() rejects '{$badId}'");
            }
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testStorageSanitizedErrorMessages(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            file_put_contents($repo->getDossiersDir() . DIRECTORY_SEPARATOR . 'DOS-SANTEST.json', '{ bad json');
            try {
                $repo->get('DOS-SANTEST');
                Assert::assertTrue(false, "Should have thrown on bad JSON");
            } catch (RuntimeException $e) {
                Assert::assertFalse(str_contains($e->getMessage(), $tempDir), "Error does not leak storage path");
                Assert::assertFalse(str_contains($e->getMessage(), 'laragon'), "Error does not leak system root");
                Assert::assertTrue(str_contains($e->getMessage(), 'DOS-SANTEST'), "Error identifies dossierId safely");
            }
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testAtomicWriteBehavior(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $id = $repo->save(self::createSampleDossier('DOS-20261115-atomic01'));
            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Zero temporary .tmp files left in storage after save");
            Assert::assertTrue(file_exists($repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json'), "Final file exists atomically");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testDuplicateIdentifierBehavior(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $repo->save(self::createSampleDossier('DOS-DUP-01', statement: 'First Version'));
            Assert::assertEquals('First Version', $repo->get('DOS-DUP-01')->claims[0]->statement, "Initial save statement verified");

            $repo->save(self::createSampleDossier('DOS-DUP-01', statement: 'Second Updated Version'));
            Assert::assertEquals('Second Updated Version', $repo->get('DOS-DUP-01')->claims[0]->statement, "Deterministic overwrite replaces record");

            $files = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => $f === 'DOS-DUP-01.json');
            Assert::assertEquals(1, count($files), "Exactly one canonical file remains for duplicate ID");
        } finally {
            self::removeDir($tempDir);
        }
    }

    private static function testFindByTaskIdWithActiveAndExpiredDossiers(): void
    {
        $tempDir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $tempDir);
            $expired = self::createSampleDossier('DOS-EXP-01', taskId: 'TASK-EXP', statement: 'Expired claim', expiresAt: '2020-01-01T00:00:00Z');
            $repo->save($expired);
            Assert::assertTrue($repo->get('DOS-EXP-01') !== null, "get() retrieves expired dossier by canonical ID");
            Assert::assertTrue($expired->isExpired(), "Dossier is expired");
            Assert::assertEquals(null, $repo->findByTaskId('TASK-EXP'), "findByTaskId() ignores expired dossier");

            $active = self::createSampleDossier('DOS-EXP-02', taskId: 'TASK-EXP', statement: 'Active claim', expiresAt: '2035-01-01T00:00:00Z');
            $repo->save($active);
            $found = $repo->findByTaskId('TASK-EXP');
            Assert::assertTrue($found !== null, "findByTaskId() finds active valid dossier");
            Assert::assertEquals('DOS-EXP-02', $found->dossierId, "Returns the active dossier ID");
        } finally {
            self::removeDir($tempDir);
        }
    }
}
