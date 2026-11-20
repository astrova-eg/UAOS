<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel\Commands;

use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Retrieval\HttpRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RestApiRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\TaskEnvelope;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Security\ToolGateway;

/**
 * Artisan command to execute an agent task through UAOS Orchestrator.
 */
class AgentOsRunCommand extends BaseArtisanCommand
{
    /** @var string */
    protected $signature = 'agent-os:run {--task=} {--prompt=} {--agent=} {--stage=1}';
    /** @var string */
    protected $description = 'Dispatch an autonomous agent task';

    public function handle(): int
    {
        $taskPrompt = (string)($this->option('prompt') ?: 'Diagnostic task');
        $agentId = $this->option('agent') ? (string)$this->option('agent') : null;
        $taskId = (string)($this->option('task') ?: 'ARTISAN-' . bin2hex(random_bytes(4)));
        $stage = (int)($this->option('stage') ?: 1);

        if (isset($this->laravel) && method_exists($this->laravel, 'has') && $this->laravel->has(Orchestrator::class)) {
            $orchestrator = $this->laravel->make(Orchestrator::class);
        } else {
            $resolver = new PathResolver();
            $validator = new PathValidator($resolver);
            $nonceStore = new PersistentNonceStore($resolver);
            $gate4 = new Gate4Verifier($resolver, $nonceStore, $validator);
            $gateway = new ToolGateway($resolver, $validator, $gate4);
            $evidence = new EvidenceCollector($resolver);
            $repo = new ResearchDossierRepository($resolver);
            $verifier = new Gate0ClearanceVerifier($repo, $resolver, $evidence);
            $retrieval = new RetrievalManager([new HttpRetrievalProvider(), new RestApiRetrievalProvider()]);
            $research = new ResearchOrchestrator(
                pathResolver: $resolver,
                dossierRepository: $repo,
                clearanceVerifier: $verifier,
                retrievalManager: $retrieval,
                evidenceCollector: $evidence
            );
            $orchestrator = new Orchestrator($resolver, $gateway, $evidence, null, $research);
        }

        $envelope = new TaskEnvelope(
            taskId: $taskId,
            taskPrompt: $taskPrompt,
            agentId: $agentId,
            stage: $stage
        );
        $result = $orchestrator->execute($envelope);
        $this->line($result->output);
        return $result->exitCode;
    }
}
