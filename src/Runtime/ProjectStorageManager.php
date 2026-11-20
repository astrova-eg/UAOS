<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\PathResolverInterface;

/**
 * Manages host workspace storage (.agent-os/) scaffolding, permissions, and directory structure.
 */
class ProjectStorageManager
{
    private PathResolverInterface $pathResolver;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Initializes the complete host workspace layout under .agent-os/
     *
     * @return array<string, string> Created directory paths
     */
    public function initializeStorage(): array
    {
        $base = $this->pathResolver->getProjectStorageDir();
        $directories = [
            'base' => $base,
            'config' => $base . DIRECTORY_SEPARATOR . 'config',
            'agents' => $base . DIRECTORY_SEPARATOR . 'agents',
            'authorizations' => $base . DIRECTORY_SEPARATOR . 'authorizations',
            'evidence' => $base . DIRECTORY_SEPARATOR . 'evidence',
            'governance' => $base . DIRECTORY_SEPARATOR . 'governance',
            'reports' => $base . DIRECTORY_SEPARATOR . 'reports',
            'skills' => $base . DIRECTORY_SEPARATOR . 'skills',
            'state' => $base . DIRECTORY_SEPARATOR . 'state',
            'sessions' => $base . DIRECTORY_SEPARATOR . 'state' . DIRECTORY_SEPARATOR . 'sessions',
            'secrets' => $base . DIRECTORY_SEPARATOR . '.secrets',
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $this->publishGitignore($base);
        $this->publishDefaultConfig($base);

        return $directories;
    }

    /**
     * Asserts that all required directories exist.
     */
    public function isInitialized(): bool
    {
        $base = $this->pathResolver->getProjectStorageDir();
        return is_dir($base)
            && is_dir($base . DIRECTORY_SEPARATOR . 'reports')
            && is_dir($base . DIRECTORY_SEPARATOR . 'state');
    }

    private function publishGitignore(string $base): void
    {
        $gitignorePath = $base . DIRECTORY_SEPARATOR . '.gitignore';
        if (!file_exists($gitignorePath)) {
            $content = "# Ignore ephemeral state, sessions, and cryptographic secrets\n"
                . "state/\n"
                . ".secrets/\n"
                . "*.tmp\n"
                . "\n"
                . "# Track configuration, audit trails, and human clearances\n"
                . "!reports/\n"
                . "!evidence/\n"
                . "!authorizations/\n"
                . "!config.json\n"
                . "!project-profile.json\n";
            file_put_contents($gitignorePath, $content);
        }
    }

    private function publishDefaultConfig(string $base): void
    {
        $configPath = $base . DIRECTORY_SEPARATOR . 'config.json';
        if (!file_exists($configPath)) {
            $template = [
                'project_id' => basename($this->pathResolver->getProjectRootDir()),
                'version' => '2.0.0',
                'active_adapter' => 'cli',
                'mcp' => [
                    'enabled' => true,
                    'servers' => [],
                ],
                'security' => [
                    'gate4_required_from_stage' => 10,
                    'key_id' => 'default',
                    'token_ttl_seconds' => 3600,
                ],
                'orchestration' => [
                    'default_agent' => 'A01',
                    'context_char_budget' => 45000,
                ],
            ];
            file_put_contents($configPath, json_encode($template, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }
    }
}
