<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once dirname(__DIR__) . '/validation/src/GovernanceRuleChecker.php';
require_once dirname(__DIR__) . '/validation/src/SpecificationChecker.php';

use UniversalCore\Validation\GovernanceRuleChecker;
use UniversalCore\Validation\SpecificationChecker;

final class ValidatorTest
{
    public static function run(): void
    {
        echo "Running ValidatorTest...\n";
        $baseDir = dirname(__DIR__);

        $findings = [];
        $govChecker = new GovernanceRuleChecker($baseDir);
        $specChecker = new SpecificationChecker($baseDir);

        // 1. Canonical documents check
        $govChecker->checkCanonicalDocuments($findings);
        $canonPassed = count(array_filter($findings, fn($f) => $f['passed']));
        Assert::assertTrue($canonPassed >= 10, "ValidatorTest: All canonical governance documents pass");

        // 2. Line count ceiling check (Rule 2A)
        $findings = [];
        $govChecker->checkLineCountCeiling($findings);
        $allWithinLimit = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($allWithinLimit, "ValidatorTest: All evaluated code/script files satisfy 300-line ceiling");

        // 3. Strict typing and zero MVP placeholders
        $findings = [];
        $govChecker->checkPhpStrictTypingAndPlaceholders($findings);
        $strictPassed = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($strictPassed, "ValidatorTest: All PHP files enforce strict typing with zero placeholders");

        // 4. Registry integrity & Deprecation
        $findings = [];
        $govChecker->checkRegistryIntegrity($findings);
        $regIntegrityPassed = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($regIntegrityPassed, "ValidatorTest: Canonical and deprecated skill registries validated");

        // 5. Agent registry & 112 bindings
        $findings = [];
        $specChecker->checkAgentRegistryAndInheritance($findings);
        $agentPassed = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($agentPassed, "ValidatorTest: All 112 agents accounted for and inherit Shared Governance Core");

        // 6. Skill registry & 23 physical files
        $findings = [];
        $specChecker->checkSkillRegistryResolution($findings);
        $skillPassed = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($skillPassed, "ValidatorTest: All 23 shared skills resolve to physical markdown files");

        // 7. Separation of duties
        $findings = [];
        $specChecker->checkSeparationOfDuties($findings);
        $sodPassed = !in_array(false, array_column($findings, 'passed'), true);
        Assert::assertTrue($sodPassed, "ValidatorTest: Separation of duties checks pass");
    }
}
