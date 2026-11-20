<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

class ResearchOrchestratorTest
{
    public static function run(): void
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_orch_test_' . bin2hex(random_bytes(4));
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'research' . DIRECTORY_SEPARATOR . 'dossiers', 0755, true);

        try {
            $resolver = new PathResolver($tmpDir);
            $repo = new ResearchDossierRepository(storageDir: $tmpDir . DIRECTORY_SEPARATOR . 'research', enableKnowledgeStore: false);
            $evidence = new EvidenceCollector($resolver);
            $verifier = new Gate0ClearanceVerifier($repo, $resolver, $evidence);

            $orchestrator = new ResearchOrchestrator(
                pathResolver: $resolver,
                dossierRepository: $repo,
                clearanceVerifier: $verifier,
                evidenceCollector: $evidence
            );

            self::testLevel0Lifecycle($orchestrator, $repo);
            self::testMultiDomainAndComparisonLifecycle($resolver, $repo, $verifier, $evidence);
            self::testRetrievalAndClaimsAssembly($resolver, $repo, $verifier, $evidence);
            self::testGetValidDossierAndIntegrity($orchestrator, $repo);
            self::testVerifyClearanceDelegation($orchestrator);
        } finally {
            self::recursiveRemove($tmpDir);
        }
    }

    private static function testLevel0Lifecycle(ResearchOrchestrator $orchestrator, ResearchDossierRepository $repo): void
    {
        $request = new ResearchRequest(
            taskId: 'TASK-ORCH-L0',
            taskPrompt: 'Fix minor typo in local variable name',
            depth: DepthProfile::l0(),
            stage: 1
        );

        $dossier = $orchestrator->conductResearch($request);

        Assert::assertTrue($dossier->isSealed(), 'L0 dossier is cryptographically sealed');
        Assert::assertTrue($dossier->verifyIntegrity(), 'L0 dossier verifies cryptographic hash integrity');
        Assert::assertEquals(DepthProfile::LEVEL_L0, $dossier->researchDepth, 'Dossier depth is L0');
        Assert::assertEquals(ResearchDossier::GATE_STATUS_EXEMPT, $dossier->gate0Status, 'L0 dossier status is EXEMPT');
        Assert::assertTrue($repo->has($dossier->dossierId), 'L0 dossier is persisted in repository');
    }

    private static function testMultiDomainAndComparisonLifecycle(
        PathResolver $resolver,
        ResearchDossierRepository $repo,
        Gate0ClearanceVerifier $verifier,
        EvidenceCollector $evidence
    ): void {
        $orchestrator = new ResearchOrchestrator(
            pathResolver: $resolver,
            dossierRepository: $repo,
            clearanceVerifier: $verifier,
            evidenceCollector: $evidence
        );

        $request = new ResearchRequest(
            taskId: 'TASK-ORCH-MULTI',
            taskPrompt: 'Evaluate database cache layer architecture',
            depth: DepthProfile::l3(),
            stage: 2,
            targetDomains: ['A', 'C'],
            targetTechnologies: ['redis', 'memcached'],
            activeProfile: ['project_name' => 'ArchitectureDemo', 'technologies' => ['backend_framework' => 'laravel']]
        );

        $dossier = $orchestrator->conductResearch($request);

        Assert::assertTrue($dossier->isSealed(), 'Multi-domain dossier is sealed');
        Assert::assertEquals(DepthProfile::LEVEL_L3, $dossier->researchDepth, 'Dossier has L3 depth');
        Assert::assertCount(1, $dossier->comparisonMatrices, 'Trade-off comparison matrix generated for technology options');
        Assert::assertStringContains('redis', json_encode($dossier->comparisonMatrices), 'Comparison matrix contains candidate technologies');
    }

    private static function testRetrievalAndClaimsAssembly(
        PathResolver $resolver,
        ResearchDossierRepository $repo,
        Gate0ClearanceVerifier $verifier,
        EvidenceCollector $evidence
    ): void {
        $sourceDef = new SourceDefinition(
            sourceId: 'SRC-MOCK-DOCS',
            officialName: 'Mock Framework Docs',
            description: 'Mock documentation source',
            owner: ['entity_name' => 'Mock Org', 'entity_type' => 'Standard Body'],
            canonicalUrl: 'https://docs.example.com',
            domains: ['A'],
            subdomains: ['A.01'],
            authorityTier: 1
        );

        $mockProvider = new class implements RetrievalProviderInterface {
            public function getName(): string { return 'mock'; }
            public function supports(SourceDefinition $source): bool { return true; }
            public function retrieve(string $target, SourceDefinition $source, array $options = []): array {
                $markdown = "# Section\n\nImmutable objects prevent unintended mutations across services.\n\n"
                    . "Strict typing improves static analysis guarantees.";
                return [
                    'content' => $markdown,
                    'hash' => hash('sha256', $markdown),
                    'format' => 'markdown',
                    'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                ];
            }
        };

        $retrievalManager = new RetrievalManager(providers: [$mockProvider]);

        $orchestrator = new ResearchOrchestrator(
            pathResolver: $resolver,
            dossierRepository: $repo,
            clearanceVerifier: $verifier,
            retrievalManager: $retrievalManager,
            evidenceCollector: $evidence
        );

        $request = new ResearchRequest(
            taskId: 'TASK-RETRIEVAL-01',
            taskPrompt: 'Investigate value object immutability',
            depth: DepthProfile::l1(),
            stage: 1,
            metadata: ['sources' => [$sourceDef]]
        );

        $dossier = $orchestrator->conductResearch($request);

        Assert::assertTrue($dossier->isSealed(), 'Dossier from retrieval is sealed');
        Assert::assertCount(1, $dossier->sourcesConsulted, 'Source definition recorded in sourcesConsulted');
        Assert::assertTrue(count($dossier->claims) >= 1, 'Claims extracted from retrieved markdown');
        Assert::assertEquals(ResearchDossier::GATE_STATUS_PASSED, $dossier->gate0Status, 'Cleared dossier receives GATE_STATUS_PASSED');
    }

    private static function testGetValidDossierAndIntegrity(ResearchOrchestrator $orchestrator, ResearchDossierRepository $repo): void
    {
        $dossier = (new ResearchDossier(
            dossierId: 'DOS-LOOKUP-01',
            taskId: 'TASK-LOOKUP-01',
            projectIdentity: 'LookupApp',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400)
        ))->seal();

        $repo->save($dossier);

        $fetched = $orchestrator->getValidDossier('DOS-LOOKUP-01');
        Assert::assertTrue($fetched !== null, 'getValidDossier finds active valid dossier');
        Assert::assertEquals('DOS-LOOKUP-01', $fetched->dossierId, 'Dossier ID matches');

        $missing = $orchestrator->getValidDossier('DOS-DOES-NOT-EXIST');
        Assert::assertTrue($missing === null, 'getValidDossier returns null for nonexistent dossier');
    }

    private static function testVerifyClearanceDelegation(ResearchOrchestrator $orchestrator): void
    {
        $res = $orchestrator->verifyClearance('TASK-NOT-FOUND-CLEARANCE');
        Assert::assertTrue($res->isBlocked(), 'verifyClearance delegates to Gate0ClearanceVerifier fail-closed');
        Assert::assertEquals(Gate0ClearanceResult::STATUS_BLOCKED, $res->status, 'Clearance status is BLOCKED');
    }

    private static function recursiveRemove(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? self::recursiveRemove($path) : @unlink($path);
        }
        @rmdir($dir);
    }
}
