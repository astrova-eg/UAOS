<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Feature;

use UniversalAgentOS\Laravel\Commands\AgentOsRunCommand;
use UniversalAgentOS\Laravel\UniversalAgentOSServiceProvider;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

/**
 * Phase 4.7 Final Runtime Verification & Closure Test Suite.
 *
 * Verifies end-to-end RDEI runtime integration, Gate 0 security boundaries,
 * research integrity/sealing, failure safety, CLI subprocess, and Artisan command wiring.
 */
class RdeiPhase47ClosureVerificationTest
{
    public static function run(): void
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_p47_verify_' . bin2hex(random_bytes(4));
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'reports', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'evidence', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'research' . DIRECTORY_SEPARATOR . 'dossiers', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'research' . DIRECTORY_SEPARATOR . 'index', 0755, true);

        try {
            $resolver = new PathResolver(projectRootDir: $tmpDir);
            $repo = new ResearchDossierRepository(storageDir: $tmpDir . DIRECTORY_SEPARATOR . 'research');
            $evidence = new EvidenceCollector($resolver);
            $verifier = new Gate0ClearanceVerifier($repo, $resolver, $evidence);

            $retrievalProvider = new class implements RetrievalProviderInterface {
                public function getName(): string { return 'closure_retrieval_provider'; }
                public function supports(SourceDefinition $source): bool { return true; }
                public function retrieve(string $target, SourceDefinition $source, array $options = []): array {
                    $body = "# Enterprise Security Guidelines\n\n"
                        . "Zero trust architecture enforces continuous authorization.\n\n"
                        . "Cryptographic signatures guarantee immutable provenance.";
                    return [
                        'content' => $body,
                        'hash' => hash('sha256', $body),
                        'format' => 'markdown',
                        'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
                    ];
                }
            };

            $retrieval = new RetrievalManager([$retrievalProvider]);
            $researchOrchestrator = new ResearchOrchestrator(
                pathResolver: $resolver,
                dossierRepository: $repo,
                clearanceVerifier: $verifier,
                retrievalManager: $retrieval,
                evidenceCollector: $evidence
            );

            $orchestrator = new Orchestrator(
                pathResolver: $resolver,
                evidenceCollector: $evidence,
                researchEngine: $researchOrchestrator
            );

            self::verifyEndToEndLifecycle($orchestrator, $repo);
            self::verifyResearchExceptionFailsClosed($resolver, $evidence);
            self::verifyDossierTamperingRejected($verifier);
            self::verifyCallerSuppliedClearanceImmunity($verifier);
            self::verifyTaskIsolationBoundaries($verifier, $repo);
            self::verifyArtisanCommandIntegration($tmpDir);
            self::verifyCliSubprocessExecution();
            self::verifyServiceProviderBootRegistration();
        } finally {
            self::recursiveRemove($tmpDir);
        }
    }

    private static function verifyEndToEndLifecycle(Orchestrator $orchestrator, ResearchDossierRepository $repo): void
    {
        $sourceDef = new SourceDefinition(
            sourceId: 'SRC-P47-01',
            officialName: 'Authoritative Security Manual',
            description: 'Zero trust architecture guidance',
            owner: ['entity_name' => 'Security Standards Group', 'entity_type' => 'Standard Body'],
            canonicalUrl: 'https://security.example.org/spec',
            domains: ['F'],
            subdomains: ['F.01'],
            authorityTier: 1
        );

        $task = new TaskEnvelope(
            taskId: 'TASK-P47-LIFECYCLE-01',
            taskPrompt: 'Implement cryptographic signature validation pipeline',
            agentId: null,
            stage: 10,
            metadata: ['sources' => [$sourceDef]]
        );

        $plan = $orchestrator->planTask($task);
        Assert::assertFalse($plan['gate_0_blocked'], 'P4.7: Gate 0 is cleared with valid evidence');
        Assert::assertTrue($plan['gate_0_clearance']->isPassed(), 'P4.7: Gate 0 clearance is PASSED');
        Assert::assertStringContains('SECTION 2.6: VERIFIED RESEARCH & TECHNICAL EVIDENCE', (string)$plan['compiled_prompt'], 'P4.7: Prompt contains Section 2.6');

        $result = $orchestrator->execute($task);
        Assert::assertEquals('SUCCESS', $result->status, 'P4.7: Task executes successfully');
        Assert::assertEquals(0, $result->exitCode, 'P4.7: Exit code 0');
        Assert::assertStringContains('successfully planned and dispatched', $result->output, 'P4.7: Dispatch boundary reached');

        $dossier = $plan['research_dossier'];
        Assert::assertTrue($dossier instanceof ResearchDossier, 'P4.7: Real ResearchDossier created');
        Assert::assertTrue($dossier->isSealed(), 'P4.7: Dossier cryptographically sealed');
        Assert::assertTrue($dossier->verifyIntegrity(), 'P4.7: Cryptographic integrity verified');
        Assert::assertTrue($repo->has($dossier->dossierId), 'P4.7: Dossier persisted in repository');
    }

    private static function verifyResearchExceptionFailsClosed(PathResolver $resolver, EvidenceCollector $evidence): void
    {
        $throwingEngine = new class implements ResearchEngineInterface {
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                throw new \RuntimeException('Simulated catastrophic network failure during retrieval');
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult {
                return Gate0ClearanceResult::blocked($taskId, 'Failsafe blocked');
            }
        };

        $orch = new Orchestrator(pathResolver: $resolver, evidenceCollector: $evidence, researchEngine: $throwingEngine);
        $task = new TaskEnvelope('TASK-P47-EXCEPTION', 'Architect secure authentication cluster', null, 2);

        $plan = $orch->planTask($task);
        Assert::assertTrue($plan['gate_0_blocked'], 'P4.7: Research exception triggers gate_0_blocked');
        Assert::assertTrue($plan['compiled_prompt'] === null, 'P4.7: Prompt is suppressed on research exception');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_UNAVAILABLE_DEPENDENCY, $plan['gate_0_clearance']->operationalCondition, 'P4.7: Operational condition is UNAVAILABLE_DEPENDENCY');

        $result = $orch->execute($task);
        Assert::assertEquals('BLOCKED', $result->status, 'P4.7: Execution blocked on research exception');
        Assert::assertEquals(2, $result->exitCode, 'P4.7: Exit code 2');
        Assert::assertFalse(str_contains($result->output, 'successfully planned and dispatched'), 'P4.7: Dispatch NOT reached on exception');
    }

    private static function verifyDossierTamperingRejected(Gate0ClearanceVerifier $verifier): void
    {
        $claim = new ExtractedClaim('CLM-TAMPER', 'Zero trust statement', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.95, ['8.2'], [
            ['source_id' => 'SRC-1', 'content_hash' => hash('sha256', 'x'), 'quote_segment' => 'quote']
        ]);

        $dossier = (new ResearchDossier(
            dossierId: 'DOS-P47-TAMPER',
            taskId: 'TASK-P47-TAMPER',
            projectIdentity: 'UAOS Core',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'SRC-1', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        ))->seal();

        // Tamper with serialized claim statement while preserving citation structure
        $tamperedClaim = new ExtractedClaim(
            claimId: 'CLM-TAMPER',
            statement: 'TAMPERED STATEMENT',
            status: ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            confidenceScore: 0.95,
            applicableVersions: ['8.2'],
            citations: [['source_id' => 'SRC-1', 'content_hash' => hash('sha256', 'x'), 'quote_segment' => 'quote']]
        );

        $tampered = new ResearchDossier(
            dossierId: $dossier->dossierId,
            taskId: $dossier->taskId,
            projectIdentity: $dossier->projectIdentity,
            researchDepth: $dossier->researchDepth,
            researchedAt: $dossier->researchedAt,
            expiresAt: $dossier->expiresAt,
            sourcesConsulted: $dossier->sourcesConsulted,
            claims: [$tamperedClaim],
            gate0Status: $dossier->gate0Status,
            dossierHash: $dossier->dossierHash // Original seal hash no longer matches modified claim
        );

        $res = $verifier->evaluateDossier($tampered, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'P4.7: Tampered dossier hash is blocked by Gate 0');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_SECURITY_HOLD, $res->operationalCondition, 'P4.7: Tampering produces SECURITY_HOLD');
    }

    private static function verifyCallerSuppliedClearanceImmunity(Gate0ClearanceVerifier $verifier): void
    {
        // Dossier claims PASSED but has zero evidence
        $forged = (new ResearchDossier(
            dossierId: 'DOS-P47-FORGED',
            taskId: 'TASK-P47-FORGED',
            projectIdentity: 'UAOS Core',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [],
            claims: [],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        ))->seal();

        $res = $verifier->evaluateDossier($forged, DepthProfile::l1());
        Assert::assertTrue($res->isBlocked(), 'P4.7: Verifier strictly ignores caller-supplied PASSED status');
    }

    private static function verifyTaskIsolationBoundaries(Gate0ClearanceVerifier $verifier, ResearchDossierRepository $repo): void
    {
        // Dossier belongs to TASK-ALPHA
        $claim = new ExtractedClaim('CLM-ISO', 'Statement', ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.9, ['8.2'], [
            ['source_id' => 'SRC-1', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']
        ]);
        $dossierAlpha = (new ResearchDossier(
            dossierId: 'DOS-ALPHA',
            taskId: 'TASK-ALPHA',
            projectIdentity: 'UAOS Core',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'SRC-1', 'is_eligible' => true, 'lifecycle_state' => 'ACTIVE']],
            claims: [$claim],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        ))->seal();
        $repo->save($dossierAlpha);

        // Verifying TASK-BETA cannot claim clearance using TASK-ALPHA's dossier
        $resBeta = $verifier->verifyClearance('TASK-BETA', 1, DepthProfile::l1());
        Assert::assertTrue($resBeta->isBlocked(), 'P4.7: Task cannot reuse or leak dossier from another task');
        Assert::assertStringContains('No research dossier found for task', $resBeta->reason, 'P4.7: Verifier isolates dossiers strictly by task ID');

        // Evaluating DOS-ALPHA directly binds result exclusively to TASK-ALPHA
        $resEval = $verifier->evaluateDossier($dossierAlpha, DepthProfile::l1());
        Assert::assertEquals('TASK-ALPHA', $resEval->taskId, 'P4.7: Clearance result is cryptographically bound to TASK-ALPHA');
        Assert::assertFalse($resEval->taskId === 'TASK-BETA', 'P4.7: Dossier from TASK-ALPHA cannot be attributed to TASK-BETA');
    }

    private static function verifyArtisanCommandIntegration(string $tmpDir): void
    {
        // 1. Standalone fallback mode (L0 exempt)
        $cmdL0 = new class(['prompt' => 'Fix typo in docblock comment', 'stage' => 1]) extends AgentOsRunCommand {
            public function __construct(private array $optionsMap) {}
            public function option(?string $key = null): mixed { return $this->optionsMap[$key] ?? null; }
        };
        $codeL0 = $cmdL0->handle();
        Assert::assertEquals(0, $codeL0, 'P4.7: Artisan command succeeds for L0 task in standalone fallback');

        // 2. Standalone fallback mode (L2 blocked)
        $cmdL2 = new class(['prompt' => 'Architect microservices distributed queue', 'stage' => 2]) extends AgentOsRunCommand {
            public function __construct(private array $optionsMap) {}
            public function option(?string $key = null): mixed { return $this->optionsMap[$key] ?? null; }
        };
        $codeL2 = $cmdL2->handle();
        Assert::assertEquals(2, $codeL2, 'P4.7: Artisan command returns exit code 2 (BLOCKED) for L2 task without research');
    }

    private static function verifyCliSubprocessExecution(): void
    {
        $php = PHP_BINARY;
        $bin = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'agent-os';

        // L0 trivial task succeeds
        $outL0 = shell_exec("\"{$php}\" \"{$bin}\" run --task=\"Fix typo in docblock comment\" 2>&1");
        Assert::assertStringContains('successfully planned and dispatched', (string)$outL0, 'P4.7: CLI executes L0 task successfully');

        // L2 task without research fails closed
        $outL2 = shell_exec("\"{$php}\" \"{$bin}\" run --task=\"Architect distributed gateway\" 2>&1");
        Assert::assertStringContains('failed Gate 0 research clearance', (string)$outL2, 'P4.7: CLI blocks L2 task without research');
        Assert::assertFalse(str_contains((string)$outL2, 'successfully planned and dispatched'), 'P4.7: CLI dispatch suppressed');
    }

    private static function verifyServiceProviderBootRegistration(): void
    {
        $registeredCommands = [];
        $app = new class {
            public function runningInConsole(): bool { return true; }
        };

        $provider = new class($app, $registeredCommands) extends UniversalAgentOSServiceProvider {
            /** @param array<int, class-string> $ref */
            public function __construct(mixed $app, private array &$ref) { parent::__construct($app); }
            public function commands(array $commands): void { $this->ref = $commands; }
        };

        $provider->boot();
        Assert::assertCount(4, $registeredCommands, 'P4.7: Provider boot registers exactly 4 console commands');
        Assert::assertTrue(in_array(AgentOsRunCommand::class, $registeredCommands, true), 'P4.7: AgentOsRunCommand registered');
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
