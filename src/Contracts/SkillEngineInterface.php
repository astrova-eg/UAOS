<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Stack-aware skill discovery, dependency resolution, and relevance filtering engine.
 */
interface SkillEngineInterface
{
    /**
     * Resolves and filters the relevant skills for a given agent and task profile.
     *
     * @param string $agentId Selected agent ID
     * @param string $taskPrompt Natural language task description
     * @param array<string, mixed> $projectProfile Active project technology profile
     * @param int $contextBudget Maximum total character budget for skills
     * @return array<string, mixed> Result containing ['skills' => [...], 'audit' => [...]]
     */
    public function resolveSkills(
        string $agentId,
        string $taskPrompt,
        array $projectProfile = [],
        int $contextBudget = 45000
    ): array;

    /**
     * Lists all registered skills from package resources and host extensions.
     *
     * @return array<string, array<string, mixed>>
     */
    public function listAvailableSkills(): array;

    /**
     * Checks whether a skill is compatible with the given technology stack.
     *
     * @param array<string, mixed> $skillMetadata
     * @param array<string, mixed> $projectProfile
     */
    public function isStackCompatible(array $skillMetadata, array $projectProfile): bool;
}
