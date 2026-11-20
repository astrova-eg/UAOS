<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use DateTimeImmutable;
use DateTimeZone;
use UniversalAgentOS\Research\Models\ConflictRecord;
use UniversalAgentOS\Research\Models\ExtractedClaim;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Deterministic contradiction detection engine identifying genuine and contextual disputes across research claims.
 */
class ConflictDetector
{
    private const CRITICAL_KEYWORDS = [
        'security', 'vulnerability', 'cve', 'crypto', 'encryption',
        'auth', 'authorization', 'token', 'tenant_id', 'isolation',
        'sandbox', 'secret', 'password', 'permission', 'firewall',
    ];

    private const OPPOSITION_PAIRS = [
        ['deprecated', 'supported'],
        ['removed', 'available'],
        ['unsafe', 'safe'],
        ['insecure', 'secure'],
        ['vulnerable', 'not affected'],
        ['true', 'false'],
        ['enabled', 'disabled'],
        ['required', 'optional'],
        ['prohibited', 'allowed'],
        ['incompatible', 'compatible'],
        ['thread-safe', 'not thread-safe'],
    ];

    /**
     * Scans a collection of extracted claims for contradictions.
     *
     * @param array<int, ExtractedClaim> $claims Claims to evaluate
     * @param array<string, SourceDefinition> $sourcesById Optional mapping of source_id to SourceDefinition
     * @param array<string, mixed> $claimContexts Context metadata keyed by claimId (e.g. environment, date, topic)
     * @return array<int, ConflictRecord> Detected conflicts
     */
    public function detectConflicts(
        array $claims,
        array $sourcesById = [],
        array $claimContexts = []
    ): array {
        $conflicts = [];
        $count = count($claims);
        $now = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y-m-d\TH:i:s\Z');
        $conflictIndex = 1;

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $claimA = $claims[$i];
                $claimB = $claims[$j];

                $conflict = $this->evaluatePair(
                    $claimA,
                    $claimB,
                    $conflictIndex,
                    $now,
                    $sourcesById,
                    $claimContexts
                );

                if ($conflict !== null) {
                    $conflicts[] = $conflict;
                    $conflictIndex++;
                }
            }
        }

        return $conflicts;
    }

    private function evaluatePair(
        ExtractedClaim $claimA,
        ExtractedClaim $claimB,
        int $index,
        string $timestamp,
        array $sourcesById,
        array $claimContexts
    ): ?ConflictRecord {
        $contextA = $claimContexts[$claimA->claimId] ?? [];
        $contextB = $claimContexts[$claimB->claimId] ?? [];

        // Determine shared subject or target
        $subject = $this->extractSharedSubject($claimA, $claimB, $contextA, $contextB);
        if ($subject === null) {
            return null; // Different topics, no conflict
        }

        // Check for semantic opposition
        $isOpposing = $this->hasSemanticOpposition($claimA->statement, $claimB->statement);
        if (!$isOpposing) {
            return null;
        }

        $sourceA = $this->resolveSourceForClaim($claimA, $sourcesById);
        $sourceB = $this->resolveSourceForClaim($claimB, $sourcesById);

        // Classify contradiction type
        $type = $this->classifyContradictionType($claimA, $claimB, $contextA, $contextB);
        $isCritical = $this->isCriticalSubject($subject, $claimA->statement, $claimB->statement);

        $description = sprintf(
            "Dispute detected on '%s' between claim %s ('%s') and claim %s ('%s'). Classified as %s.",
            $subject,
            $claimA->claimId,
            substr($claimA->statement, 0, 60),
            $claimB->claimId,
            substr($claimB->statement, 0, 60),
            $type
        );

        $conflictId = sprintf('CNF-%s-%03d', date('Ymd'), $index);

        return new ConflictRecord(
            conflictId: $conflictId,
            claimA: $claimA,
            claimB: $claimB,
            contradictionType: $type,
            targetSubject: $subject,
            isCritical: $isCritical,
            description: $description,
            detectedAt: $timestamp,
            sourceA: $sourceA,
            sourceB: $sourceB
        );
    }

    private function classifyContradictionType(
        ExtractedClaim $claimA,
        ExtractedClaim $claimB,
        array $contextA,
        array $contextB
    ): string {
        // 1. Version Divergence
        if (!empty($claimA->applicableVersions) && !empty($claimB->applicableVersions)) {
            $intersection = array_intersect($claimA->applicableVersions, $claimB->applicableVersions);
            if (empty($intersection)) {
                return ConflictRecord::TYPE_VERSION_DIVERGENCE;
            }
        }

        // 2. Context Discrepancy (e.g. OS, deployment environment)
        $envA = $contextA['environment'] ?? $contextA['os'] ?? null;
        $envB = $contextB['environment'] ?? $contextB['os'] ?? null;
        if ($envA !== null && $envB !== null && strtolower((string)$envA) !== strtolower((string)$envB)) {
            return ConflictRecord::TYPE_CONTEXT_DISCREPANCY;
        }

        // 3. Temporal Invalidation (e.g. publication year delta >= 2 years)
        $dateA = $contextA['published_at'] ?? $contextA['date'] ?? null;
        $dateB = $contextB['published_at'] ?? $contextB['date'] ?? null;
        if ($dateA !== null && $dateB !== null) {
            $yearA = (int)substr((string)$dateA, 0, 4);
            $yearB = (int)substr((string)$dateB, 0, 4);
            if (abs($yearA - $yearB) >= 2) {
                return ConflictRecord::TYPE_TEMPORAL_INVALIDATION;
            }
        }

        // 4. True Technical Contradiction
        return ConflictRecord::TYPE_TRUE_TECHNICAL_CONTRADICTION;
    }

    private function hasSemanticOpposition(string $statementA, string $statementB): bool
    {
        $sa = strtolower($statementA);
        $sb = strtolower($statementB);

        foreach (self::OPPOSITION_PAIRS as [$word1, $word2]) {
            if ((str_contains($sa, $word1) && str_contains($sb, $word2)) ||
                (str_contains($sa, $word2) && str_contains($sb, $word1))) {
                return true;
            }
        }

        return false;
    }

    private function extractSharedSubject(
        ExtractedClaim $claimA,
        ExtractedClaim $claimB,
        array $contextA,
        array $contextB
    ): ?string {
        if (!empty($contextA['subject']) && !empty($contextB['subject'])) {
            if (strtolower((string)$contextA['subject']) === strtolower((string)$contextB['subject'])) {
                return (string)$contextA['subject'];
            }
        }

        // Extract key nouns / symbols common to both
        $wordsA = preg_split('/[\s,\.\(\)]+/', strtolower($claimA->statement), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $wordsB = preg_split('/[\s,\.\(\)]+/', strtolower($claimB->statement), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $stopWords = ['the', 'is', 'in', 'and', 'or', 'for', 'to', 'a', 'an', 'with', 'by', 'of', 'on', 'not'];
        $filteredA = array_diff($wordsA, $stopWords);
        $filteredB = array_diff($wordsB, $stopWords);

        $common = array_intersect($filteredA, $filteredB);
        if (count($common) >= 1) {
            return implode(' ', array_slice(array_values($common), 0, 3));
        }

        return null;
    }

    private function isCriticalSubject(string $subject, string $statementA, string $statementB): bool
    {
        $haystack = strtolower("{$subject} {$statementA} {$statementB}");
        foreach (self::CRITICAL_KEYWORDS as $kw) {
            if (str_contains($haystack, $kw)) {
                return true;
            }
        }
        return false;
    }

    private function resolveSourceForClaim(ExtractedClaim $claim, array $sourcesById): ?SourceDefinition
    {
        if (empty($claim->citations)) {
            return null;
        }
        $firstSourceId = $claim->citations[0]['source_id'] ?? null;
        if ($firstSourceId !== null && isset($sourcesById[$firstSourceId])) {
            return $sourcesById[$firstSourceId];
        }
        return null;
    }
}
