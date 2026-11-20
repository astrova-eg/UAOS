<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery\Detectors;

/**
 * Heuristic detector discovering frontend stack (Blade, Livewire, Filament, Vue, React, Tailwind).
 */
class FrontendDetector
{
    /**
     * @return array<string, mixed>
     */
    public function detect(string $projectRoot): array
    {
        $composerPath = $projectRoot . '/composer.json';
        $composerContent = file_exists($composerPath) ? (string)file_get_contents($composerPath) : '';

        $packageJsonPath = $projectRoot . '/package.json';
        $packageJsonContent = file_exists($packageJsonPath) ? (string)file_get_contents($packageJsonPath) : '';

        $frameworks = [];

        if (str_contains($composerContent, 'filament/filament')) {
            $frameworks[] = 'filament';
        }
        if (str_contains($composerContent, 'livewire/livewire')) {
            $frameworks[] = 'livewire';
        }
        if (str_contains($packageJsonContent, '"vue"') || str_contains($composerContent, 'inertiajs/inertia-laravel')) {
            $frameworks[] = 'vue_inertia';
        }
        if (str_contains($packageJsonContent, '"react"')) {
            $frameworks[] = 'react';
        }
        if (is_dir($projectRoot . '/resources/views')) {
            $frameworks[] = 'blade';
        }

        $css = [];
        if (str_contains($packageJsonContent, 'tailwindcss')) {
            $css[] = 'tailwind';
        }
        if (str_contains($packageJsonContent, 'bootstrap')) {
            $css[] = 'bootstrap';
        }

        $primary = !empty($frameworks) ? $frameworks[0] : 'standard_web';

        return [
            'primary_frontend' => $primary,
            'frameworks' => array_values(array_unique($frameworks)),
            'css_frameworks' => $css,
            'source' => 'composer.json & package.json inspection',
            'status' => 'automatically_detected',
            'confidence' => 0.90,
        ];
    }
}
