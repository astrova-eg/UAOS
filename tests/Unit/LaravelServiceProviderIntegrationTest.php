<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Laravel\UniversalAgentOSServiceProvider;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Contracts\Gate0ClearanceVerifierInterface;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Security\ToolGateway;
use UniversalAgentOS\Tests\Assert;

/**
 * Verifies production service provider bindings and composition root wiring.
 */
class LaravelServiceProviderIntegrationTest
{
    public static function run(): void
    {
        $container = new class {
            /** @var array<string, callable> */
            private array $bindings = [];
            /** @var array<string, mixed> */
            private array $instances = [];
            /** @var array<string, string> */
            private array $aliases = [];

            public function singleton(string $abstract, callable $concrete): void
            {
                $this->bindings[$abstract] = $concrete;
            }

            public function alias(string $abstract, string $alias): void
            {
                $this->aliases[$alias] = $abstract;
            }

            public function make(string $abstract): mixed
            {
                $target = $this->aliases[$abstract] ?? $abstract;
                if (isset($this->instances[$target])) {
                    return $this->instances[$target];
                }
                if (isset($this->bindings[$target])) {
                    $this->instances[$target] = ($this->bindings[$target])($this);
                    return $this->instances[$target];
                }
                throw new \RuntimeException("Unbound container dependency: {$target}");
            }

            public function has(string $abstract): bool
            {
                $target = $this->aliases[$abstract] ?? $abstract;
                return isset($this->bindings[$target]) || isset($this->instances[$target]);
            }

            public function basePath(): string
            {
                return dirname(__DIR__, 2);
            }
        };

        $provider = new UniversalAgentOSServiceProvider($container);
        $provider->register();

        // 1. EvidenceCollectorInterface binding
        Assert::assertTrue($container->has(EvidenceCollectorInterface::class), 'EvidenceCollectorInterface registered');
        $collector = $container->make(EvidenceCollectorInterface::class);
        Assert::assertTrue($collector instanceof EvidenceCollector, 'Resolves to real EvidenceCollector');

        // 2. DossierRepositoryInterface binding
        Assert::assertTrue($container->has(DossierRepositoryInterface::class), 'DossierRepositoryInterface registered');
        $repo = $container->make(DossierRepositoryInterface::class);
        Assert::assertTrue($repo instanceof ResearchDossierRepository, 'Resolves to real ResearchDossierRepository');

        // 3. Gate0ClearanceVerifierInterface binding
        Assert::assertTrue($container->has(Gate0ClearanceVerifierInterface::class), 'Gate0ClearanceVerifierInterface registered');
        $verifier = $container->make(Gate0ClearanceVerifierInterface::class);
        Assert::assertTrue($verifier instanceof Gate0ClearanceVerifier, 'Resolves to real Gate0ClearanceVerifier');

        // 4. RetrievalManager binding
        Assert::assertTrue($container->has(RetrievalManager::class), 'RetrievalManager registered');
        $retrieval = $container->make(RetrievalManager::class);
        Assert::assertTrue($retrieval instanceof RetrievalManager, 'Resolves to real RetrievalManager');

        // 5. ResearchEngineInterface binding
        Assert::assertTrue($container->has(ResearchEngineInterface::class), 'ResearchEngineInterface registered');
        $research = $container->make(ResearchEngineInterface::class);
        Assert::assertTrue($research instanceof ResearchOrchestrator, 'ResearchEngineInterface resolves to real ResearchOrchestrator');

        // 6. ToolGatewayInterface binding
        Assert::assertTrue($container->has(ToolGatewayInterface::class), 'ToolGatewayInterface registered');
        $gateway = $container->make(ToolGatewayInterface::class);
        Assert::assertTrue($gateway instanceof ToolGateway, 'Resolves to real ToolGateway');

        // 7. Orchestrator production resolution
        Assert::assertTrue($container->has(Orchestrator::class), 'Orchestrator registered');
        $orchestrator = $container->make(Orchestrator::class);
        Assert::assertTrue($orchestrator instanceof Orchestrator, 'Resolves to real production Orchestrator');

        // 8. Verify resolved Orchestrator has wired ResearchOrchestrator by testing Gate 0 evaluation
        $l0Task = new \UniversalAgentOS\Runtime\TaskEnvelope('TASK-CONTAINER-L0', 'Fix typo in comment', null, 1);
        $l0Plan = $orchestrator->planTask($l0Task);
        Assert::assertFalse($l0Plan['gate_0_blocked'], 'L0 task is exempt in container-resolved orchestrator');

        $l1Task = new \UniversalAgentOS\Runtime\TaskEnvelope('TASK-CONTAINER-BLOCKED', 'Architect cache invalidation', null, 2);
        $l1Plan = $orchestrator->planTask($l1Task);
        Assert::assertTrue($l1Plan['gate_0_blocked'], 'L2 task fails Gate 0 in container-resolved orchestrator without research evidence');
        Assert::assertTrue($l1Plan['research_dossier'] !== null, 'Research dossier generated by wired ResearchOrchestrator');
    }
}
