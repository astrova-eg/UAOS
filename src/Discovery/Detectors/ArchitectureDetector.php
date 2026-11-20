<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery\Detectors;

/**
 * Heuristic detector for project architectural pattern (MVC, DDD, Modular, Clean Architecture).
 */
class ArchitectureDetector
{
    /**
     * @return array<string, mixed>
     */
    public function detect(string $projectRoot): array
    {
        $patterns = [];

        if (is_dir($projectRoot . '/app/Domain') || is_dir($projectRoot . '/src/Domain')) {
            $patterns[] = 'domain_driven_design';
        }

        if (is_dir($projectRoot . '/app/Modules') || is_dir($projectRoot . '/modules')) {
            $patterns[] = 'modular';
        }

        if (is_dir($projectRoot . '/app/Http/Controllers') || is_dir($projectRoot . '/app/Models')) {
            $patterns[] = 'mvc';
        }

        if (empty($patterns)) {
            $patterns[] = 'generic_php';
        }

        $primary = $patterns[0];
        return [
            'primary_pattern' => $primary,
            'detected_patterns' => $patterns,
            'source' => 'filesystem_directory_structure',
            'status' => 'automatically_detected',
            'confidence' => 0.85,
        ];
    }
}
