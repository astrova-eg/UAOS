<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery\Detectors;

/**
 * Heuristic detector discovering testing frameworks (Pest, PHPUnit, Dusk).
 */
class TestingFrameworkDetector
{
    /**
     * @return array<string, mixed>
     */
    public function detect(string $projectRoot): array
    {
        $composerPath = $projectRoot . '/composer.json';
        $composerContent = file_exists($composerPath) ? (string)file_get_contents($composerPath) : '';

        $frameworks = [];

        if (str_contains($composerContent, 'pestphp/pest')) {
            $frameworks[] = 'pest';
        }
        if (str_contains($composerContent, 'phpunit/phpunit') || file_exists($projectRoot . '/phpunit.xml')) {
            $frameworks[] = 'phpunit';
        }
        if (str_contains($composerContent, 'laravel/dusk')) {
            $frameworks[] = 'dusk';
        }

        $primary = !empty($frameworks) ? $frameworks[0] : 'phpunit';

        return [
            'primary_framework' => $primary,
            'frameworks' => $frameworks,
            'source' => 'composer.json & phpunit.xml',
            'status' => 'automatically_detected',
            'confidence' => 0.95,
        ];
    }
}
