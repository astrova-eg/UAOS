<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use InvalidArgumentException;
use UniversalAgentOS\Research\Engine\ResearchDepthSelector;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Tests\Assert;

class ResearchDepthIntelligenceTest
{
    public static function run(): void
    {
        $selector = new ResearchDepthSelector();

        // 1. Trivial local change selects L0
        $l0Profile = $selector->selectDepth('Fix minor typo in comments', stage: 10);
        Assert::assertEquals(DepthProfile::LEVEL_L0, $l0Profile->level, 'Trivial typo maps to L0');
        Assert::assertFalse($l0Profile->requiresExternalResearch(), 'L0 requires no external research');
        Assert::assertEquals(0, $l0Profile->minSources, 'L0 minSources is 0');
        Assert::assertEquals(0, $l0Profile->maxQueries, 'L0 maxQueries is 0');

        // 2. Standard coding task selects L1
        $l1Profile = $selector->selectDepth('Update string helper utility function', stage: 10);
        Assert::assertEquals(DepthProfile::LEVEL_L1, $l1Profile->level, 'Standard method update in stage 10 maps to L1');
        Assert::assertTrue($l1Profile->requiresExternalResearch(), 'L1 requires external research');
        Assert::assertEquals(1, $l1Profile->minSources, 'L1 requires 1 source');

        // 3. Medium risk keywords / Stage 7-8 planning selects L2
        $l2Profile = $selector->selectDepth('Plan implementation of order controller and validation rule', stage: 7);
        Assert::assertEquals(DepthProfile::LEVEL_L2, $l2Profile->level, 'Controller and stage 7 map to L2');
        Assert::assertEquals(2, $l2Profile->minSources, 'L2 requires 2 sources');

        // 4. Stage 1-3 architecture / High-risk keywords selects L3
        $l3Stage = $selector->selectDepth('Design system caching and queue worker architecture', stage: 2);
        Assert::assertEquals(DepthProfile::LEVEL_L3, $l3Stage->level, 'Stage 2 architecture maps to L3');
        Assert::assertTrue($l3Stage->requiresTradeoffMatrix, 'L3 requires trade-off matrix');
        Assert::assertFalse($l3Stage->requiresConflictAudit, 'L3 does not mandate conflict audit');

        // 5. Critical risk keywords (auth, crypto, tenant_id) select L4
        $l4Auth = $selector->selectDepth('Implement multi-tenant auth gateway with token encryption', stage: 10);
        Assert::assertEquals(DepthProfile::LEVEL_L4, $l4Auth->level, 'Auth and token encryption map to L4');
        Assert::assertTrue($l4Auth->requiresTradeoffMatrix, 'L4 requires trade-off matrix');
        Assert::assertTrue($l4Auth->requiresConflictAudit, 'L4 mandates conflict audit');
        Assert::assertEquals(4, $l4Auth->minSources, 'L4 requires 4 sources');

        // 6. Domain C and D mandate L4
        $l4Domain = $selector->selectDepth('Update key rotation helper', stage: 10, targetDomains: ['C']);
        Assert::assertEquals(DepthProfile::LEVEL_L4, $l4Domain->level, 'Domain C mandates L4');

        $l4Tenant = $selector->selectDepth('Update row isolation filter', stage: 10, targetDomains: ['D']);
        Assert::assertEquals(DepthProfile::LEVEL_L4, $l4Tenant->level, 'Domain D mandates L4');

        // 7. Dependency blast radius elevates to L3 or L4
        $l3Blast = $selector->selectDepth('Update composer.json to include third-party package', stage: 10);
        Assert::assertEquals(DepthProfile::LEVEL_L3, $l3Blast->level, 'composer.json blast radius elevates to L3');

        // 8. ResearchRequest integration
        $req = new ResearchRequest(
            taskId: 'TASK-20261117-001',
            taskPrompt: 'Evaluate database schema migration for PostgreSQL',
            depth: DepthProfile::l1(),
            stage: 2,
            targetDomains: ['A']
        );
        $reqProfile = $selector->selectForRequest($req);
        Assert::assertEquals(DepthProfile::LEVEL_L3, $reqProfile->level, 'Request with stage 2 evaluates to L3');

        // 9. Dynamic Escalation Triggers
        $baseL1 = DepthProfile::l1();
        $escDep = $selector->escalate($baseL1, ResearchDepthSelector::ESCALATION_DEPRECATED_API, 'Str::random removed');
        Assert::assertEquals(DepthProfile::LEVEL_L3, $escDep->level, 'Escalated to L3 on deprecated API');

        $escCve = $selector->escalate($baseL1, ResearchDepthSelector::ESCALATION_ACTIVE_CVE, 'CVE-2026-1234');
        Assert::assertEquals(DepthProfile::LEVEL_L4, $escCve->level, 'Escalated to L4 on active CVE');
        Assert::assertTrue($escCve->requiresConflictAudit, 'Escalated L4 requires conflict audit');

        $escConflict = $selector->escalate($baseL1, ResearchDepthSelector::ESCALATION_CONTRADICTION);
        Assert::assertEquals(DepthProfile::LEVEL_L3, $escConflict->level, 'Escalated to L3 on contradiction');

        $escPkg = $selector->escalate($baseL1, ResearchDepthSelector::ESCALATION_UNVETTED_PACKAGE);
        Assert::assertEquals(DepthProfile::LEVEL_L3, $escPkg->level, 'Escalated to L3 on unvetted package');

        // 10. Fail-closed error handling
        $emptyCaught = false;
        try {
            $selector->selectDepth('');
        } catch (InvalidArgumentException) {
            $emptyCaught = true;
        }
        Assert::assertTrue($emptyCaught, 'Empty taskPrompt throws InvalidArgumentException');

        $invalidStageCaught = false;
        try {
            $selector->selectDepth('Some task', stage: 99);
        } catch (InvalidArgumentException) {
            $invalidStageCaught = true;
        }
        Assert::assertTrue($invalidStageCaught, 'Invalid stage throws InvalidArgumentException');
    }
}
