<?php

declare(strict_types=1);

namespace UniversalAgentOS\Adapters;

use UniversalAgentOS\Contracts\AdapterInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Exceptions\AdapterException;

/**
 * Registry discovering, instantiating, and caching registered platform adapters.
 */
class AdapterRegistry
{
    private PathResolverInterface $pathResolver;
    private ?ToolGatewayInterface $toolGateway;
    /** @var array<string, AdapterInterface> */
    private array $adapters = [];

    public function __construct(
        PathResolverInterface $pathResolver,
        ?ToolGatewayInterface $toolGateway = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->toolGateway = $toolGateway;
        $this->registerBuiltInAdapters();
    }

    public function register(AdapterInterface $adapter): void
    {
        $this->adapters[$adapter->getAdapterId()] = $adapter;
    }

    public function get(string $adapterId): AdapterInterface
    {
        if (!isset($this->adapters[$adapterId])) {
            throw new AdapterException("Unknown adapter identifier: '{$adapterId}'. Available: " . implode(', ', array_keys($this->adapters)));
        }
        return $this->adapters[$adapterId];
    }

    public function has(string $adapterId): bool
    {
        return isset($this->adapters[$adapterId]);
    }

    /**
     * @return array<string, AdapterInterface>
     */
    public function all(): array
    {
        return $this->adapters;
    }

    private function registerBuiltInAdapters(): void
    {
        $this->register(new CliAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new AntigravityAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new CursorAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new WindsurfAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new VsCodeAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new ClineAdapter($this->pathResolver, $this->toolGateway));
        $this->register(new JetBrainsAdapter($this->pathResolver, $this->toolGateway));
    }
}
