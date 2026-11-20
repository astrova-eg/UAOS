<?php

declare(strict_types=1);

namespace UniversalAgentOS\Discovery;

use UniversalAgentOS\Contracts\PathResolverInterface;

/**
 * Validates project-profile.json against the extensible JSON schema with non-blocking unknown support.
 */
class ProfileValidator
{
    private PathResolverInterface $pathResolver;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    /**
     * @param array<string, mixed> $profile
     * @return array<int, string> List of validation errors
     */
    public function validate(array $profile): array
    {
        $errors = [];

        if (empty($profile['schema_version'])) {
            $errors[] = "Missing mandatory field 'schema_version'";
        }

        if (empty($profile['project_name'])) {
            $errors[] = "Missing mandatory field 'project_name'";
        }

        if (!isset($profile['technologies']) || !is_array($profile['technologies'])) {
            $errors[] = "Missing mandatory section 'technologies'";
        } else {
            if (empty($profile['technologies']['backend_framework'])) {
                $errors[] = "Missing mandatory technology: 'backend_framework'";
            }
        }

        if (!isset($profile['provenance']) || !is_array($profile['provenance'])) {
            $errors[] = "Missing mandatory section 'provenance' tracking fact sources";
        }

        return $errors;
    }
}
