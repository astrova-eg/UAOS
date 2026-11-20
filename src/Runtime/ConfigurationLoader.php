<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\PathResolverInterface;

/**
 * Cascading configuration loader (Default -> Host Config -> Environment Variables).
 */
class ConfigurationLoader
{
    private PathResolverInterface $pathResolver;
    /** @var array<string, mixed>|null */
    private ?array $cachedConfig = null;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        if ($this->cachedConfig !== null) {
            return $this->cachedConfig;
        }

        $config = $this->getDefaults();

        $configFile = $this->pathResolver->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'config.json';
        if (file_exists($configFile)) {
            $raw = file_get_contents($configFile);
            if ($raw !== false) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $config = array_replace_recursive($config, $decoded);
                }
            }
        }

        $this->applyEnvOverrides($config);
        $this->cachedConfig = $config;
        return $config;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->load();
        $segments = explode('.', $key);
        $current = $config;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return $default;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    /**
     * @return array<string, mixed>
     */
    private function getDefaults(): array
    {
        return [
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
    }

    /**
     * @param array<string, mixed> $config
     */
    private function applyEnvOverrides(array &$config): void
    {
        $envProjectId = getenv('AGENT_OS_PROJECT_ID');
        if (is_string($envProjectId) && $envProjectId !== '') {
            $config['project_id'] = $envProjectId;
        }

        $envAdapter = getenv('AGENT_OS_ADAPTER');
        if (is_string($envAdapter) && $envAdapter !== '') {
            $config['active_adapter'] = $envAdapter;
        }
    }
}
