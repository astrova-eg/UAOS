<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class SkillEngine
{
    private string $registryPath;
    private string $skillsDir;
    /** @var array<string, array<string, mixed>> */
    private array $skillsById = [];
    /** @var array<string, list<string>> */
    private array $skillsByAgent = [];
    /** @var array<string, list<string>> */
    private array $skillsByRole = [];
    /** @var array<string, string> */
    private array $loadedSkillCache = [];

    public function __construct(?string $registryPath = null, ?string $skillsDir = null)
    {
        $base = dirname(__DIR__, 2);
        $this->registryPath = $registryPath ?? $base . '/registry/skill-registry.json';
        $this->skillsDir = $skillsDir ?? $base . '/skills';
        $this->loadRegistry();
    }

    private function loadRegistry(): void
    {
        if (!file_exists($this->registryPath)) {
            throw new \RuntimeException("Canonical skill registry not found: {$this->registryPath}");
        }

        $raw = file_get_contents($this->registryPath);
        if ($raw === false) {
            throw new \RuntimeException("Failed to read skill registry: {$this->registryPath}");
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['skills']) || !is_array($decoded['skills'])) {
            throw new \RuntimeException("Malformed skill registry: {$this->registryPath}");
        }

        foreach ($decoded['skills'] as $skill) {
            if (!isset($skill['id'], $skill['file_path'])) {
                continue;
            }
            $id = (string)$skill['id'];
            $this->skillsById[$id] = $skill;

            if (isset($skill['target_agents']) && is_array($skill['target_agents'])) {
                foreach ($skill['target_agents'] as $agentId) {
                    $this->skillsByAgent[(string)$agentId][] = $id;
                }
            }

            if (isset($skill['target_roles']) && is_array($skill['target_roles'])) {
                foreach ($skill['target_roles'] as $role) {
                    $this->skillsByRole[strtolower(trim((string)$role))][] = $id;
                }
            }
        }
    }

    /**
     * @param string $agentId
     * @param string|null $role
     * @param list<string> $taskKeywords
     * @return list<string>
     */
    public function discoverSkillIds(string $agentId, ?string $role = null, array $taskKeywords = []): array
    {
        $selected = [];

        // 1. By Agent ID
        if (isset($this->skillsByAgent[$agentId])) {
            foreach ($this->skillsByAgent[$agentId] as $id) {
                $selected[$id] = true;
            }
        }

        // 2. By Role
        if ($role !== null) {
            $normRole = strtolower(trim($role));
            if (isset($this->skillsByRole[$normRole])) {
                foreach ($this->skillsByRole[$normRole] as $id) {
                    $selected[$id] = true;
                }
            }
        }

        // 3. By Task Keywords
        foreach ($taskKeywords as $keyword) {
            $kw = strtolower(trim($keyword));
            if ($kw === '') {
                continue;
            }
            foreach ($this->skillsById as $id => $skill) {
                $desc = strtolower((string)($skill['description'] ?? ''));
                $name = strtolower((string)($skill['name'] ?? ''));
                if (str_contains($desc, $kw) || str_contains($name, $kw) || str_contains(strtolower($id), $kw)) {
                    $selected[$id] = true;
                }
            }
        }

        return array_keys($selected);
    }

    /**
     * @param list<string> $skillIds
     * @return array<string, array{meta: array<string, mixed>, content: string}>
     */
    public function loadSkills(array $skillIds): array
    {
        $result = [];

        foreach ($skillIds as $id) {
            if (!isset($this->skillsById[$id])) {
                throw new \RuntimeException("Skill ID '{$id}' does not exist in canonical registry.");
            }

            $meta = $this->skillsById[$id];
            $relativeFile = (string)$meta['file_path'];
            $baseName = basename($relativeFile);
            $fullPath = $this->skillsDir . '/' . $baseName;

            if (!file_exists($fullPath)) {
                throw new \RuntimeException("Physical skill file missing for '{$id}': {$fullPath}");
            }

            if (!isset($this->loadedSkillCache[$id])) {
                $content = file_get_contents($fullPath);
                if ($content === false || trim($content) === '') {
                    throw new \RuntimeException("Failed to read skill content for '{$id}' at {$fullPath}");
                }
                $this->loadedSkillCache[$id] = $content;
            }

            $result[$id] = [
                'meta' => $meta,
                'content' => $this->loadedSkillCache[$id],
            ];
        }

        return $result;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAllSkills(): array
    {
        return $this->skillsById;
    }
}
