<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Assert.php';

use UniversalAgentOS\Tests\Assert;

echo "=======================================================\n";
echo " UNIVERSAL AGENT OS (UAOS) AUTOMATED TEST SUITE RUNNER\n";
echo "=======================================================\n\n";

Assert::reset();

$testSuites = [
    // Unit Tests
    'Unit: PathResolverTest' => \UniversalAgentOS\Tests\Unit\PathResolverTest::class,
    'Unit: PathValidatorTest' => \UniversalAgentOS\Tests\Unit\PathValidatorTest::class,
    'Unit: PersistentNonceStoreTest' => \UniversalAgentOS\Tests\Unit\PersistentNonceStoreTest::class,
    'Unit: Gate4VerifierTest' => \UniversalAgentOS\Tests\Unit\Gate4VerifierTest::class,
    'Unit: ToolGatewayTest' => \UniversalAgentOS\Tests\Unit\ToolGatewayTest::class,
    'Unit: ProjectDiscoveryTest' => \UniversalAgentOS\Tests\Unit\ProjectDiscoveryTest::class,
    'Unit: PromptBuilderTest' => \UniversalAgentOS\Tests\Unit\PromptBuilderTest::class,
    'Unit: SkillEngineTest' => \UniversalAgentOS\Tests\Unit\SkillEngineTest::class,
    'Unit: OrchestratorTest' => \UniversalAgentOS\Tests\Unit\OrchestratorTest::class,
    'Unit: ResearchContractsAndModelsTest' => \UniversalAgentOS\Tests\Unit\ResearchContractsAndModelsTest::class,
    'Unit: ResearchContractsRemediationTest' => \UniversalAgentOS\Tests\Unit\ResearchContractsRemediationTest::class,
    'Unit: ResearchDossierRepositoryTest' => \UniversalAgentOS\Tests\Unit\ResearchDossierRepositoryTest::class,
    'Unit: ResearchDossierConcurrencyTest' => \UniversalAgentOS\Tests\Unit\ResearchDossierConcurrencyTest::class,
    'Unit: ResearchDossierWindowsPersistenceTest' => \UniversalAgentOS\Tests\Unit\ResearchDossierWindowsPersistenceTest::class,
    'Unit: SQLiteKnowledgeStoreTest' => \UniversalAgentOS\Tests\Unit\SQLiteKnowledgeStoreTest::class,
    'Unit: SourceDefinitionValidationTest' => \UniversalAgentOS\Tests\Unit\SourceDefinitionValidationTest::class,
    'Unit: TrustIntelligenceEngineTest' => \UniversalAgentOS\Tests\Unit\TrustIntelligenceEngineTest::class,
    'Unit: FreshnessIntelligenceTest' => \UniversalAgentOS\Tests\Unit\FreshnessIntelligenceTest::class,
    'Unit: VersionIntelligenceTest' => \UniversalAgentOS\Tests\Unit\VersionIntelligenceTest::class,
    'Unit: SourceEligibilityGovernanceTest' => \UniversalAgentOS\Tests\Unit\SourceEligibilityGovernanceTest::class,
    'Unit: SourceRepositoryPersistenceTest' => \UniversalAgentOS\Tests\Unit\SourceRepositoryPersistenceTest::class,
    'Unit: SourceRegistryLifecycleTest' => \UniversalAgentOS\Tests\Unit\SourceRegistryLifecycleTest::class,
    'Unit: DocumentParserAndSanitizerTest' => \UniversalAgentOS\Tests\Unit\DocumentParserAndSanitizerTest::class,
    'Unit: RetrievalProviderAndOrchestrationTest' => \UniversalAgentOS\Tests\Unit\RetrievalProviderAndOrchestrationTest::class,
    'Unit: EvidenceExtractionAndProvenanceTest' => \UniversalAgentOS\Tests\Unit\EvidenceExtractionAndProvenanceTest::class,
    'Unit: ResearchDepthIntelligenceTest' => \UniversalAgentOS\Tests\Unit\ResearchDepthIntelligenceTest::class,
    'Unit: DomainIntelligenceTest' => \UniversalAgentOS\Tests\Unit\DomainIntelligenceTest::class,
    'Unit: TechnologyComparisonTest' => \UniversalAgentOS\Tests\Unit\TechnologyComparisonTest::class,
    'Unit: ConflictIntelligenceTest' => \UniversalAgentOS\Tests\Unit\ConflictIntelligenceTest::class,
    'Unit: Gate0ClearanceVerifierTest' => \UniversalAgentOS\Tests\Unit\Gate0ClearanceVerifierTest::class,
    'Unit: ResearchOrchestratorTest' => \UniversalAgentOS\Tests\Unit\ResearchOrchestratorTest::class,
    'Unit: PromptBuilderSection26Test' => \UniversalAgentOS\Tests\Unit\PromptBuilderSection26Test::class,
    'Unit: UaosOrchestratorRdeiTest' => \UniversalAgentOS\Tests\Unit\UaosOrchestratorRdeiTest::class,
    'Unit: EvidenceCollectorTelemetryTest' => \UniversalAgentOS\Tests\Unit\EvidenceCollectorTelemetryTest::class,
    'Unit: LaravelServiceProviderIntegrationTest' => \UniversalAgentOS\Tests\Unit\LaravelServiceProviderIntegrationTest::class,

    // Security & Adversarial Tests
    'Security: PathValidatorAdversarialTest' => \UniversalAgentOS\Tests\Security\PathValidatorAdversarialTest::class,
    'Security: StreamableHttpTransportSsrfTest' => \UniversalAgentOS\Tests\Security\StreamableHttpTransportSsrfTest::class,
    'Security: RetrievalSsrfAndSecurityTest' => \UniversalAgentOS\Tests\Security\RetrievalSsrfAndSecurityTest::class,
    'Security: RemoteMcpTransportSecurityTest' => \UniversalAgentOS\Tests\Security\RemoteMcpTransportSecurityTest::class,
    'Security: DangerousCommandExecutionTest' => \UniversalAgentOS\Tests\Security\DangerousCommandExecutionTest::class,
    'Security: GitCommandAuthorizationTest' => \UniversalAgentOS\Tests\Security\GitCommandAuthorizationTest::class,
    'Security: ConcurrencyNonceTest' => \UniversalAgentOS\Tests\Security\ConcurrencyNonceTest::class,
    'Security: Gate4ReplayTest' => \UniversalAgentOS\Tests\Security\Gate4ReplayTest::class,
    'Security: GovernanceBypassTest' => \UniversalAgentOS\Tests\Security\GovernanceBypassTest::class,
    'Security: PromptInjectionResistanceTest' => \UniversalAgentOS\Tests\Security\PromptInjectionResistanceTest::class,

    // Platform Adapters Contract Tests
    'Adapters: AdapterContractTest' => \UniversalAgentOS\Tests\Adapters\AdapterContractTest::class,

    // MCP Acceptance Tests (24 criteria)
    'MCP: McpAcceptanceTest' => \UniversalAgentOS\Tests\Mcp\McpAcceptanceTest::class,

    // Feature & Distribution Tests
    'Feature: AssetPreservationTest' => \UniversalAgentOS\Tests\Feature\AssetPreservationTest::class,
    'Feature: ComposerInstallationTest' => \UniversalAgentOS\Tests\Feature\ComposerInstallationTest::class,
    'Feature: Gate0RuntimeIntegrationTest' => \UniversalAgentOS\Tests\Feature\Gate0RuntimeIntegrationTest::class,
    'Feature: RdeiPhase47ClosureVerificationTest' => \UniversalAgentOS\Tests\Feature\RdeiPhase47ClosureVerificationTest::class,
];

foreach ($testSuites as $name => $class) {
    echo "Running {$name}...\n";
    try {
        $class::run();
    } catch (\Throwable $e) {
        Assert::assertTrue(false, "Unhandled exception in {$name}: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    }
}

$stats = Assert::getStats();

echo "\n=======================================================\n";
echo "TEST SUITE SUMMARY:\n";
echo "  Total Assertions: " . ($stats['passed'] + $stats['failed']) . "\n";
echo "  Passed:           " . $stats['passed'] . "\n";
echo "  Failed:           " . $stats['failed'] . "\n";
echo "=======================================================\n";

if ($stats['failed'] > 0) {
    echo "\nFAILURE DETAILS:\n";
    foreach ($stats['failures'] as $fail) {
        echo " - {$fail}\n";
    }
    echo "\nTEST SUITE FAILED.\n";
    exit(1);
}

echo "ALL AUTOMATED TESTS PASSED SUCCESSFULLY (100%)\n";
exit(0);
