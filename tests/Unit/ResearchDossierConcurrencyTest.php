<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Tests\Assert;

class ResearchDossierConcurrencyTest
{
    public static function run(): void
    {
        self::testConcurrentWritesToSameDossier();
        self::testConcurrentWritesToDifferentDossiers();
        self::testConcurrentReadersWhileDossierIsBeingReplaced();
        self::testLockContentionAndRelease();
        self::testAbruptProcessTerminationLockRecovery();
    }

    private static function createTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_conc_' . bin2hex(random_bytes(6));
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

    private static function makeWriteScript(string $dir, string $id, string $taskId, string $statement): string
    {
        return '<?php
        require_once "' . addslashes(__DIR__ . '/../../vendor/autoload.php') . '";
        use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
        use UniversalAgentOS\Research\Models\ResearchDossier;
        use UniversalAgentOS\Research\Models\DepthProfile;
        use UniversalAgentOS\Research\Models\ExtractedClaim;

        $repo = new ResearchDossierRepository(storageDir: "' . addslashes($dir) . '");
        $claim = new ExtractedClaim("CLM-01", "' . addslashes($statement) . '", ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
        $d = new ResearchDossier("' . $id . '", "' . $taskId . '", "core", DepthProfile::LEVEL_L1, "2026-11-16T00:00:00Z", null, [], [$claim]);
        $repo->save($d->seal());
        echo "OK\n";
        ';
    }

    private static function testConcurrentWritesToSameDossier(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $p1 = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1);
            $p2 = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2);

            fwrite($pipes1[0], self::makeWriteScript($dir, 'DOS-SAME-01', 'TASK-A', 'Statement A'));
            fclose($pipes1[0]);
            fwrite($pipes2[0], self::makeWriteScript($dir, 'DOS-SAME-01', 'TASK-B', 'Statement B'));
            fclose($pipes2[0]);

            $out1 = stream_get_contents($pipes1[1]); fclose($pipes1[1]); fclose($pipes1[2]); $c1 = proc_close($p1);
            $out2 = stream_get_contents($pipes2[1]); fclose($pipes2[1]); fclose($pipes2[2]); $c2 = proc_close($p2);

            Assert::assertEquals(0, $c1, "Process 1 exited with 0");
            Assert::assertEquals(0, $c2, "Process 2 exited with 0");

            $final = $repo->get('DOS-SAME-01');
            Assert::assertTrue($final !== null, "Final dossier exists");
            Assert::assertTrue($final->verifyIntegrity(), "Final dossier passes cryptographic verification");
            $statement = $final->claims[0]->statement;
            Assert::assertTrue($statement === 'Statement A' || $statement === 'Statement B', "Final statement is valid");

