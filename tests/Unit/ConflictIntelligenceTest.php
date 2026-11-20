<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Intelligence\ConflictDetector;
use UniversalAgentOS\Research\Intelligence\ConflictResolver;
use UniversalAgentOS\Research\Models\ConflictRecord;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResolutionDecision;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class ConflictIntelligenceTest
{
    public static function run(): void
    {
        $detector = new ConflictDetector();
        $resolver = new ConflictResolver();

        // 1. Detection: Version Divergence
        $claimV1 = new ExtractedClaim(
            claimId: 'CLM-001',
            statement: 'Method Str::random is available in Laravel 10',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.90,
            applicableVersions: ['10.0'],
            citations: [['source_id' => 'src-laravel-10', 'content_hash' => str_repeat('a', 64), 'quote_segment' => 'Str::random available']]
        );

        $claimV2 = new ExtractedClaim(
            claimId: 'CLM-002',
            statement: 'Method Str::random is removed in Laravel 11',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.90,
            applicableVersions: ['11.0'],
            citations: [['source_id' => 'src-laravel-11', 'content_hash' => str_repeat('b', 64), 'quote_segment' => 'Str::random removed']]
        );

        $conflicts = $detector->detectConflicts([$claimV1, $claimV2]);
        Assert::assertEquals(1, count($conflicts), 'Detected 1 conflict');
        Assert::assertEquals(ConflictRecord::TYPE_VERSION_DIVERGENCE, $conflicts[0]->contradictionType, 'Classified as VERSION_DIVERGENCE');

        // Resolution of Version Divergence: target profile Laravel 11
        $resVersion = $resolver->resolveConflict($conflicts[0], ['framework_version' => '11.0']);
        Assert::assertEquals(ResolutionDecision::STATUS_RESOLVED, $resVersion->status, 'Version divergence resolved');
        Assert::assertEquals('CLM-002', $resVersion->prevailingClaim->claimId, 'Claim applying to active version 11.0 prevails');
        Assert::assertEquals('CLM-001', $resVersion->supersededClaim->claimId, 'Claim for version 10.0 preserved as superseded');
        Assert::assertFalse($resVersion->blocksGate0, 'Resolved version divergence does not block Gate 0');
        Assert::assertEquals(2, count($resVersion->citationsPreserved), 'Preserved citations from both claims');

        // 2. Detection & Resolution: Authority Hierarchy (Tier 1 vs Tier 5)
        $claimOfficial = new ExtractedClaim(
            claimId: 'CLM-003',
            statement: 'Password hashing with Argon2id is secure and recommended by PHP official manual',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.95,
            applicableVersions: ['8.2', '8.3'],
            citations: [['source_id' => 'src-php-net', 'content_hash' => str_repeat('c', 64), 'quote_segment' => 'Argon2id is secure']]
        );

        $claimBlog = new ExtractedClaim(
            claimId: 'CLM-004',
            statement: 'Argon2id is insecure and outdated according to blog post',
            status: ExtractedClaim::STATUS_UNVERIFIED_SPECULATION,
            confidenceScore: 0.30,
            applicableVersions: ['8.2', '8.3'],
            citations: [['source_id' => 'src-random-blog', 'content_hash' => str_repeat('d', 64), 'quote_segment' => 'Argon2id is insecure']]
        );

        $fixOfficial = RdeiSchemaFixtures::getValidSourceDef();
        $fixOfficial['source_id'] = 'src-php-net';
        $fixOfficial['authority_tier'] = 1;
        $sourceOfficial = SourceDefinition::fromArray($fixOfficial);

        $fixBlog = RdeiSchemaFixtures::getValidSourceDef();
        $fixBlog['source_id'] = 'src-random-blog';
        $fixBlog['authority_tier'] = 5;
        $sourceBlog = SourceDefinition::fromArray($fixBlog);

        $conflictsAuth = $detector->detectConflicts(
            [$claimOfficial, $claimBlog],
            ['src-php-net' => $sourceOfficial, 'src-random-blog' => $sourceBlog]
        );
        Assert::assertEquals(1, count($conflictsAuth), 'Detected 1 authority conflict');
        Assert::assertTrue($conflictsAuth[0]->isCritical, 'Conflict on crypto/security is marked critical');

        $resAuth = $resolver->resolveConflict($conflictsAuth[0]);
        Assert::assertEquals(ResolutionDecision::STATUS_RESOLVED, $resAuth->status, 'Dispute resolved by authority hierarchy');
        Assert::assertEquals('CLM-003', $resAuth->prevailingClaim->claimId, 'Tier 1 official documentation overrides Tier 5 blog');
        Assert::assertFalse($resAuth->blocksGate0, 'Resolved dispute does not block Gate 0');

        // 3. Resolution: Ineligible / Revoked Source Check
        $fixRevoked = RdeiSchemaFixtures::getValidSourceDef();
        $fixRevoked['source_id'] = 'src-deprecated-vendor';
        $fixRevoked['authority_tier'] = 2;
        $fixRevoked['status'] = 'deprecated';
        $sourceRevoked = SourceDefinition::fromArray($fixRevoked);

        $claimRevoked = new ExtractedClaim(
            claimId: 'CLM-005',
            statement: 'Encryption key format XYZ is required',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.85,
            applicableVersions: ['8.3'],
            citations: [['source_id' => 'src-deprecated-vendor', 'content_hash' => str_repeat('e', 64), 'quote_segment' => 'key format XYZ required']]
        );

        $claimActive = new ExtractedClaim(
            claimId: 'CLM-006',
            statement: 'Encryption key format XYZ is optional in active standard',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.85,
            applicableVersions: ['8.3'],
            citations: [['source_id' => 'src-php-net', 'content_hash' => str_repeat('f', 64), 'quote_segment' => 'key format XYZ optional']]
        );

        $conflictIneligible = new ConflictRecord(
            conflictId: 'CNF-INELIGIBLE-01',
            claimA: $claimRevoked,
            claimB: $claimActive,
            contradictionType: ConflictRecord::TYPE_TRUE_TECHNICAL_CONTRADICTION,
            targetSubject: 'encryption key format',
            isCritical: true,
            description: 'Conflict between revoked source and active source',
            detectedAt: '2026-11-17T12:00:00Z',
            sourceA: $sourceRevoked,
            sourceB: $sourceOfficial
        );

        $resIneligible = $resolver->resolveConflict($conflictIneligible);
        Assert::assertEquals(ResolutionDecision::STATUS_RESOLVED, $resIneligible->status, 'Ineligible source conflict resolved');
        Assert::assertEquals('CLM-006', $resIneligible->prevailingClaim->claimId, 'Active source prevails over revoked source');

        // 4. Critical Technical Contradiction between equivalent Tier 1 sources BLOCKS Gate 0
        $fixIetf = RdeiSchemaFixtures::getValidSourceDef();
        $fixIetf['source_id'] = 'src-ietf-rfc';
        $fixIetf['authority_tier'] = 1;
        $sourceIetf = SourceDefinition::fromArray($fixIetf);

        $conflictCriticalEqual = new ConflictRecord(
            conflictId: 'CNF-CRITICAL-EQUAL-01',
            claimA: new ExtractedClaim('CLM-007', 'Cipher algorithm X is insecure for auth tokens', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95, ['8.3'], [['source_id' => 'src-ietf-rfc', 'content_hash' => str_repeat('1', 64), 'quote_segment' => 'insecure']]),
            claimB: new ExtractedClaim('CLM-008', 'Cipher algorithm X is secure for auth tokens', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95, ['8.3'], [['source_id' => 'src-php-net', 'content_hash' => str_repeat('2', 64), 'quote_segment' => 'secure']]),
            contradictionType: ConflictRecord::TYPE_TRUE_TECHNICAL_CONTRADICTION,
            targetSubject: 'cipher algorithm security',
            isCritical: true,
            description: 'True critical contradiction between two Tier 1 sources',
            detectedAt: '2026-11-17T12:00:00Z',
            sourceA: $sourceIetf,
            sourceB: $sourceOfficial
        );

        $resCritical = $resolver->resolveConflict($conflictCriticalEqual);
        Assert::assertEquals(ResolutionDecision::STATUS_UNRESOLVED_CRITICAL, $resCritical->status, 'Critical contradiction is marked UNRESOLVED_CRITICAL');
        Assert::assertTrue($resCritical->blocksGate0, 'Critical unresolved contradiction strictly blocks Gate 0');
        Assert::assertTrue($resCritical->requiresFollowUpEvidence, 'Requires follow-up evidence');
        Assert::assertTrue(str_contains($resCritical->followUpRequirement, 'Department C'), 'Escalates to Dept C');
    }
}
