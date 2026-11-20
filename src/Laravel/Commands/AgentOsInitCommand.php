<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel\Commands;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\ProjectStorageManager;

/**
 * Artisan command to initialize UAOS host storage layout.
 */
class AgentOsInitCommand extends BaseArtisanCommand
{
    /** @var string */
    protected $signature = 'agent-os:init';
    /** @var string */
    protected $description = 'Initialize .agent-os/ workspace directory structure';

    public function handle(): int
    {
        $resolver = new PathResolver();
        $manager = new ProjectStorageManager($resolver);
        $manager->initializeStorage();
        $this->info("Initialized .agent-os/ workspace.");
        return 0;
    }
}
