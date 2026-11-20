<?php
declare(strict_types=1);

namespace UniversalCore\Tests;

require_once __DIR__ . '/Assert.php';
require_once dirname(__DIR__) . '/runtime/src/SkillEngine.php';

use UniversalCore\Runtime\SkillEngine;

final class SkillEngineTest
{
    public static function run(): void
    {
        echo "Running SkillEngineTest...\n";
        $engine = new SkillEngine();

        // 1. Skill discovery by agent ID
        $a11ySkills = $engine->discoverSkillIds('A11Y-001');
        Assert::assertTrue(count($a11ySkills) > 0, "SkillEngineTest: A11Y-001 has discovered skills");
        Assert::assertTrue(in_array('SKILL-A11Y-001', $a11ySkills, true), "SkillEngineTest: SKILL-A11Y-001 discovered for A11Y-001");

        // 2. Skill discovery by keyword
        $secSkills = $engine->discoverSkillIds('UNKNOWN-001', null, ['cryptography', 'security']);
        Assert::assertTrue(count($secSkills) > 0, "SkillEngineTest: Security skills discovered by keywords");

        // 3. Multiple skill loading
        $toLoad = array_slice($a11ySkills, 0, 2);
        $loaded = $engine->loadSkills($toLoad);
        Assert::assertEquals(count($toLoad), count($loaded), "SkillEngineTest: Multiple skills loaded");

        foreach ($loaded as $id => $skillData) {
            Assert::assertTrue(isset($skillData['content']) && strlen($skillData['content']) > 100, "SkillEngineTest: Loaded skill {$id} has physical markdown content");
        }

        // 4. Duplicate handling
        $dupList = array_merge($toLoad, $toLoad);
        $loadedDup = $engine->loadSkills($dupList);
        Assert::assertEquals(count($toLoad), count($loadedDup), "SkillEngineTest: Duplicate skill IDs handled without duplication");

        // 5. Missing skill rejection
        $exceptionThrown = false;
        try {
            $engine->loadSkills(['NON-EXISTENT-SKILL-999']);
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
        }
        Assert::assertTrue($exceptionThrown, "SkillEngineTest: Missing skill ID throws RuntimeException");
    }
}
