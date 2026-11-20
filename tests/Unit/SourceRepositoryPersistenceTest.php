<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Repository\SourceRepository;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class SourceRepositoryPersistenceTest
{
    public static function run(): void
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_source_repo_test_' . bin2hex(random_bytes(4));
        @mkdir($tempDir, 0755, true);

        try {
            $repo = new SourceRepository(storageDir: $tempDir);
            $fixture = RdeiSchemaFixtures::getValidSourceDef();
            $fixture['source_id'] = 'src-test-persistence-01';
            $source = SourceDefinition::fromArray($fixture);

            // 1. Save and Retrieve Source
            $savedPath = $repo->save($source);
            Assert::assertTrue(file_exists($savedPath), "Source file persisted to disk");
            Assert::assertTrue($repo->has('src-test-persistence-01'), "Repository reports source exists");

            $restored = $repo->get('src-test-persistence-01');
            Assert::assertTrue($restored !== null, "Restored source is not null");
            Assert::assertEquals($source->sourceId, $restored->sourceId, "source_id preserved");
            Assert::assertEquals($source->officialName, $restored->officialName, "official_name preserved");
            Assert::assertEquals($source->authorityTier, $restored->authorityTier, "authority_tier preserved");
            Assert::assertEquals($source->canonicalUrl, $restored->canonicalUrl, "canonical_url preserved");
            Assert::assertCount(count($source->accessMechanisms), $restored->accessMechanisms, "access_mechanisms count preserved");

            // 2. Snapshot creation on update
            $updatedFixture = $fixture;
            $updatedFixture['officialName'] = 'Updated Official Name';
            $updatedSource = SourceDefinition::fromArray($updatedFixture);
            $repo->save($updatedSource);

            $historyDir = $tempDir . DIRECTORY_SEPARATOR . 'sources' . DIRECTORY_SEPARATOR . 'history' . DIRECTORY_SEPARATOR . 'src-test-persistence-01';
            Assert::assertTrue(is_dir($historyDir), "History directory created for source");
            $historyFiles = array_diff((array)scandir($historyDir), ['.', '..']);
            Assert::assertTrue(count($historyFiles) >= 1, "At least one historical snapshot preserved");

            // 3. Path Traversal & Identifier Safety
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($repo) {
                $repo->get('../malicious-source');
            }, "Rejects path traversal in get()");

            Assert::assertThrows(InvalidArgumentException::class, static function () use ($repo) {
                $repo->has('src/invalid/slashes');
            }, "Rejects slashes in has()");

            // 4. Audit Trail Recording and Retrieval
            $auditRec1 = SourceAuditRecord::create(
                sourceId: 'src-test-persistence-01',
                action: SourceAuditRecord::ACTION_REGISTER,
                actor: 'test-agent',
                reason: 'Initial test registration'
            );
            $repo->recordAudit($auditRec1);

            $auditRec2 = SourceAuditRecord::create(
                sourceId: 'src-test-persistence-01',
                action: SourceAuditRecord::ACTION_TRANSITION,
                actor: 'test-agent',
                fromState: 'active',
                toState: 'suspended',
                reason: 'Test suspension'
            );
            $repo->recordAudit($auditRec2);

            $history = $repo->getAuditHistory('src-test-persistence-01');
            Assert::assertCount(2, $history, "Retrieves 2 audit records");
            Assert::assertEquals(SourceAuditRecord::ACTION_REGISTER, $history[0]->action, "First audit action matches");
            Assert::assertEquals(SourceAuditRecord::ACTION_TRANSITION, $history[1]->action, "Second audit action matches");
            Assert::assertEquals('suspended', $history[1]->toState, "Second audit to_state matches");

            // 5. Querying and filtering (all)
            $fixture2 = $fixture;
            $fixture2['source_id'] = 'src-test-persistence-02';
            $fixture2['authority_tier'] = 3;
            $fixture2['domains'] = ['C'];
            $repo->save(SourceDefinition::fromArray($fixture2));

            $allA = $repo->all(['domain' => 'A']);
            Assert::assertCount(1, $allA, "Filtered by domain A returns 1 source");

            $allC = $repo->all(['domain' => 'C']);
            Assert::assertCount(1, $allC, "Filtered by domain C returns 1 source");

            $allTier1 = $repo->all(['authority_tier' => 1]);
            Assert::assertCount(1, $allTier1, "Filtered by authority tier 1 returns 1 source");

            // 6. Removal
            Assert::assertTrue($repo->remove('src-test-persistence-02'), "Source removal returns true");
            Assert::assertFalse($repo->has('src-test-persistence-02'), "Removed source no longer reported in repo");
        } finally {
            self::recursiveDelete($tempDir);
        }
    }

    private static function recursiveDelete(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = array_diff((array)scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? self::recursiveDelete($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
