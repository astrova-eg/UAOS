<?php

declare(strict_types=1);

namespace UniversalAgentOS\Validation;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Exceptions\GovernanceViolationException;

/**
 * Validates Layer 3 custom extensions (agents, skills, governance) against Constitutional Invariants.
 */
class ExtensionValidator
{
    private PathResolverInterface $pathResolver;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * Scans all custom extensions in .agent-os/ and asserts they do not violate constitutional rules.
     *
     * @return array<string, int> Validated counts
     * @throws GovernanceViolationException If any extension attempts to weaken governance
     */
    public function validateExtensions(): array
    {
        $storageDir = $this->pathResolver->getProjectStorageDir();
        $counts = ['agents' => 0, 'skills' => 0, 'governance' => 0];

        // 1. Check custom governance additions
        $customGovDir = $storageDir . DIRECTORY_SEPARATOR . 'governance';
        if (is_dir($customGovDir)) {
            $files = glob($customGovDir . DIRECTORY_SEPARATOR . '*.md') ?: [];
            foreach ($files as $file) {
                $content = (string)file_get_contents($file);
                $this->assertNoWeakening($content, $file);
                $counts['governance']++;
            }
        }

        // 2. Check custom skills additions
        $customSkillsDir = $storageDir . DIRECTORY_SEPARATOR . 'skills';
        if (is_dir($customSkillsDir)) {
            $files = glob($customSkillsDir . DIRECTORY_SEPARATOR . '*.md') ?: [];
            foreach ($files as $file) {
                $content = (string)file_get_contents($file);
                $this->assertNoWeakening($content, $file);
                $counts['skills']++;
            }
        }

        // 3. Check custom agents additions
        $customAgentsDir = $storageDir . DIRECTORY_SEPARATOR . 'agents';
        if (is_dir($customAgentsDir)) {
            $files = glob($customAgentsDir . DIRECTORY_SEPARATOR . '*.md') ?: [];
            foreach ($files as $file) {
                $content = (string)file_get_contents($file);
                $this->assertNoWeakening($content, $file);
                $counts['agents']++;
            }
        }

        return $counts;
    }

    private function assertNoWeakening(string $content, string $filePath): void
    {
        $forbiddenKeywords = [
            '/bypass\s+gate\s*4/i',
            '/disable\s+tool\s*gateway/i',
            '/allow\s+force\s+push/i',
            '/disable\s+audit/i',
            '/skip\s+verification/i',
        ];

        foreach ($forbiddenKeywords as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                $filename = basename($filePath);
                throw new GovernanceViolationException(
                    "Constitutional violation in Layer 3 extension '{$filename}': Attempting to weaken security controls."
                );
            }
        }
    }
}
