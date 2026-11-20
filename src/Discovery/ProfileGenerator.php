<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery;

/**
 * Generates standardized project profile structures with fact provenance and prompt injection defense.
 */
class ProfileGenerator
{
    /**
     * @param array<string, mixed> $facts Detected facts from detectors
     * @param array<string, mixed> $manualOverrides User-supplied overrides
     * @return array<string, mixed> Complete sanitized profile payload
     */
    public function generate(string $projectName, array $facts, array $manualOverrides = []): array
    {
        $sanitizedName = $this->sanitizeMetadataString($projectName);

        $backend = $this->sanitizeMetadataString(
            (string)($manualOverrides['backend_framework'] ?? $facts['backend_framework']['value'] ?? 'laravel')
        );
        $phpVersion = $this->sanitizeMetadataString(
            (string)($manualOverrides['php_version'] ?? $facts['php_version']['value'] ?? '8.3')
        );
        $db = $this->sanitizeMetadataString(
            (string)($manualOverrides['database_engine'] ?? $facts['database']['primary_engine'] ?? 'mysql')
        );
        $frontend = $this->sanitizeMetadataString(
            (string)($manualOverrides['frontend_framework'] ?? $facts['frontend']['primary_frontend'] ?? 'blade')
        );
        $testing = $this->sanitizeMetadataString(
            (string)($manualOverrides['testing_framework'] ?? $facts['testing']['primary_framework'] ?? 'pest')
        );
        $arch = $this->sanitizeMetadataString(
            (string)($manualOverrides['architecture_pattern'] ?? $facts['architecture']['primary_pattern'] ?? 'mvc')
        );

        $provenance = [];
        $provenance['backend_framework'] = [
            'value' => $backend,
            'status' => isset($manualOverrides['backend_framework']) ? 'manual_override' : ($facts['backend_framework']['status'] ?? 'automatically_detected'),
            'source' => isset($manualOverrides['backend_framework']) ? 'user_config' : ($facts['backend_framework']['source'] ?? 'composer.json'),
        ];
        $provenance['database_engine'] = [
            'value' => $db,
            'status' => isset($manualOverrides['database_engine']) ? 'manual_override' : ($facts['database']['status'] ?? 'automatically_detected'),
            'source' => isset($manualOverrides['database_engine']) ? 'user_config' : ($facts['database']['source'] ?? 'heuristics'),
        ];
        $provenance['frontend_framework'] = [
            'value' => $frontend,
            'status' => isset($manualOverrides['frontend_framework']) ? 'manual_override' : ($facts['frontend']['status'] ?? 'automatically_detected'),
            'source' => isset($manualOverrides['frontend_framework']) ? 'user_config' : ($facts['frontend']['source'] ?? 'heuristics'),
        ];

        return [
            'schema_version' => '2.0.0',
            'profile_version' => '1.0.0',
            'project_name' => $sanitizedName,
            'technologies' => [
                'backend_framework' => $backend,
                'php_version' => $phpVersion,
                'database_engine' => $db,
                'orm' => $facts['database']['orm'] ?? 'eloquent',
                'frontend_framework' => $frontend,
                'testing_framework' => $testing,
                'architecture_pattern' => $arch,
            ],
            'subsystems' => $manualOverrides['subsystems'] ?? [],
            'provenance' => $provenance,
            'last_inspected_at' => date('c'),
        ];
    }

    /**
     * Sanitizes discovered strings against prompt injection delimiters.
     */
    private function sanitizeMetadataString(string $input): string
    {
        $sanitized = trim($input);
        // Strip markdown header injections and instruction injection tags
        $sanitized = preg_replace('/(SYSTEM:|INSTRUCTION:|IGNORE PREVIOUS|BEGIN PROMPT)/i', '', $sanitized);
        return htmlspecialchars((string)$sanitized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
