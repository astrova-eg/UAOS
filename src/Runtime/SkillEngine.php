<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\SkillEngineInterface;

/**
 * Stack-aware skill discovery, dependency resolution, and relevance filtering engine.
 */
class SkillEngine implements SkillEngineInterface
{
    private PathResolverInterface $pathResolver;
    /** @var array<string, array<string, mixed>>|null */
    private ?array $skillRegistry = null;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function resolveSkills(
        string $agentId,
        string $taskPrompt,
        array $projectProfile = [],
        int $contextBudget = 45000
    ): array {
        $available = $this->listAvailableSkills();
        $selected = [];
        $audit = [];

        $profileTech = (array)($projectProfile['technologies'] ?? []);
        $frontend = (string)($profileTech['frontend_framework'] ?? '');
        $database = (string)($profileTech['database_engine'] ?? '');
        $testing = (string)($profileTech['testing_framework'] ?? '');

        foreach ($available as $skillName => $skill) {
            $path = (string)($skill['path'] ?? '');
            $content = file_exists($path) ? (string)file_get_contents($path) : '';

            // Stack-aware exclusion checks
            if ($this->shouldExcludeSkill($skillName, $frontend, $database, $testing)) {
                $audit[$skillName] = [
                    'decision' => 'EXCLUDED',
                    'reason' => 'Incompatible with detected project stack',
                ];
                continue;
            }

            // Keyword relevance check
            if ($this->isRelevant($skillName, $agentId, $taskPrompt)) {
                $selected[$skillName] = $content;
                $audit[$skillName] = [
                    'decision' => 'INCLUDED',
                    'reason' => 'Matches task objectives and project stack',
                    'length' => strlen($content),
                ];
            } else {
                $audit[$skillName] = [
                    'decision' => 'EXCLUDED',
                    'reason' => 'Irrelevant to active task and agent role',
                ];
            }
        }

        // Context budget enforcement
        $totalChars = array_sum(array_map('strlen', $selected));
        if ($totalChars > $contextBudget) {
            $selected = $this->compactSkills($selected, $contextBudget, $audit);
        }

        return [
            'skills' => $selected,
            'audit' => $audit,
            'total_characters' => array_sum(array_map('strlen', $selected)),
        ];
    }

    public function listAvailableSkills(): array
    {
        if ($this->skillRegistry !== null) {
            return $this->skillRegistry;
        }

        $registry = [];
        $registryFile = $this->pathResolver->resolveResourcePath('registry/skill-registry.json');
        if (file_exists($registryFile)) {
            $raw = file_get_contents($registryFile);
            $data = is_string($raw) ? json_decode($raw, true) : [];
            $skillsList = (array)($data['skills'] ?? []);

            foreach ($skillsList as $item) {
                $filePath = (string)($item['file_path'] ?? $item['file'] ?? '');
                $slug = basename($filePath, '.md');
                $name = $slug !== '' ? $slug : (string)($item['id'] ?? '');
                $file = basename($filePath);
                try {
                    $resolvedPath = $this->pathResolver->resolveResourcePath("skills/{$file}");
                } catch (\Exception) {
                    $resolvedPath = $this->pathResolver->getPackageResourcesDir() . DIRECTORY_SEPARATOR . 'skills' . DIRECTORY_SEPARATOR . $file;
                }
                $registry[$name] = [
                    'id' => $item['id'] ?? $name,
                    'name' => $name,
                    'path' => $resolvedPath,
                    'metadata' => $item,
                ];
            }
        }

        $this->skillRegistry = $registry;
        return $registry;
    }

    public function isStackCompatible(array $skillMetadata, array $projectProfile): bool
    {
        $applicable = (array)($skillMetadata['applicable_stacks'] ?? []);
        if (empty($applicable)) {
            return true;
        }

        $tech = (array)($projectProfile['technologies'] ?? []);
        $backend = (string)($tech['backend_framework'] ?? 'generic_php');

        return in_array($backend, $applicable, true);
    }

    private function shouldExcludeSkill(string $skillName, string $frontend, string $database, string $testing): bool
    {
        if ($frontend === 'filament' || $frontend === 'livewire') {
            if (str_contains($skillName, 'vue-inertia') || str_contains($skillName, 'react')) {
                return true;
            }
        } elseif ($frontend === 'vue_inertia') {
            if (str_contains($skillName, 'filament-livewire')) {
                return true;
            }
        }

        if ($database === 'mysql' && str_contains($skillName, 'postgresql')) {
            return true;
        }
        if ($database === 'postgresql' && str_contains($skillName, 'mysql')) {
            return true;
        }

        if ($testing === 'pest' && str_contains($skillName, 'phpunit-generic')) {
            return true;
        }

        return false;
    }

    private function isRelevant(string $skillName, string $agentId, string $prompt): bool
    {
        // Core standards are universally relevant
        if (in_array($skillName, ['secure-coding-standards', 'php-engineering-standards', 'laravel-engineering-standards'], true)) {
            return true;
        }

        $p = strtolower($prompt);
        $name = str_replace('-', ' ', $skillName);
        $words = explode(' ', $name);

        foreach ($words as $w) {
            if (strlen($w) > 3 && str_contains($p, $w)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, string> $skills
     * @param array<string, array<string, mixed>> $audit
     * @return array<string, string>
     */
    private function compactSkills(array $skills, int $budget, array &$audit): array
    {
        $compacted = [];
        $currentChars = 0;

        foreach ($skills as $name => $content) {
            $sliceLength = (int)($budget / max(1, count($skills)));
            $trimmed = substr($content, 0, $sliceLength) . "\n\n... [Skill compacted to respect token budget]";
            $compacted[$name] = $trimmed;
            $currentChars += strlen($trimmed);
            $audit[$name]['decision'] = 'COMPACTED';
            $audit[$name]['compacted_length'] = strlen($trimmed);
        }

        return $compacted;
    }
}
