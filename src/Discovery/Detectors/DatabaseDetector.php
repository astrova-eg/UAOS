<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery\Detectors;

/**
 * Heuristic detector discovering database engine and ORM without touching .env or credentials.
 */
class DatabaseDetector
{
    /**
     * @return array<string, mixed>
     */
    public function detect(string $projectRoot): array
    {
        $composerJsonPath = $projectRoot . '/composer.json';
        $composerContent = file_exists($composerJsonPath) ? (string)file_get_contents($composerJsonPath) : '';

        $engines = [];
        $orm = 'unknown';

        if (str_contains($composerContent, 'laravel/framework') || str_contains($composerContent, 'illuminate/database')) {
            $orm = 'eloquent';
        } elseif (str_contains($composerContent, 'doctrine/orm')) {
            $orm = 'doctrine';
        }

        if (str_contains($composerContent, 'pgsql') || str_contains($composerContent, 'postgresql')) {
            $engines[] = 'postgresql';
        }
        if (str_contains($composerContent, 'mysql') || str_contains($composerContent, 'mariadb')) {
            $engines[] = 'mysql';
        }
        if (str_contains($composerContent, 'sqlite')) {
            $engines[] = 'sqlite';
        }

        // Check structural config/database.php if present
        $dbConfig = $projectRoot . '/config/database.php';
        if (file_exists($dbConfig)) {
            $cfgRaw = (string)file_get_contents($dbConfig);
            if (str_contains($cfgRaw, "'default' => 'mysql'") || str_contains($cfgRaw, '"default" => "mysql"')) {
                $engines[] = 'mysql';
            } elseif (str_contains($cfgRaw, "'default' => 'pgsql'") || str_contains($cfgRaw, '"default" => "pgsql"')) {
                $engines[] = 'postgresql';
            } elseif (str_contains($cfgRaw, "'default' => 'sqlite'") || str_contains($cfgRaw, '"default" => "sqlite"')) {
                $engines[] = 'sqlite';
            }
        }

        $engines = array_values(array_unique($engines));
        $primaryEngine = !empty($engines) ? $engines[0] : 'unknown';

        return [
            'primary_engine' => $primaryEngine,
            'detected_engines' => $engines,
            'orm' => $orm,
            'source' => 'composer.json & config/database.php (structural only)',
            'status' => $primaryEngine !== 'unknown' ? 'automatically_detected' : 'unknown',
            'confidence' => $primaryEngine !== 'unknown' ? 0.90 : 0.0,
        ];
    }
}
