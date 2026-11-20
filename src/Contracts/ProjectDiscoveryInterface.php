<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Inspection engine discovering host project technologies and architecture.
 */
interface ProjectDiscoveryInterface
{
    /**
     * Inspects the host project workspace and produces discovered tech facts.
     *
     * @param string $projectRoot Path to host project root
     * @return array<string, mixed> Discovered tech facts with provenance
     */
    public function discover(string $projectRoot): array;

    /**
     * Synthesizes and writes the complete project-profile.json file.
     *
     * @param string $projectRoot Host project root
     * @param array<string, mixed> $manualOverrides User-supplied overrides
     * @return array<string, mixed> The complete generated profile data
     */
    public function generateProfile(string $projectRoot, array $manualOverrides = []): array;

    /**
     * Validates a profile array against project-profile.schema.json.
     *
     * @param array<string, mixed> $profileData
     * @return array<int, string> List of validation errors (empty if valid)
     */
    public function validateProfile(array $profileData): array;
}
