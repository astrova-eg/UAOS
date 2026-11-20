<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Engine;

use InvalidArgumentException;
use UniversalAgentOS\Research\Models\DepthProfile;
use UniversalAgentOS\Research\Models\ResearchRequest;

/**
 * Deterministic, explainable research depth selector for RDEI (L0 to L4).
 *
 * Evaluates workflow stage, domain risk indicators, dependency blast radius,
 * and target taxonomy domains to compute resource-proportional research depth.
 */
class ResearchDepthSelector
{
    public const CRITICAL_KEYWORDS = [
        'auth', 'authentication', 'crypto', 'cryptography', 'encryption',
        'token', 'tenant_id', 'sandbox', 'gateway', 'cve', 'firewall',
        'zero-trust', 'secret', 'permission', 'vulnerability', 'rbac',
    ];

    public const HIGH_RISK_KEYWORDS = [
        'architecture', 'database schema', 'queue worker', 'dependency',
        'third-party', 'orm', 'redis', 'migration', 'cache', 'concurrency',
        'distributed', 'event-driven', 'microservice', 'transaction',
    ];

    public const MEDIUM_RISK_KEYWORDS = [
        'service', 'controller', 'livewire', 'component', 'validation rule',
        'repository', 'endpoint', 'api', 'handler', 'middleware', 'view',
    ];

    public const TRIVIAL_KEYWORDS = [
        'typo', 'formatting', 'whitespace', 'indentation', 'rename variable',
        'comment', 'readme', 'docblock', 'local docs', 'fix typo',
    ];

    public const ESCALATION_DEPRECATED_API = 'DEPRECATED_API';
    public const ESCALATION_ACTIVE_CVE = 'ACTIVE_CVE';
    public const ESCALATION_CONTRADICTION = 'CONTRADICTION_DETECTED';
    public const ESCALATION_UNVETTED_PACKAGE = 'UNVETTED_PACKAGE';

    /**
     * Computes the calibrated DepthProfile for a task request.
     *
     * @param string $taskPrompt Natural language task description
     * @param int $stage Workflow stage (1-14)
     * @param array<int, string> $targetDomains Target taxonomy domain letters
     * @param array<string, mixed> $activeProfile Active target project profile
     * @param array<string, mixed> $metadata Arbitrary contextual execution metadata
     */
    public function selectDepth(
        string $taskPrompt,
        int $stage = 1,
        array $targetDomains = [],
        array $activeProfile = [],
        array $metadata = []
    ): DepthProfile {
        $trimmedPrompt = trim($taskPrompt);
        if ($trimmedPrompt === '') {
            throw new InvalidArgumentException('taskPrompt cannot be empty for depth selection.');
        }

        if ($stage < 1 || $stage > 14) {
            throw new InvalidArgumentException("Workflow stage must be between 1 and 14, got: {$stage}");
        }

        $lowerPrompt = strtolower($trimmedPrompt);
        $reasons = [];

        // Check trivial / formatting first (L0 candidate)
        $isTrivial = $this->matchesAny($lowerPrompt, self::TRIVIAL_KEYWORDS);
        $hasDependencyBlast = $this->hasDependencyBlastRadius($lowerPrompt, $metadata);

        // Domain-specific baseline depth
        $domainDepth = $this->evaluateDomainDepth($targetDomains, $reasons);

        // Risk keyword evaluation
        $keywordDepth = DepthProfile::LEVEL_L0;
        if ($this->matchesAny($lowerPrompt, self::CRITICAL_KEYWORDS)) {
            $keywordDepth = DepthProfile::LEVEL_L4;
            $reasons[] = 'Critical risk keywords detected in task prompt';
        } elseif ($this->matchesAny($lowerPrompt, self::HIGH_RISK_KEYWORDS)) {
            $keywordDepth = DepthProfile::LEVEL_L3;
            $reasons[] = 'High risk keywords detected in task prompt';
        } elseif ($this->matchesAny($lowerPrompt, self::MEDIUM_RISK_KEYWORDS)) {
            $keywordDepth = DepthProfile::LEVEL_L2;
            $reasons[] = 'Medium risk keywords detected in task prompt';
        }

        // Stage-based baseline
        $stageDepth = $this->evaluateStageDepth($stage, $reasons);

        // Determine combined raw level
        $selectedLevel = DepthProfile::LEVEL_L1; // standard default
        if ($isTrivial && !$hasDependencyBlast && $keywordDepth === DepthProfile::LEVEL_L0 && $domainDepth === DepthProfile::LEVEL_L0) {
            $selectedLevel = DepthProfile::LEVEL_L0;
            $reasons[] = 'Trivial local modification with zero external dependencies';
        } else {
            $selectedLevel = $this->highestLevel([$stageDepth, $keywordDepth, $domainDepth]);
        }

        // Elevate if dependency blast radius detected
        if ($hasDependencyBlast) {
            $blastFloor = ($selectedLevel === DepthProfile::LEVEL_L4) ? DepthProfile::LEVEL_L4 : DepthProfile::LEVEL_L3;
            $selectedLevel = $this->highestLevel([$selectedLevel, $blastFloor]);
            $reasons[] = 'Dependency blast radius detected (composer/core modification)';
        }

        $rationale = sprintf(
            'Assigned %s. Factors: %s.',
            $selectedLevel,
            empty($reasons) ? 'Default baseline' : implode('; ', array_unique($reasons))
        );

        return DepthProfile::forLevel($selectedLevel, $rationale);
    }

