<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use ReflectionClass;
use ReflectionMethod;
use UniversalAgentOS\Research\Contracts\DocumentParserInterface;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Contracts\TrustEvaluatorInterface;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Tests\Assert;

class ResearchContractsAndModelsTest
{
    public static function run(): void
    {
        self::testContractsReflection();
        self::testDepthProfile();
        self::testResearchRequest();
        self::testExtractedClaim();
        self::testSourceDefinition();
        self::testResearchDossier();
        self::testGate0ClearanceResult();
    }

    private static function testContractsReflection(): void
    {
        $contracts = [
            ResearchEngineInterface::class => ['conductResearch', 'getValidDossier', 'verifyClearance'],
            RetrievalProviderInterface::class => ['getName', 'supports', 'retrieve'],
            DocumentParserInterface::class => ['supportsFormat', 'parse'],
            TrustEvaluatorInterface::class => ['calculateAuthorityScore', 'evaluateClaimConfidence', 'classifyEvidentialState', 'assessTrust'],
            DossierRepositoryInterface::class => ['save', 'get', 'findByTaskId', 'has', 'verifyIntegrity'],
        ];

        foreach ($contracts as $interface => $expectedMethods) {
            $ref = new ReflectionClass($interface);
            Assert::assertTrue($ref->isInterface(), "{$interface} is an interface");
            foreach ($expectedMethods as $method) {
                Assert::assertTrue($ref->hasMethod($method), "{$interface} declares {$method}()");
            }
        }
    }

