<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\Gate0ClearanceResult;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\ResearchRequest;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Tests\Assert;

class EvidenceCollectorTelemetryTest
{
    public static function run(): void
    {
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uaos_evidence_test_' . bin2hex(random_bytes(4));
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'reports', 0755, true);
        @mkdir($tmpDir . DIRECTORY_SEPARATOR . 'evidence', 0755, true);

        try {
            $resolver = new PathResolver(projectRootDir: $tmpDir);
            $collector = new EvidenceCollector($resolver);

            self::testSessionLifecycleAndCorrelation($collector);
            self::testSecretAndCredentialRedaction($collector);
            self::testGate0DecisionRecording($collector);
            self::testMarkdownAuditReportStructure($collector);
            self::testGate0TelemetryFailureSafety($resolver, $tmpDir);
        } finally {
            self::recursiveRemove($tmpDir);
        }
    }

    private static function testSessionLifecycleAndCorrelation(EvidenceCollector $collector): void
    {
        $sessId = $collector->startSession('TASK-EVID-01', 'DEV-001', 'dept-d', 'cli');
        Assert::assertTrue(!empty($sessId), 'startSession generates valid session identifier');
        Assert::assertEquals($sessId, $collector->getActiveSessionId(), 'Active session ID matches');

        $collector->recordEvent('test_op', '/path/to/file', 'PERMITTED', ['key' => 'val']);
        Assert::assertCount(1, $collector->getEvents(), 'Event recorded in active session');

        $reports = $collector->endSession('SUCCESS', 'Task completed without errors');
        Assert::assertTrue(file_exists($reports['json']), 'JSON execution report is generated');
        Assert::assertTrue(file_exists($reports['md']), 'Markdown execution report is generated');
        Assert::assertTrue($collector->getActiveSessionId() === null, 'Active session cleared after endSession');
    }

    private static function testSecretAndCredentialRedaction(EvidenceCollector $collector): void
    {
        $collector->startSession('TASK-SECRET-01', 'DEV-001', 'dept-d', 'cli');

        $sensitiveDetails = [
            'api_key' => 'secret_api_key_12345678',
            'client_secret' => 'super_secret_oauth_token',
            'password' => 'DatabasePassword999',
            'nested' => [
                'auth_token' => 'jwt_secret_token_abcdef',
                'normal_key' => 'normal_public_value',
            ],
            'header' => 'Authorization: Bearer my_super_secret_token_value_987654321',
            'url' => 'https://admin:my_secret_pass@api.vendor.com/v1/research',
        ];

        $collector->recordEvent('auth_call', 'https://api.vendor.com', 'PERMITTED', $sensitiveDetails);
        $events = $collector->getEvents();
        $details = $events[0]['details'];

        Assert::assertEquals('[REDACTED]', $details['api_key'], 'api_key was redacted');
        Assert::assertEquals('[REDACTED]', $details['client_secret'], 'client_secret was redacted');
        Assert::assertEquals('[REDACTED]', $details['password'], 'password was redacted');
        Assert::assertEquals('[REDACTED]', $details['nested']['auth_token'], 'Nested auth_token was redacted');
        Assert::assertEquals('normal_public_value', $details['nested']['normal_key'], 'Normal non-sensitive key was preserved');
        Assert::assertStringContains('[REDACTED]', $details['header'], 'Bearer token in header string was redacted');
        Assert::assertStringContains('[REDACTED]@api.vendor.com', $details['url'], 'Credentials in URL string were redacted');

        $collector->endSession('SUCCESS', 'Secret test finished');
    }

    private static function testGate0DecisionRecording(EvidenceCollector $collector): void
    {
        $collector->startSession('TASK-GATE0-EVID', 'DEV-001', 'dept-d', 'cli');

        $clearance = Gate0ClearanceResult::blocked(
            taskId: 'TASK-GATE0-EVID',
            reason: 'Mandatory depth L3 trade-off matrix missing',
            violations: ['Missing comparison matrix'],
            dossierId: 'DOS-TEST-99',
            dossierHash: hash('sha256', 'payload'),
            operationalCondition: Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED
        );

        $collector->recordGate0Decision($clearance);
        $events = $collector->getEvents();

        Assert::assertCount(1, $events, 'Gate 0 decision recorded as execution event');
        Assert::assertEquals('GATE_0_EVALUATED', $events[0]['operation'], 'Operation is GATE_0_EVALUATED');
        Assert::assertEquals('REJECTED', $events[0]['permission_decision'], 'Decision is REJECTED for blocked clearance');
        Assert::assertEquals('DOS-TEST-99', $events[0]['details']['dossier_id'], 'Dossier ID preserved in details');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED, $events[0]['details']['operational_condition'], 'Operational condition preserved');

        $collector->endSession('BLOCKED', 'Gate 0 blocked');
    }

    private static function testMarkdownAuditReportStructure(EvidenceCollector $collector): void
    {
        $collector->startSession('TASK-MD-AUDIT', 'AUD-001', 'dept-m', 'cli');

        $clearance = Gate0ClearanceResult::passed('TASK-MD-AUDIT', 'DOS-MD-01', hash('sha256', 'hash'));
        $collector->recordGate0Decision($clearance);

        $reports = $collector->endSession('SUCCESS', 'Audit pass completed');
        $md = (string)file_get_contents($reports['md']);

        Assert::assertStringContains('## Research & Gate 0 Governance', $md, 'Markdown report includes Research & Gate 0 Governance section');
        Assert::assertStringContains('GATE_0_EVALUATED', $md, 'Markdown report displays GATE_0_EVALUATED event');
        Assert::assertStringContains('PERMITTED', $md, 'Markdown report displays PERMITTED decision');
    }

    private static function testGate0TelemetryFailureSafety(PathResolver $resolver, string $tmpDir): void
    {
        // 1. Simulate recordGate0Decision() throwing an exception for a BLOCKED decision
        $failingCollectorBlocked = new class implements EvidenceCollectorInterface {
            public function startSession(string $taskId, string $agentId, string $departmentId, string $adapterId): string { return 'sess_fail_1'; }
            public function recordEvent(string $operation, string $targetResource, string $permissionDecision, array $details = []): void {}
            public function recordGate0Decision(Gate0ClearanceResult $clearance): void {
                throw new \RuntimeException('Simulated telemetry storage failure during BLOCKED record');
            }
            public function endSession(string $status, string $summary): array { return ['json' => '', 'md' => '']; }
            public function getActiveSessionId(): ?string { return 'sess_fail_1'; }
        };

        $repo = new ResearchDossierRepository(storageDir: $tmpDir . DIRECTORY_SEPARATOR . 'research_telemetry_test');
        $verifierBlocked = new Gate0ClearanceVerifier($repo, $resolver, $failingCollectorBlocked);

        $clearanceBlocked = $verifierBlocked->verifyClearance('TASK-FAIL-BLOCKED', 1, DepthProfile::l1());
        Assert::assertTrue($clearanceBlocked->isBlocked(), 'Clearance remains BLOCKED when telemetry throws exception');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_QUORUM_UNSATISFIED, $clearanceBlocked->operationalCondition, 'Original condition preserved');
        Assert::assertStringContains('Simulated telemetry storage failure', json_encode($clearanceBlocked->violations), 'Telemetry exception recorded in violations');

        // Test boundary at Orchestrator::execute() with BLOCKED clearance under telemetry failure
        $engineBlocked = new class($clearanceBlocked) implements ResearchEngineInterface {
            public function __construct(private Gate0ClearanceResult $clearance) {}
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                return (new ResearchDossier(
                    dossierId: 'DOS-FAIL-BLOCKED',
                    taskId: $request->taskId,
                    projectIdentity: 'Test',
                    researchDepth: DepthProfile::LEVEL_L1,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    gate0Status: ResearchDossier::GATE_STATUS_BLOCKED
                ))->seal();
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult { return $this->clearance; }
        };

        $orchBlocked = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $failingCollectorBlocked,
            researchEngine: $engineBlocked
        );
        $taskBlocked = new TaskEnvelope('TASK-FAIL-BLOCKED', 'Architect cache invalidation strategy', null, 2);
        $execBlocked = $orchBlocked->execute($taskBlocked);

        Assert::assertEquals('BLOCKED', $execBlocked->status, 'Orchestrator::execute fails closed (BLOCKED) when telemetry fails on BLOCKED');
        Assert::assertEquals(2, $execBlocked->exitCode, 'Exit code is 2 for blocked execution');
        Assert::assertFalse($execBlocked->isSuccess(), 'Task execution is not successful');
        Assert::assertStringContains('failed Gate 0 research clearance', $execBlocked->output, 'Output confirms Gate 0 blocked');
        Assert::assertFalse(str_contains($execBlocked->output, 'successfully planned and dispatched'), 'Dispatch boundary NEVER reached');

        // 2. Simulate recordGate0Decision() throwing an exception for a PASSED decision
        $failingCollectorPassed = new class implements EvidenceCollectorInterface {
            public function startSession(string $taskId, string $agentId, string $departmentId, string $adapterId): string { return 'sess_fail_2'; }
            public function recordEvent(string $operation, string $targetResource, string $permissionDecision, array $details = []): void {}
            public function recordGate0Decision(Gate0ClearanceResult $clearance): void {
                throw new \RuntimeException('Simulated audit telemetry failure during PASSED record');
            }
            public function endSession(string $status, string $summary): array { return ['json' => '', 'md' => '']; }
            public function getActiveSessionId(): ?string { return 'sess_fail_2'; }
        };

        // Create a valid dossier in repo
        $validDossier = (new ResearchDossier(
            dossierId: 'DOS-PASS-EVID',
            taskId: 'TASK-FAIL-PASSED',
            projectIdentity: 'Test',
            researchDepth: DepthProfile::LEVEL_L1,
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [['source_id' => 'SRC-1', 'is_eligible' => true]],
            claims: [new \UniversalAgentOS\Research\Models\ExtractedClaim('C1', 'Fact statement', \UniversalAgentOS\Research\Models\ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, 0.99, ['8.2'], [['source_id' => 'SRC-1', 'content_hash' => hash('sha256', 'a'), 'quote_segment' => 'quote']])],
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        ))->seal();
        $repo->save($validDossier);

        $verifierPassed = new Gate0ClearanceVerifier($repo, $resolver, $failingCollectorPassed);
        $clearancePassed = $verifierPassed->verifyClearance('TASK-FAIL-PASSED', 1, DepthProfile::l1(), $validDossier);

        // Security requirement: Must fail closed to SECURITY_HOLD when mandatory audit logging fails
        Assert::assertTrue($clearancePassed->isBlocked(), 'Passed clearance fails closed to BLOCKED when audit telemetry throws');
        Assert::assertEquals(Gate0ClearanceResult::CONDITION_SECURITY_HOLD, $clearancePassed->operationalCondition, 'Condition is SECURITY_HOLD');
        Assert::assertEquals('PASSED', $clearancePassed->auditDetails['original_decision'], 'Original passed decision recorded in audit details');

        // Test boundary at Orchestrator::execute() with PASSED decision under telemetry failure
        $enginePassed = new class($clearancePassed) implements ResearchEngineInterface {
            public function __construct(private Gate0ClearanceResult $clearance) {}
            public function conductResearch(ResearchRequest $request): ResearchDossier {
                return (new ResearchDossier(
                    dossierId: 'DOS-PASS-EVID',
                    taskId: $request->taskId,
                    projectIdentity: 'Test',
                    researchDepth: DepthProfile::LEVEL_L1,
                    researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
                    expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
                    gate0Status: ResearchDossier::GATE_STATUS_PASSED
                ))->seal();
            }
            public function getValidDossier(string $researchId): ?ResearchDossier { return null; }
            public function verifyClearance(string $taskId): Gate0ClearanceResult { return $this->clearance; }
        };

        $orchPassed = new Orchestrator(
            pathResolver: $resolver,
            evidenceCollector: $failingCollectorPassed,
            researchEngine: $enginePassed
        );
        $taskPassed = new TaskEnvelope('TASK-FAIL-PASSED', 'Architect cache invalidation strategy', null, 2);
        $execPassed = $orchPassed->execute($taskPassed);

        Assert::assertEquals('BLOCKED', $execPassed->status, 'Orchestrator blocks execution when telemetry fails for PASSED task');
        Assert::assertEquals(2, $execPassed->exitCode, 'Exit code is 2 for blocked execution');
        Assert::assertFalse($execPassed->isSuccess(), 'Task execution without auditability is strictly not permitted');
        Assert::assertFalse(str_contains($execPassed->output, 'successfully planned and dispatched'), 'Dispatch boundary NEVER reached');
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
