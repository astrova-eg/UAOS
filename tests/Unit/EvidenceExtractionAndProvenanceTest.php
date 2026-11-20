<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Research\Extraction\EvidenceExtractor;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\ResearchDossier;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Parsing\DocumentProcessorPipeline;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class EvidenceExtractionAndProvenanceTest
{
    public static function run(): void
    {
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $source = SourceDefinition::fromArray($fixture);

        $pipeline = new DocumentProcessorPipeline();
        $rawDoc = [
            'content' => "# Cryptographic Architecture\n\nAll signatures must be evaluated using constant-time comparisons.\n\nNonce replay stores must reject duplicated tokens.",
            'mime_type' => 'text/markdown',
            'url' => 'https://example.com/security.md',
            'source_id' => $source->sourceId,
            'hash' => hash('sha256', "# Cryptographic Architecture\n\nAll signatures must be evaluated using constant-time comparisons.\n\nNonce replay stores must reject duplicated tokens."),
        ];
        $parsed = $pipeline->process($rawDoc);

        $extractor = new EvidenceExtractor();

        // 1. Claim Extraction
        $claims = $extractor->extractClaims($parsed, $source, ['applicable_versions' => ['8.2', '8.3']]);
        Assert::assertTrue(count($claims) >= 1, "Extracted claims from segments");

        $firstClaim = $claims[0];
        Assert::assertTrue($firstClaim instanceof ExtractedClaim, "Claim is instance of ExtractedClaim");
        Assert::assertTrue(str_starts_with($firstClaim->claimId, 'CLM-'), "Claim ID has canonical prefix");
        Assert::assertEquals(ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE, $firstClaim->status, "Tier 1 source yields VERIFIED_AUTHORITATIVE");
        Assert::assertTrue($firstClaim->isHighConfidence(), "Tier 1 source has high confidence score (>= 0.85)");
        Assert::assertEquals(['8.2', '8.3'], $firstClaim->applicableVersions, "Preserved applicable versions");

        // Citations structure
        Assert::assertEquals(1, count($firstClaim->citations), "Claim has 1 citation");
        Assert::assertEquals($source->sourceId, $firstClaim->citations[0]['source_id'], "Citation references sourceId");
        Assert::assertTrue(strlen($firstClaim->citations[0]['content_hash']) === 64, "Citation references 64-char hash");

        // 2. Evidence Provenance Record
        $provenance = $extractor->createProvenance($parsed, $source, [
            'trust_score' => 0.95,
            'freshness_score' => 0.90,
            'version_context' => '8.2-8.3',
        ]);
        Assert::assertEquals($source->sourceId, $provenance->sourceId, "Provenance matches sourceId");
        Assert::assertEquals('fully_tested', $provenance->verificationStatus, "Verification status is fully_tested");
        Assert::assertEquals(0.95, $provenance->trustScore, "Provenance trust score preserved");
        $provHash = $provenance->computeProvenanceHash();
        Assert::assertTrue(strlen($provHash) === 64, "Deterministic provenance hash generated");

        // 3. Format Source Consulted
        $consulted = $extractor->formatSourceConsulted($parsed, $source);
        Assert::assertEquals($source->sourceId, $consulted['source_id'], "Source consulted source_id matches");
        Assert::assertEquals($source->officialName, $consulted['name'], "Source consulted name matches");
        Assert::assertEquals($source->authorityTier, $consulted['authority_level'], "Authority level matches");
        Assert::assertTrue(strlen($consulted['content_hash']) === 64, "Content hash present in consulted");

        // 4. Dossier Integration
        $dossier = new ResearchDossier(
            dossierId: 'DOS-20261117-abcdef',
            taskId: 'TASK-20261117-01',
            projectIdentity: 'UAOS-Core',
            researchDepth: 'L2',
            researchedAt: gmdate('Y-m-d\TH:i:s\Z'),
            expiresAt: gmdate('Y-m-d\TH:i:s\Z', time() + 86400),
            sourcesConsulted: [$consulted],
            claims: $claims,
            gate0Status: ResearchDossier::GATE_STATUS_PASSED
        );

        $dossierHash = $dossier->computeHash();
        Assert::assertTrue(strlen($dossierHash) === 64, "Dossier computes valid SHA-256 hash incorporating extracted claims");
        $sealed = $dossier->seal();
        Assert::assertTrue($sealed->isSealed(), "Dossier is sealed");
        Assert::assertTrue($sealed->verifyIntegrity(), "Dossier integrity verifies successfully");
    }
}