            $tmpFiles = array_filter((array)scandir($repo->getDossiersDir()), fn ($f) => str_ends_with($f, '.tmp'));
            Assert::assertEquals(0, count($tmpFiles), "Zero temp files remain after concurrent writes");
            Assert::assertTrue(file_exists($repo->getLocksDir() . DIRECTORY_SEPARATOR . 'DOS-SAME-01.lock'), "Lock file exists");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testConcurrentWritesToDifferentDossiers(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $p1 = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes1);
            $p2 = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes2);

            fwrite($pipes1[0], self::makeWriteScript($dir, 'DOS-DIFF-01', 'TASK-D1', 'Statement Diff 1'));
            fclose($pipes1[0]);
            fwrite($pipes2[0], self::makeWriteScript($dir, 'DOS-DIFF-02', 'TASK-D2', 'Statement Diff 2'));
            fclose($pipes2[0]);

            fclose($pipes1[1]); fclose($pipes1[2]); $c1 = proc_close($p1);
            fclose($pipes2[1]); fclose($pipes2[2]); $c2 = proc_close($p2);

            Assert::assertEquals(0, $c1, "Process 1 finished successfully");
            Assert::assertEquals(0, $c2, "Process 2 finished successfully");

            $d1 = $repo->get('DOS-DIFF-01');
            $d2 = $repo->get('DOS-DIFF-02');
            Assert::assertTrue($d1 !== null && $d1->verifyIntegrity(), "Dossier 1 valid and verified");
            Assert::assertTrue($d2 !== null && $d2->verifyIntegrity(), "Dossier 2 valid and verified");
            Assert::assertEquals('Statement Diff 1', $d1->claims[0]->statement, "Dossier 1 statement preserved");
            Assert::assertEquals('Statement Diff 2', $d2->claims[0]->statement, "Dossier 2 statement preserved");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testConcurrentReadersWhileDossierIsBeingReplaced(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $claim = new ExtractedClaim('CLM-01', 'Initial', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
            $init = new ResearchDossier('DOS-READ-01', 'TASK-R0', 'core', DepthProfile::LEVEL_L1, '2026-11-16T00:00:00Z', null, [], [$claim]);
            $repo->save($init->seal());

            $writerScript = '<?php
            require_once "' . addslashes(__DIR__ . '/../../vendor/autoload.php') . '";
            use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
            use UniversalAgentOS\Research\Models\ResearchDossier;
            use UniversalAgentOS\Research\Models\DepthProfile;
            use UniversalAgentOS\Research\Models\ExtractedClaim;

            $repo = new ResearchDossierRepository(storageDir: "' . addslashes($dir) . '");
            for ($i = 1; $i <= 5; $i++) {
                $c = new ExtractedClaim("CLM-01", "Update " . $i, ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
                $d = new ResearchDossier("DOS-READ-01", "TASK-R" . $i, "core", DepthProfile::LEVEL_L1, "2026-11-16T00:0" . $i . ":00Z", null, [], [$c]);
                $repo->save($d->seal());
                usleep(5000);
            }
            echo "DONE\n";
            ';

            $readerScript = '<?php
            require_once "' . addslashes(__DIR__ . '/../../vendor/autoload.php') . '";
            use UniversalAgentOS\Research\Repository\ResearchDossierRepository;

            $repo = new ResearchDossierRepository(storageDir: "' . addslashes($dir) . '");
            $success = true;
            for ($i = 0; $i < 25; $i++) {
                $d = $repo->get("DOS-READ-01");
                if ($d === null || !$d->verifyIntegrity() || empty($d->claims[0]->statement)) {
                    $success = false;
                    break;
                }
                usleep(2000);
            }
            echo $success ? "ALL_VALID\n" : "INVALID_READ\n";
            ';

            $pw = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesW);
            fwrite($pipesW[0], $writerScript); fclose($pipesW[0]);

            $pr = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipesR);
            fwrite($pipesR[0], $readerScript); fclose($pipesR[0]);

            $outW = stream_get_contents($pipesW[1]); fclose($pipesW[1]); fclose($pipesW[2]); $cW = proc_close($pw);
            $outR = stream_get_contents($pipesR[1]); fclose($pipesR[1]); fclose($pipesR[2]); $cR = proc_close($pr);

            Assert::assertEquals(0, $cW, "Writer finished successfully");
            Assert::assertEquals(0, $cR, "Reader finished successfully");
            Assert::assertTrue(str_contains($outR, 'ALL_VALID'), "All reader reads returned complete valid verified JSON");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testLockContentionAndRelease(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $lockScript = '<?php
            $lockFile = "' . addslashes($repo->getLocksDir()) . DIRECTORY_SEPARATOR . 'DOS-CONT-01.lock";
            $fp = fopen($lockFile, "cb");
            flock($fp, LOCK_EX);
            echo "LOCKED\n";
            usleep(60000); // 60ms
            flock($fp, LOCK_UN);
            fclose($fp);
            echo "UNLOCKED\n";
            ';

            $proc = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            fwrite($pipes[0], $lockScript); fclose($pipes[0]);

            $line = fgets($pipes[1]);
            Assert::assertTrue(str_contains((string)$line, 'LOCKED'), "Child acquired exclusive lock");

            $claim = new ExtractedClaim('CLM-01', 'Contention Test', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
            $d = new ResearchDossier('DOS-CONT-01', 'TASK-C1', 'core', DepthProfile::LEVEL_L1, '2026-11-16T00:00:00Z', null, [], [$claim]);

            $id = $repo->save($d->seal());
            Assert::assertEquals('DOS-CONT-01', $id, "Save succeeded after waiting for lock release");

            fclose($pipes[1]); fclose($pipes[2]); proc_close($proc);
            Assert::assertTrue($repo->get('DOS-CONT-01')->verifyIntegrity(), "Saved dossier verified");
        } finally {
            self::removeDir($dir);
        }
    }

    private static function testAbruptProcessTerminationLockRecovery(): void
    {
        $dir = self::createTempDir();
        try {
            $repo = new ResearchDossierRepository(storageDir: $dir);
            $crashScript = '<?php
            $lockFile = "' . addslashes($repo->getLocksDir()) . DIRECTORY_SEPARATOR . 'DOS-CRASH-01.lock";
            $fp = fopen($lockFile, "cb");
            flock($fp, LOCK_EX);
            echo "LOCKED\n";
            exit(99);
            ';

            $proc = proc_open('"' . PHP_BINARY . '"', [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            fwrite($pipes[0], $crashScript); fclose($pipes[0]);
            fgets($pipes[1]);
            fclose($pipes[1]); fclose($pipes[2]);
            $code = proc_close($proc);
            Assert::assertEquals(99, $code, "Child crashed abruptly with exit 99");

            $claim = new ExtractedClaim('CLM-01', 'After Crash', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95);
            $d = new ResearchDossier('DOS-CRASH-01', 'TASK-CR1', 'core', DepthProfile::LEVEL_L1, '2026-11-16T00:00:00Z', null, [], [$claim]);

            $id = $repo->save($d->seal());
            Assert::assertEquals('DOS-CRASH-01', $id, "Save succeeded immediately after child termination");
            Assert::assertTrue($repo->get('DOS-CRASH-01')->verifyIntegrity(), "Dossier integrity valid after recovery");
        } finally {
            self::removeDir($dir);
        }
    }
}