    private static function testDepthProfile(): void
    {
        $l3 = DepthProfile::forLevel('L3', 'Architecture review');
        Assert::assertEquals('L3', $l3->level, "Level is L3");
        Assert::assertEquals(3, $l3->minSources, "L3 minSources is 3");
        Assert::assertTrue($l3->requiresTradeoffMatrix, "L3 mandates tradeoff matrix");
        Assert::assertTrue($l3->requiresExternalResearch(), "L3 mandates external research");

        $l0 = DepthProfile::l0();
        Assert::assertFalse($l0->requiresExternalResearch(), "L0 does not require external research");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new DepthProfile('L99');
        }, "Rejects invalid depth level");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new DepthProfile('L1', minSources: -1);
        }, "Rejects negative minSources");

        $data = $l3->toArray();
        $restored = DepthProfile::fromArray($data);
        Assert::assertEquals($l3->level, $restored->level, "DepthProfile round-trip level match");
        Assert::assertEquals($l3->maxExecutionTimeoutSeconds, $restored->maxExecutionTimeoutSeconds, "Round-trip timeout match");
    }

    private static function testResearchRequest(): void
    {
        $depth = DepthProfile::l2();
        $req = new ResearchRequest(
            taskId: 'TASK-20261115-01',
            taskPrompt: 'Research queue implementations for high throughput',
            depth: $depth,
            stage: 2,
            targetDomains: ['A'],
            targetTechnologies: ['php', 'redis']
        );

        Assert::assertEquals('TASK-20261115-01', $req->taskId, "Request taskId matches");
        Assert::assertEquals(2, $req->stage, "Request stage matches");

        $task = new TaskEnvelope('TASK-ENV-01', 'Test prompt for task envelope conversion', null, 3);
        $fromEnv = ResearchRequest::fromTaskEnvelope($task, 'L3');
        Assert::assertEquals('TASK-ENV-01', $fromEnv->taskId, "Converted request taskId matches");
        Assert::assertEquals('L3', $fromEnv->depth->level, "Converted request depth is L3");

        Assert::assertThrows(\InvalidArgumentException::class, static function () use ($depth) {
            new ResearchRequest('', 'Prompt', $depth);
        }, "Rejects empty taskId");

        Assert::assertThrows(\InvalidArgumentException::class, static function () use ($depth) {
            new ResearchRequest('TASK-01', 'Prompt', $depth, stage: 15);
        }, "Rejects stage > 14");

        $restored = ResearchRequest::fromArray($req->toArray());
        Assert::assertEquals($req->taskId, $restored->taskId, "ResearchRequest round-trip taskId match");
        Assert::assertEquals($req->depth->level, $restored->depth->level, "ResearchRequest round-trip depth match");
    }

    private static function testExtractedClaim(): void
    {
        $citation = [
            'source_id' => 'src-php-manual',
            'content_hash' => hash('sha256', 'DateTimeImmutable sample'),
            'quote_segment' => 'DateTimeImmutable represents an immutable date and time.',
        ];

        $claim = new ExtractedClaim(
            claimId: 'CLAIM-01',
            statement: 'Use DateTimeImmutable instead of DateTime',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.95,
            applicableVersions: ['8.2', '8.3'],
            citations: [$citation]
        );

        Assert::assertTrue($claim->isHighConfidence(), "Score 0.95 is high confidence");
        Assert::assertTrue($claim->isAuthoritative(), "Authoritative status acknowledged");
        Assert::assertFalse($claim->hasDispute(), "Has no dispute");

        $corrected = $claim->withStatus(ExtractedClaim::STATUS_EVIDENCE_VERIFIED);
        Assert::assertTrue($corrected->isAuthoritative(), "EVIDENCE VERIFIED is authoritative");
        Assert::assertEquals('EVIDENCE VERIFIED', $corrected->status, "Status updated immutably");

        Assert::assertThrows(\InvalidArgumentException::class, static function () use ($citation) {
            new ExtractedClaim('CLM-01', 'Statement', 'INVALID_STATUS', 0.8, [], [$citation]);
        }, "Rejects invalid claim status");

        Assert::assertThrows(\InvalidArgumentException::class, static function () use ($citation) {
            new ExtractedClaim('CLM-01', 'Statement', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 1.5, [], [$citation]);
        }, "Rejects confidence score > 1.0");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new ExtractedClaim('CLM-01', 'Statement', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, [], [
                ['source_id' => 'src-01'] // missing content_hash, quote_segment
            ]);
        }, "Rejects incomplete citation");

        $restored = ExtractedClaim::fromArray($claim->toArray());
        Assert::assertEquals($claim->claimId, $restored->claimId, "ExtractedClaim round-trip ID match");
        Assert::assertEquals($claim->confidenceScore, $restored->confidenceScore, "ExtractedClaim round-trip score match");
    }

    private static function testSourceDefinition(): void
    {
        $src = new SourceDefinition(
            sourceId: 'src-soft-php-net',
            officialName: 'PHP Official Documentation',
            description: 'The canonical documentation for the PHP programming language.',
            owner: [
                'institution_name' => 'The PHP Group',
                'institution_type' => 'open_source_foundation',
                'jurisdiction' => 'GLOBAL',
            ],
            canonicalUrl: 'https://www.php.net/docs.php',
            domains: ['A'],
            subdomains: ['A.01'],
            authorityTier: 1,
            authProfile: ['requires_auth' => false, 'auth_type' => 'none'],
            status: SourceDefinition::STATUS_ACTIVE
        );

        Assert::assertTrue($src->isAuthoritative(), "Tier 1 is authoritative");
        Assert::assertTrue($src->isAvailable(), "Active source is available");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new SourceDefinition('', 'Name', 'Desc', [], 'https://test.com', ['A'], ['A.01'], 1);
        }, "Rejects empty sourceId");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new SourceDefinition('src-test', 'Name', 'Desc', [], 'https://test.com', ['A'], ['A.01'], 7);
        }, "Rejects tier > 6");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new SourceDefinition(
                'src-test', 'Name', 'Desc', [], 'https://test.com', ['A'], ['A.01'], 2,
                authProfile: ['requires_auth' => true, 'auth_type' => 'api_key_header', 'env_var_key' => 'secret with spaces!']
            );
        }, "Rejects invalid secret format in env_var_key (C3 invariant)");

        $restored = SourceDefinition::fromArray($src->toArray());
        Assert::assertEquals($src->sourceId, $restored->sourceId, "SourceDefinition round-trip sourceId match");
        Assert::assertEquals($src->authorityTier, $restored->authorityTier, "SourceDefinition round-trip tier match");
    }

    private static function testResearchDossier(): void
    {
        $claim = new ExtractedClaim(
            claimId: 'CLM-01',
            statement: 'Test statement for dossier hashing',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.92,
            citations: [
                ['source_id' => 'src-01', 'content_hash' => hash('sha256', 'content'), 'quote_segment' => 'Quote segment']
            ]
        );

        $dossier = new ResearchDossier(
            dossierId: 'DOS-20261115-a1b2c3',
            taskId: 'TASK-20261115-01',
            projectIdentity: 'core',
            researchDepth: DepthProfile::LEVEL_L2,
            researchedAt: '2026-11-15T18:00:00Z',
            claims: [$claim],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        );

        Assert::assertEquals(64, strlen($dossier->computeHash()), "Dossier computes valid 64-char SHA-256 hash");
        Assert::assertFalse($dossier->verifyIntegrity(), "Unsealed dossier fails integrity verification");

        $sealed = $dossier->seal();
        Assert::assertTrue($sealed->verifyIntegrity(), "Sealed dossier passes cryptographic integrity verification");
        Assert::assertFalse($sealed->hasCriticalConflicts(), "Dossier has no critical conflicts");

        $withConflict = new ResearchDossier(
            dossierId: 'DOS-20261115-a1b2c4',
            taskId: 'TASK-20261115-02',
            projectIdentity: 'core',
            researchDepth: DepthProfile::LEVEL_L3,
            researchedAt: '2026-11-15T18:00:00Z',
            claims: [$claim],
            conflictsDetected: [
                ['conflict_id' => 'CONF-01', 'resolution_status' => 'ESCALATED']
            ]
        );
        Assert::assertTrue($withConflict->hasCriticalConflicts(), "Escalated conflict detected");

        $restored = ResearchDossier::fromArray($sealed->toArray());
        Assert::assertTrue($restored->verifyIntegrity(), "Restored dossier preserves hash and passes integrity");
    }

    private static function testGate0ClearanceResult(): void
    {
        $passed = Gate0ClearanceResult::passed(
            taskId: 'TASK-001',
            dossierId: 'DOS-20261115-4a7b9c',
            dossierHash: str_repeat('a', 64)
        );
        Assert::assertTrue($passed->isCleared(), "Passed is cleared");
        Assert::assertTrue($passed->isPassed(), "Passed isPassed is true");
        Assert::assertFalse($passed->isBlocked(), "Passed is not blocked");

        $blocked = Gate0ClearanceResult::blocked(
            taskId: 'TASK-002',
            reason: 'Missing authoritative evidence quorum',
            violations: ['Depth L3 requires 3 sources, only 1 available']
        );
        Assert::assertFalse($blocked->isCleared(), "Blocked is not cleared");
        Assert::assertTrue($blocked->isBlocked(), "Blocked isBlocked is true");
        Assert::assertCount(1, $blocked->violations, "Blocked records 1 violation");

        $exempt = Gate0ClearanceResult::exempt('TASK-003');
        Assert::assertTrue($exempt->isCleared(), "Exempt is cleared");
        Assert::assertTrue($exempt->isExempt(), "Exempt isExempt is true");

        Assert::assertThrows(\InvalidArgumentException::class, static function () {
            new Gate0ClearanceResult(
                status: Gate0ClearanceResult::STATUS_PASSED,
                taskId: 'TASK-004',
                reason: 'No dossier provided'
            );
        }, "Rejects PASSED result without dossierId/dossierHash");

        $restored = Gate0ClearanceResult::fromArray($passed->toArray());
        Assert::assertEquals($passed->status, $restored->status, "Gate0ClearanceResult round-trip status match");
        Assert::assertEquals($passed->dossierHash, $restored->dossierHash, "Gate0ClearanceResult round-trip hash match");
    }
}
