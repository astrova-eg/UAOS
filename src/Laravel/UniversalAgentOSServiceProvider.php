<?php

declare(strict_types=1);

namespace UniversalAgentOS\Laravel;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Runtime\ProjectStorageManager;
use UniversalAgentOS\Security\EvidenceCollector;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Security\PersistentNonceStore;
use UniversalAgentOS\Security\Gate4Verifier;
use UniversalAgentOS\Security\ToolGateway;
use UniversalAgentOS\Runtime\Orchestrator;
use UniversalAgentOS\Discovery\ProjectDiscoveryEngine;
use UniversalAgentOS\Research\Contracts\DossierRepositoryInterface;
use UniversalAgentOS\Research\Contracts\Gate0ClearanceVerifierInterface;
use UniversalAgentOS\Research\Contracts\ResearchEngineInterface;
use UniversalAgentOS\Research\Contracts\SourceRegistryInterface;
use UniversalAgentOS\Research\Contracts\TrustEvaluatorInterface;
use UniversalAgentOS\Research\Engine\Gate0ClearanceVerifier;
use UniversalAgentOS\Research\Engine\ResearchOrchestrator;
use UniversalAgentOS\Research\Intelligence\TrustIntelligenceEngine;
use UniversalAgentOS\Research\Registry\SourceRegistry;
use UniversalAgentOS\Research\Repository\ResearchDossierRepository;
use UniversalAgentOS\Research\Repository\SourceRepository;
use UniversalAgentOS\Research\Retrieval\HttpRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RestApiRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;

if (!class_exists(\Illuminate\Support\ServiceProvider::class)) {
    abstract class BaseServiceProvider
    {
        /** @var mixed */
        protected $app;

        public function __construct(mixed $app = null)
        {
            $this->app = $app;
        }

        public function isDeferred(): bool
        {
            return false;
        }

        /** @return array<int, string> */
        public function provides(): array
        {
            return [];
        }

        /** @return array<string, mixed> */
        public function when(): array
        {
            return [];
        }

        public function callBootingCallbacks(): void
        {
        }

        public function callBootedCallbacks(): void
        {
        }

        /** @param array<int, class-string> $commands */
        public function commands(array $commands): void
        {
        }
    }
    class_alias(BaseServiceProvider::class, \Illuminate\Support\ServiceProvider::class);
}

/**
 * Optional Laravel Framework Service Provider registering UAOS singletons and Artisan commands.
 */
class UniversalAgentOSServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register(): void
    {
        if ($this->app === null || !method_exists($this->app, 'singleton')) {
            return;
        }

        $this->app->singleton(PathResolverInterface::class, function () {
            $basePath = method_exists($this->app, 'basePath') ? $this->app->basePath() : null;
            return new PathResolver(projectRootDir: $basePath);
        });
        $this->app->alias(PathResolverInterface::class, PathResolver::class);

        $this->app->singleton(EvidenceCollectorInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            return new EvidenceCollector($resolver);
        });
        $this->app->alias(EvidenceCollectorInterface::class, EvidenceCollector::class);

        $this->app->singleton(DossierRepositoryInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            return new ResearchDossierRepository($resolver);
        });
        $this->app->alias(DossierRepositoryInterface::class, ResearchDossierRepository::class);

        $this->app->singleton(Gate0ClearanceVerifierInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            $repo = $app->make(DossierRepositoryInterface::class);
            $evidence = $app->make(EvidenceCollectorInterface::class);
            return new Gate0ClearanceVerifier($repo, $resolver, $evidence);
        });
        $this->app->alias(Gate0ClearanceVerifierInterface::class, Gate0ClearanceVerifier::class);

        $this->app->singleton(RetrievalManager::class, function () {
            return new RetrievalManager([
                new HttpRetrievalProvider(),
                new RestApiRetrievalProvider(),
            ]);
        });

        $this->app->singleton(ResearchEngineInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            $repo = $app->make(DossierRepositoryInterface::class);
            $verifier = $app->make(Gate0ClearanceVerifierInterface::class);
            $retrieval = $app->make(RetrievalManager::class);
            $evidence = $app->make(EvidenceCollectorInterface::class);
            return new ResearchOrchestrator(
                pathResolver: $resolver,
                dossierRepository: $repo,
                clearanceVerifier: $verifier,
                retrievalManager: $retrieval,
                evidenceCollector: $evidence
            );
        });
        $this->app->alias(ResearchEngineInterface::class, ResearchOrchestrator::class);

        $this->app->singleton(ToolGatewayInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            $validator = new PathValidator($resolver);
            $nonceStore = new PersistentNonceStore($resolver);
            $gate4 = new Gate4Verifier($resolver, $nonceStore, $validator);
            return new ToolGateway($resolver, $validator, $gate4);
        });
        $this->app->alias(ToolGatewayInterface::class, ToolGateway::class);

        $this->app->singleton(Orchestrator::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            $gateway = $app->make(ToolGatewayInterface::class);
            $evidence = $app->make(EvidenceCollectorInterface::class);
            $research = $app->make(ResearchEngineInterface::class);
            return new Orchestrator(
                pathResolver: $resolver,
                toolGateway: $gateway,
                evidenceCollector: $evidence,
                researchEngine: $research
            );
        });

        $this->app->singleton(TrustEvaluatorInterface::class, function () {
            return new TrustIntelligenceEngine();
        });
        $this->app->alias(TrustEvaluatorInterface::class, TrustIntelligenceEngine::class);

        $this->app->singleton(SourceRegistryInterface::class, function ($app) {
            $resolver = $app->make(PathResolverInterface::class);
            $repo = new SourceRepository($resolver);
            return new SourceRegistry($repo);
        });
        $this->app->alias(SourceRegistryInterface::class, SourceRegistry::class);
    }

    public function boot(): void
    {
        if ($this->app === null || !method_exists($this->app, 'runningInConsole')) {
            return;
        }

        if ($this->app->runningInConsole() && method_exists($this, 'commands')) {
            $this->commands([
                \UniversalAgentOS\Laravel\Commands\AgentOsInitCommand::class,
                \UniversalAgentOS\Laravel\Commands\AgentOsProfileDiscoverCommand::class,
                \UniversalAgentOS\Laravel\Commands\AgentOsRunCommand::class,
                \UniversalAgentOS\Laravel\Commands\AgentOsGate4VerifyCommand::class,
            ]);
        }
    }
}
