<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Intelligence;

use UniversalAgentOS\Research\Contracts\VersionEvaluatorInterface;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Models\VersionEvaluationResult;

/**
 * Deterministic engine for analyzing engineering versions and compatibility.
 */
class VersionIntelligenceEngine implements VersionEvaluatorInterface
{
    private const SEMVER_REGEX = '/^v?(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';
    private const CALVER_REGEX = '/^v?(20\d{2})\.(0?[1-9]|1[0-2])(?:\.(0?[1-9]|[12]\d|3[01]))?$/';
    private const ORDINAL_REGEX = '/^v?([1-9]\d*)$/';
    private const DATE_REGEX = '/^(20\d{2})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/';

    public function evaluateVersion(
        string $version,
        ?SourceDefinition $source = null,
        array $context = []
    ): VersionEvaluationResult {
        $vTrim = trim($version);
        $scheme = $this->detectScheme($vTrim);
        $parsed = $this->parseVersion($vTrim, $scheme);

        $state = VersionEvaluationResult::STATE_ACTIVE;
        $isOutdated = false;
        $isSupported = true;
        $reasons = [];

        if ($scheme === VersionEvaluationResult::SCHEME_UNKNOWN) {
            $state = VersionEvaluationResult::STATE_UNKNOWN;
            $reasons[] = "Unrecognized version pattern '{$version}'. Handled safely as unclassified.";
            return new VersionEvaluationResult(
                rawVersion: $version,
                scheme: $scheme,
                state: $state,
                isOutdated: false,
                isSupported: true,
                parsedParts: [],
                compatibilityMetadata: [],
                reasons: $reasons
            );
        }

        // Check if context or source defines EOL / minimum supported versions
        $eolVersions = $context['eol_versions'] ?? [];
        $deprecatedVersions = $context['deprecated_versions'] ?? [];
        $targetVersion = $context['target_version'] ?? null;
        $supportedRange = $context['supported_range'] ?? null;

        if (in_array($vTrim, $eolVersions, true)) {
            $state = VersionEvaluationResult::STATE_END_OF_LIFE;
            $isSupported = false;
            $isOutdated = true;
            $reasons[] = "Version '{$vTrim}' is explicitly marked End-of-Life (EOL).";
        } elseif (in_array($vTrim, $deprecatedVersions, true)) {
            $state = VersionEvaluationResult::STATE_DEPRECATED;
            $isOutdated = true;
            $reasons[] = "Version '{$vTrim}' is marked deprecated.";
        }

        // Compare against target version if provided
        if ($targetVersion !== null && is_string($targetVersion) && trim($targetVersion) !== '') {
            $cmp = $this->compare($vTrim, trim($targetVersion));
            if ($cmp < 0) {
                $isOutdated = true;
                $reasons[] = "Version '{$vTrim}' is older than target reference '{$targetVersion}'.";
            } elseif ($cmp > 0) {
                $reasons[] = "Version '{$vTrim}' is newer than target reference '{$targetVersion}'.";
            } else {
                $reasons[] = "Version '{$vTrim}' exactly matches target reference.";
            }
        }

        $compat = [
            'scheme' => $scheme,
            'parsed' => $parsed,
            'source_id' => $source?->sourceId,
            'supported_range' => $supportedRange,
        ];

        return new VersionEvaluationResult(
            rawVersion: $version,
            scheme: $scheme,
            state: $state,
            isOutdated: $isOutdated,
            isSupported: $isSupported,
            parsedParts: $parsed,
            compatibilityMetadata: $compat,
            reasons: $reasons
        );
    }

    public function detectScheme(string $version): string
    {
        $v = trim($version);
        if ($v === '') {
            return VersionEvaluationResult::SCHEME_UNKNOWN;
        }

        if (preg_match(self::SEMVER_REGEX, $v)) {
            return VersionEvaluationResult::SCHEME_SEMVER;
        }
        if (preg_match(self::CALVER_REGEX, $v)) {
            return VersionEvaluationResult::SCHEME_CALVER;
        }
        if (preg_match(self::DATE_REGEX, $v)) {
            return VersionEvaluationResult::SCHEME_DATE;
        }
        if (preg_match(self::ORDINAL_REGEX, $v)) {
            return VersionEvaluationResult::SCHEME_ORDINAL;
        }

        return VersionEvaluationResult::SCHEME_UNKNOWN;
    }

    public function compare(string $v1, string $v2): int
    {
        $v1Trim = ltrim(trim($v1), 'vV');
        $v2Trim = ltrim(trim($v2), 'vV');

        if ($v1Trim === $v2Trim) {
            return 0;
        }

        $s1 = $this->detectScheme($v1Trim);
        $s2 = $this->detectScheme($v2Trim);

        if ($s1 === VersionEvaluationResult::SCHEME_SEMVER && $s2 === VersionEvaluationResult::SCHEME_SEMVER) {
            return version_compare($v1Trim, $v2Trim);
        }

        if ($s1 === VersionEvaluationResult::SCHEME_ORDINAL && $s2 === VersionEvaluationResult::SCHEME_ORDINAL) {
            return (int)$v1Trim <=> (int)$v2Trim;
        }

        // Standard string comparison fallback for dates or CalVer
        return version_compare($v1Trim, $v2Trim);
    }

    private function parseVersion(string $version, string $scheme): array
    {
        $v = trim($version);
        if ($scheme === VersionEvaluationResult::SCHEME_SEMVER && preg_match(self::SEMVER_REGEX, $v, $m)) {
            return [
                'major' => (int)$m[1],
                'minor' => (int)$m[2],
                'patch' => (int)$m[3],
                'prerelease' => $m[4] ?? null,
                'build' => $m[5] ?? null,
            ];
        }

        if ($scheme === VersionEvaluationResult::SCHEME_CALVER && preg_match(self::CALVER_REGEX, $v, $m)) {
            return [
                'year' => (int)$m[1],
                'month' => (int)$m[2],
                'day' => isset($m[3]) ? (int)$m[3] : null,
            ];
        }

        if ($scheme === VersionEvaluationResult::SCHEME_ORDINAL && preg_match(self::ORDINAL_REGEX, $v, $m)) {
            return ['ordinal' => (int)$m[1]];
        }

        return ['raw' => $version];
    }
}
