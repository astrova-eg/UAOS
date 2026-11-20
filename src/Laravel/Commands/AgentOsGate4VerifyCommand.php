<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel\Commands;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\Gate4Request;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\KeyManager;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;

/**
 * Artisan command to verify a Gate 4 cryptographic clearance token.
 */
class AgentOsGate4VerifyCommand extends BaseArtisanCommand
{
    /** @var string */
    protected $signature = 'agent-os:gate4:verify {token} {--task=}';
    /** @var string */
    protected $description = 'Verify Gate 4 clearance token against task context';

    public function handle(): int
    {
        $tokenJson = (string)$this->argument('token');
        $taskId = (string)($this->option('task') ?: 'UNSPECIFIED-TASK');

        $resolver = new PathResolver();
        $nonceStore = new PersistentNonceStore($resolver);
        $validator = new PathValidator($resolver);
        $verifier = new Gate4Verifier($resolver, $nonceStore, $validator);
        $keyManager = new KeyManager($resolver);

        $request = new Gate4Request(
            projectId: basename($resolver->getProjectRootDir()),
            projectRoot: $resolver->getProjectRootDir(),
            taskId: $taskId
        );

        $response = $verifier->verifyToken($tokenJson, $request, $keyManager->getKey());
        $this->info("Gate 4 Status: {$response->decision} - {$response->reason}");
        return $response->cleared ? 0 : 1;
    }
}
