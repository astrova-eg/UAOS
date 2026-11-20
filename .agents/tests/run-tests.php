<?php
declare(strict_types=1);

require_once __DIR__ . '/Assert.php';
require_once __DIR__ . '/Gate4Test.php';
require_once __DIR__ . '/RegistryTest.php';
require_once __DIR__ . '/SkillEngineTest.php';
require_once __DIR__ . '/ToolGatewayTest.php';
require_once __DIR__ . '/OrchestratorTest.php';
require_once __DIR__ . '/ValidatorTest.php';
require_once __DIR__ . '/RdeiSchemaTest.php';

use UniversalCore\Tests\Assert;
use UniversalCore\Tests\Gate4Test;
use UniversalCore\Tests\RegistryTest;
use UniversalCore\Tests\SkillEngineTest;
use UniversalCore\Tests\ToolGatewayTest;
use UniversalCore\Tests\OrchestratorTest;
use UniversalCore\Tests\ValidatorTest;
use UniversalCore\Tests\RdeiSchemaTest;

echo "=======================================================\n";
echo " UNIVERSAL CORE AUTOMATED TEST SUITE RUNNER\n";
echo "=======================================================\n\n";

Assert::reset();

$tests = [
    'Gate4Test' => fn() => Gate4Test::run(),
    'RegistryTest' => fn() => RegistryTest::run(),
    'SkillEngineTest' => fn() => SkillEngineTest::run(),
    'ToolGatewayTest' => fn() => ToolGatewayTest::run(),
    'OrchestratorTest' => fn() => OrchestratorTest::run(),
    'ValidatorTest' => fn() => ValidatorTest::run(),
    'RdeiSchemaTest' => fn() => RdeiSchemaTest::run(),
];

foreach ($tests as $name => $fn) {
    try {
        $fn();
    } catch (\Throwable $e) {
        Assert::assertTrue(false, "{$name} threw unhandled exception: " . $e->getMessage());
    }
}

$stats = Assert::getStats();
$total = $stats['passed'] + $stats['failed'];

echo "\n=======================================================\n";
echo "TEST SUITE SUMMARY:\n";
echo "  Total Assertions: {$total}\n";
echo "  Passed:           {$stats['passed']}\n";
echo "  Failed:           {$stats['failed']}\n";
echo "=======================================================\n";

if ($stats['failed'] > 0) {
    echo "FAILURES:\n";
    foreach ($stats['failures'] as $f) {
        echo " - {$f}\n";
    }
    exit(1);
}

echo "ALL AUTOMATED TESTS PASSED SUCCESSFULLY (100%)\n";
exit(0);
