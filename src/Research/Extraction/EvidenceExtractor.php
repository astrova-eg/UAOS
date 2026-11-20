<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Extraction;

use UniversalAgentOS\Research\Models\EvidenceProvenance;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\TrustPolicyConfiguration;

/**
 * Extracts verifiable claims, builds structured citations, and preserves evidence provenance.
 */
class EvidenceExtractor
{
    /**
     * Extracts structured claims from parsed document segments with source attribution.
     *
     * @param array<string, mixed> $parsedDocument Output from DocumentProcessorPipeline
     * @param SourceDefinition $source Authoritative source definition
     * @param array<string, mixed> $options Extraction configuration options
     * @return array<int, ExtractedClaim>
     */
    public function extractClaims(array $parsedDocument, SourceDefinition $source, array $options = []): array
    {
        $segments = (array)($parsedDocument['segments'] ?? []);
        $claims = [];
        $seq = 1;

        $sourceId = $source->sourceId;
        $docHash = (string)($parsedDocument['content_hash'] ?? hash('sha256', (string)($parsedDocument['text'] ?? '')));
        $defaultVersions = (array)($options['applicable_versions'] ?? []);

        $tier = $source->authorityTier;
        $initialStatus = match (true) {
            $tier <= 2 => ExtractedClaim::STATUS_VERIFIED_AUTHORITATIVE,
            $tier <= 3 => ExtractedClaim::STATUS_CORROBORATED_PRACTICE,
            default => ExtractedClaim::STATUS_UNVERIFIED_SPECULATION,
        };

        $baseConfidence = match ($tier) {
            1 => 0.95,
            2 => 0.88,
            3 => 0.78,
            4 => 0.65,
            default => 0.50,
        };

        $maxClaims = (int)($options['max_claims'] ?? 20);

        foreach ($segments as $seg) {
            if (count($claims) >= $maxClaims) {
                break;
            }

            $text = trim((string)($seg['text'] ?? ''));
            if ($text === '') {
                continue;
            }

            $quote = mb_substr($text, 0, 300);
            $segHash = (string)($seg['hash'] ?? hash('sha256', $text));

            $claimId = sprintf('CLM-%s-%03d', strtoupper(substr(hash('sha256', $sourceId . $segHash), 0, 8)), $seq++);

            $citations = [
                [
                    'source_id' => $sourceId,
                    'content_hash' => $segHash,
                    'quote_segment' => $quote,
                ],
            ];

            $claims[] = new ExtractedClaim(
                claimId: $claimId,
                statement: $text,
                status: $initialStatus,
                confidenceScore: $baseConfidence,
                applicableVersions: $defaultVersions,
                citations: $citations
            );
        }

        return $claims;
    }

    /**
     * Builds an authoritative provenance record for retrieved evidence.
     *
     * @param array<string, mixed> $parsedDocument
     * @param SourceDefinition $source
     * @param array<string, mixed> $options
     */
    public function createProvenance(array $parsedDocument, SourceDefinition $source, array $options = []): EvidenceProvenance
    {
        $mechanismType = (string)($options['mechanism_type'] ?? 'http_direct');
        $retrievedAt = (string)($parsedDocument['retrieved_at'] ?? gmdate('Y-m-d\TH:i:s\Z'));
        $evidenceRef = (string)($parsedDocument['url'] ?? $source->canonicalUrl);
        $versionContext = isset($options['version_context']) ? (string)$options['version_context'] : null;
        $trustScore = isset($options['trust_score']) ? (float)$options['trust_score'] : null;
        $freshnessScore = isset($options['freshness_score']) ? (float)$options['freshness_score'] : null;
        $warnings = (array)($options['warnings'] ?? []);

        return new EvidenceProvenance(
            evidenceReference: $evidenceRef,
            sourceId: $source->sourceId,
            mechanismType: $mechanismType,
            verifiedAt: $retrievedAt,
            verificationStatus: 'fully_tested',
            versionContext: $versionContext,
            trustScore: $trustScore,
            freshnessScore: $freshnessScore,
            policyVersion: TrustPolicyConfiguration::DEFAULT_POLICY_VERSION,
            warnings: $warnings
        );
    }

    /**
     * Formats source entry for inclusion in a ResearchDossier sources_consulted array.
     *
     * @param array<string, mixed> $parsedDocument
     * @param SourceDefinition $source
     * @return array{source_id: string, name: string, url: string, retrieved_at: string, content_hash: string, authority_level: int}
     */
    public function formatSourceConsulted(array $parsedDocument, SourceDefinition $source): array
    {
        return [
            'source_id' => $source->sourceId,
            'name' => $source->officialName,
            'url' => (string)($parsedDocument['url'] ?? $source->canonicalUrl),
            'retrieved_at' => (string)($parsedDocument['retrieved_at'] ?? gmdate('Y-m-d\TH:i:s\Z')),
            'content_hash' => (string)($parsedDocument['payload_hash'] ?? $parsedDocument['content_hash'] ?? hash('sha256', (string)($parsedDocument['text'] ?? ''))),
            'authority_level' => $source->authorityTier,
        ];
    }
}
