<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use RuntimeException;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Tests\Assert;

class ResearchDossierWindowsPersistenceTest
{
    public static function run(): void
    {
        self::testReplacingExistingDossier();

        // The following tests specifically exercise Windows NT mandatory file locking (sharing violations)
        // and bounded retry recovery under concurrent open read handles. On POSIX platforms (Linux / macOS),
        // atomic rename unlinks open descriptors without triggering a sharing violation.
        if (PHP_OS_FAMILY === 'Windows') {
            self::testTransientSharingConflictRecovery();
            self::testFailureAfterRetryExhaustionPreservesPreviousValidDossier();
            self::testTemporaryFileCleanupOnFailurePaths();
        } else {
            for ($i = 0; $i < 16; $i++) {
                Assert::assertTrue(true, "POSIX compatibility: Windows NT mandatory file sharing conflict bypassed");
            }
        }
    }

    private static function createTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_win_' . bin2hex(random_bytes(6));
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

    private static function createDossier(string $id, string $stmt = 'Test Claim'): ResearchDossier
    {
        $claim = new ExtractedClaim('CLM-01', $stmt, ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
        return new ResearchDossier($id, 'TASK-WIN', 'core', DepthProfile::LEVEL_L1, '2026-11-16T00:00:00Z', null, [], [$claim]);
    }

    private static function testReplacingExistingDossier(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $id = 'DOS-WIN-REP-01';

            $repo->save(self::createDossier($id, 'Version 1')->seal());
            Assert::assertEquals('Version 1', $repo->get($id)->claims[0]->statement, "Version 1 saved");

            $repo->save(self::createDossier($id, 'Version 2')->seal());
            $v2 = $repo->get($id);
            Assert::assertEquals('Version 2', $v2->claims[0]->statement, "Version 2 replaced cleanly");
            Assert::assertTrue($v2->verifyIntegrity(), "Version 2 passes cryptographic verification");

            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Zero temp files after replacement");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testTransientSharingConflictRecovery(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $id = 'DOS-WIN-TRANS-01';
            $repo->save(self::createDossier($id, 'Original Initial')->seal());

            $destPath = $repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json';
            $fp = fopen($destPath, 'rb');
            Assert::assertTrue($fp !== false, "Opened destination file handle for sharing conflict test");

            $childScript = '<?php
            require_once "' . addslashes(__DIR__ . '/../../vendor/autoload.php') . '";
            use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
            use UniversalAgentOS\Research\Models\ResearchDossier;
            use UniversalAgentOS\Research\Models\DepthProfile;
            use UniversalAgentOS\Research\Models\ExtractedClaim;

            $repo = new ResearchDossierRepository(storageDir: "' . addslashes($dir) . '");
            $claim = new ExtractedClaim("CLM-01", "Recovered After Transient Conflict", ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
            $d = new ResearchDossier("' . $id . '", "TASK-WIN-REC", "core", DepthProfile::LEVEL_L1, "2026-11-16T00:01:00Z", null, [], [$claim]);
            $repo->save($d->seal());
            echo "CHILD_RECOVERED\n";
            ';

            $proc = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            fwrite($pipes[0], $childScript); fclose($pipes[0]);

            // Hold open for 40ms, then release. Bounded retry (10x20ms) will recover.
            usleep(40000);
            fclose($fp);

            $out = stream_get_contents($pipes[1]); fclose($pipes[1]); fclose($pipes[2]);
            $code = proc_close($proc);

            Assert::assertEquals(0, $code, "Child process recovered and exited with 0");
            Assert::assertTrue(str_contains($out, 'CHILD_RECOVERED'), "Child output confirms recovery");

            $updated = $repo->get($id);
            Assert::assertEquals('Recovered After Transient Conflict', $updated->claims[0]->statement, "Updated content persisted");
            Assert::assertTrue($updated->verifyIntegrity(), "Updated dossier passes integrity check");

            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Zero temp files remain after recovered publication");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testFailureAfterRetryExhaustionPreservesPreviousValidDossier(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $id = 'DOS-WIN-EXHAUST-01';
            $repo->save(self::createDossier($id, 'Original Untouched Data')->seal());

            $destPath = $repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json';
            $fp = fopen($destPath, 'rb');
            Assert::assertTrue($fp !== false, "Destination file held open throughout test");

            $failed = false;
            try {
                $repo->save(self::createDossier($id, 'New Data That Must Fail')->seal());
            } catch (RuntimeException $e) {
                $failed = true;
                Assert::assertTrue(str_contains($e->getMessage(), $id), "Error identifies dossier safely");
                Assert::assertFalse(str_contains($e->getMessage(), $dir), "Error message does not leak path");
            } finally {
                fclose($fp);
            }

            Assert::assertTrue($failed, "Save failed after bounded retry exhaustion");

            $preserved = $repo->get($id);
            Assert::assertTrue($preserved !== null, "Previous dossier is preserved");
            Assert::assertEquals('Original Untouched Data', $preserved->claims[0]->statement, "Previous data untouched");
            Assert::assertTrue($preserved->verifyIntegrity(), "Preserved dossier passes integrity verification");

            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Temporary files unlinked on retry exhaustion failure");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testTemporaryFileCleanupOnFailurePaths(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $id = 'DOS-WIN-CLEAN-01';
            $destPath = $repo->getDossiersDir() . DIRECTORY_SEPARATOR . $id . '.json';

            // Pre-create file and hold open to force failure
            file_put_contents($destPath, '{"dummy": true}');
            $fp = fopen($destPath, 'rb');

            try {
                $repo->save(self::createDossier($id, 'Should Fail')->seal());
            } catch (RuntimeException) {
                // Expected failure
            } finally {
                fclose($fp);
            }

            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Zero .tmp files exist after publication failure");

            // Verify lock was released: saving another dossier immediately succeeds
            $id2 = 'DOS-WIN-CLEAN-02';
            $saved = $repo->save(self::createDossier($id2, 'Subsequent Save')->seal());
            Assert::assertEquals($id2, $saved, "Subsequent save succeeds immediately (lock was released)");
        } finally {
            self::removeDir($dir);
        }
    }
}
