<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Models\SourceAuditRecord;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\SourceEligibilityDecision;
use UniversalAgentOS\Research\Models\SourceLifecycleState;
use UniversalAgentOS\Research\Registry\SourceRegistry;
use UniversalAgentOS\Research\Repository\SourceRepository;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class SourceRegistryLifecycleTest
{
    public static function run(): void
    {
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_registry_lifecycle_test_' . bin2hex(random_bytes(4));
        @mkdir($tempDir, 0755, true);

        try {
            $repo = new SourceRepository(storageDir: $tempDir);
            $registry = new SourceRegistry(repository: $repo);
            $fixture = RdeiSchemaFixtures::getValidSourceDef();
            $fixture['source_id'] = 'src-lifecycle-01';
            $fixture['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', time() - 1800); // 30m ago
            $fixture['access_mechanisms'][0]['verified_at'] = $fixture['last_verified_at'];
            $fixture['access_mechanisms'][0]['verification_status'] = 'fully_tested';
            $fixture['verification_profile']['connectivity_tested'] = true;
            $fixture['verification_profile']['endpoint_verified'] = true;
            $source = SourceDefinition::fromArray($fixture);

            // 1. Initial Source Registration
            $initialDecision = $registry->register($source, actor: 'agent-dev');
            Assert::assertTrue($registry->has('src-lifecycle-01'), "Registry contains source after registration");
            Assert::assertTrue($initialDecision->isFullyEligible(), "Initial eligibility decision is fully eligible");
            Assert::assertEquals(SourceEligibilityDecision::STATUS_ELIGIBLE, $initialDecision->status, "Status is eligible");

            // 2. Prevent Duplicate Source IDs
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($registry, $source) {
                $registry->register($source, actor: 'agent-dev');
            }, "Rejects registering duplicate source_id");

            // 3. Invariant: Stored successfully != automatically eligible
            $expSecFixture = $fixture;
            $expSecFixture['source_id'] = 'src-lifecycle-expired-sec';
            $expSecFixture['domains'] = ['C'];
            $expSecFixture['subdomains'] = ['C.01'];
            // Valid calendar date but 48 hours old (Security domain TTL is 6h)
            $expSecFixture['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', time() - (48 * 3600));
            $expSecFixture['access_mechanisms'][0]['verified_at'] = $expSecFixture['last_verified_at'];
            $expSecSource = SourceDefinition::fromArray($expSecFixture);

            $expSecDecision = $registry->register($expSecSource, actor: 'agent-dev');
            Assert::assertTrue($registry->has('src-lifecycle-expired-sec'), "Source was successfully persisted");
            Assert::assertFalse($expSecDecision->isEligible, "Expired security source is NOT eligible merely because it was stored");
            Assert::assertEquals(SourceEligibilityDecision::STATUS_INELIGIBLE, $expSecDecision->status, "Status is ineligible");

            // 4. Source Update
            $updatedFixture = $fixture;
            $updatedFixture['description'] = 'Updated description for lifecycle testing';
            $updatedSource = SourceDefinition::fromArray($updatedFixture);
            $updateDecision = $registry->update($updatedSource, actor: 'agent-dev', reason: 'Routine metadata refresh');
            Assert::assertTrue($updateDecision->isFullyEligible(), "Updated source remains eligible");
            $fetched = $registry->get('src-lifecycle-01');
            Assert::assertEquals('Updated description for lifecycle testing', $fetched->description, "Description updated on disk");

            // 5. Lifecycle State Transitions
            // 5a. active -> suspended
            $susDecision = $registry->transitionState('src-lifecycle-01', SourceLifecycleState::STATE_SUSPENDED, 'Routine audit check', 'auditor');
            Assert::assertEquals(SourceEligibilityDecision::STATUS_SUSPENDED, $susDecision->status, "State transitioned to suspended");
            Assert::assertFalse($susDecision->isEligible, "Suspended source is not eligible");

            // 5b. suspended -> active
            $actDecision = $registry->transitionState('src-lifecycle-01', SourceLifecycleState::STATE_ACTIVE, 'Audit passed', 'auditor');
            Assert::assertEquals(SourceEligibilityDecision::STATUS_ELIGIBLE, $actDecision->status, "State transitioned back to active");
            Assert::assertTrue($actDecision->isEligible, "Active source is eligible again");

            // 5c. active -> deprecated
            $depDecision = $registry->transitionState('src-lifecycle-01', SourceLifecycleState::STATE_DEPRECATED, 'Superseded by newer standard', 'architect');
            Assert::assertEquals(SourceEligibilityDecision::STATUS_DEPRECATED, $depDecision->status, "State transitioned to deprecated");
            Assert::assertFalse($depDecision->isEligible, "Deprecated source is not eligible");

            // 5d. deprecated -> revoked
            $revDecision = $registry->transitionState('src-lifecycle-01', SourceLifecycleState::STATE_REVOKED, 'Security compromise identified', 'cso');
            Assert::assertEquals(SourceEligibilityDecision::STATUS_REVOKED, $revDecision->status, "State transitioned to revoked");
            Assert::assertFalse($revDecision->isEligible, "Revoked source is not eligible");

            // 5e. Re-activating revoked source is strictly forbidden (terminal state)
            Assert::assertThrows(InvalidArgumentException::class, static function () use ($registry) {
                $registry->transitionState('src-lifecycle-01', SourceLifecycleState::STATE_ACTIVE, 'Attempted un-revocation', 'hacker');
            }, "Cannot transition out of terminal state 'revoked'");

            // 6. Querying and Filtering
            $allSources = $registry->find();
            Assert::assertCount(2, $allSources, "find() returns both registered sources");

            $eligibleSources = $registry->find(['is_eligible' => true]);
            Assert::assertCount(0, $eligibleSources, "0 eligible sources currently (one revoked, one ineligible)");

            $domainCSources = $registry->find(['domain' => 'C']);
            Assert::assertCount(1, $domainCSources, "1 source in domain C");

            // 7. Audit Trail Verification
            $auditHistory = $registry->getAuditHistory('src-lifecycle-01');
            Assert::assertTrue(count($auditHistory) >= 6, "Audit trail contains all lifecycle and evaluation events");
            $actions = array_map(fn(SourceAuditRecord $r) => $r->action, $auditHistory);
            Assert::assertTrue(in_array(SourceAuditRecord::ACTION_REGISTER, $actions, true), "Audit trail contains registration");
            Assert::assertTrue(in_array(SourceAuditRecord::ACTION_UPDATE, $actions, true), "Audit trail contains update");
            Assert::assertTrue(in_array(SourceAuditRecord::ACTION_TRANSITION, $actions, true), "Audit trail contains transitions");
            Assert::assertTrue(in_array(SourceAuditRecord::ACTION_ELIGIBILITY_EVALUATION, $actions, true), "Audit trail contains evaluations");
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