    /**
     * Computes depth directly from a ResearchRequest value object.
     */
    public function selectForRequest(ResearchRequest $request): DepthProfile
    {
        return $this->selectDepth(
            taskPrompt: $request->taskPrompt,
            stage: $request->stage,
            targetDomains: $request->targetDomains,
            activeProfile: $request->activeProfile,
            metadata: $request->metadata
        );
    }

    /**
     * Escalates an existing depth profile upon discovering higher risk at runtime.
     */
    public function escalate(DepthProfile $current, string $escalationReason, ?string $detail = null): DepthProfile
    {
        $detailStr = $detail !== null ? " ({$detail})" : '';
        return match ($escalationReason) {
            self::ESCALATION_ACTIVE_CVE => DepthProfile::l4(
                "Escalated from {$current->level} to L4: Active CVE advisory detected{$detailStr}."
            ),
            self::ESCALATION_DEPRECATED_API => DepthProfile::forLevel(
                $this->highestLevel([$current->level, DepthProfile::LEVEL_L3]),
                "Escalated from {$current->level} to L3/L4: Target API deprecated or removed upstream{$detailStr}."
            ),
            self::ESCALATION_CONTRADICTION => DepthProfile::forLevel(
                $this->highestLevel([$current->level, DepthProfile::LEVEL_L3]),
                "Escalated from {$current->level} to L3/L4: Contradiction detected across sources{$detailStr}."
            ),
            self::ESCALATION_UNVETTED_PACKAGE => DepthProfile::forLevel(
                $this->highestLevel([$current->level, DepthProfile::LEVEL_L3]),
                "Escalated from {$current->level} to L3: Unvetted third-party package introduced{$detailStr}."
            ),
            default => DepthProfile::forLevel(
                $this->highestLevel([$current->level, DepthProfile::LEVEL_L2]),
                "Escalated from {$current->level}: {$escalationReason}{$detailStr}."
            ),
        };
    }

    private function evaluateStageDepth(int $stage, array &$reasons): string
    {
        if ($stage >= 1 && $stage <= 3) {
            $reasons[] = "Lifecycle Stage {$stage} (Architecture/Specification) mandates >= L3";
            return DepthProfile::LEVEL_L3;
        }
        if ($stage === 7 || $stage === 8) {
            $reasons[] = "Lifecycle Stage {$stage} (Implementation Planning) mandates >= L2";
            return DepthProfile::LEVEL_L2;
        }
        return DepthProfile::LEVEL_L1;
    }

    /**
     * @param array<int, string> $targetDomains
     */
    private function evaluateDomainDepth(array $targetDomains, array &$reasons): string
    {
        $highest = DepthProfile::LEVEL_L0;
        foreach ($targetDomains as $domain) {
            $upper = strtoupper(trim($domain));
            if ($upper === 'C' || $upper === 'D') {
                $reasons[] = "Domain {$upper} (Cryptography / Isolation) mandates L4 depth";
                $highest = $this->highestLevel([$highest, DepthProfile::LEVEL_L4]);
            } elseif ($upper === 'B') {
                $reasons[] = "Domain B (Cybersecurity) mandates >= L3 depth";
                $highest = $this->highestLevel([$highest, DepthProfile::LEVEL_L3]);
            } elseif ($upper === 'A' || $upper === 'E' || $upper === 'H' || $upper === 'I') {
                $reasons[] = "Domain {$upper} mandates >= L2 depth";
                $highest = $this->highestLevel([$highest, DepthProfile::LEVEL_L2]);
            }
        }
        return $highest;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function hasDependencyBlastRadius(string $prompt, array $metadata): bool
    {
        if (str_contains($prompt, 'composer.json') || str_contains($prompt, 'composer require')
            || str_contains($prompt, 'composer update') || str_contains($prompt, 'package.json')) {
            return true;
        }
        if (!empty($metadata['modifies_dependencies']) || !empty($metadata['modifies_framework_config'])) {
            return true;
        }
        return false;
    }

    /**
     * @param array<int, string> $keywords
     */
    private function matchesAny(string $haystack, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($haystack, $keyword)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param array<int, string> $levels
     */
    private function highestLevel(array $levels): string
    {
        $rank = [
            DepthProfile::LEVEL_L0 => 0,
            DepthProfile::LEVEL_L1 => 1,
            DepthProfile::LEVEL_L2 => 2,
            DepthProfile::LEVEL_L3 => 3,
            DepthProfile::LEVEL_L4 => 4,
        ];

        $maxRank = 0;
        $maxLevel = DepthProfile::LEVEL_L0;
        foreach ($levels as $lvl) {
            $r = $rank[$lvl] ?? 0;
            if ($r > $maxRank) {
                $maxRank = $r;
                $maxLevel = $lvl;
            }
        }
        return $maxLevel;
    }
}
