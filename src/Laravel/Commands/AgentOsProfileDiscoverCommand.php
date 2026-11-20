<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel\Commands;

use UniversalAgentOS\Discovery\ProjectDiscoveryEngine;
use UniversalAgentOS\Runtime\PathResolver;

/**
 * Artisan command to automatically discover project stack and update profile.
 */
class AgentOsProfileDiscoverCommand extends BaseArtisanCommand
{
    /** @var string */
    protected $signature = 'agent-os:profile:discover';
    /** @var string */
    protected $description = 'Discover host project technology stack and write project-profile.json';

    public function handle(): int
    {
        $resolver = new PathResolver();
        $discovery = new ProjectDiscoveryEngine($resolver);
        $profile = $discovery->generateProfile($resolver->getProjectRootDir());
        $this->info("Project profile successfully updated: " . ($profile['project_name'] ?? ''));
        return 0;
    }
}
