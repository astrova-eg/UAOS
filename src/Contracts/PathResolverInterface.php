<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Dual-path locator resolving immutable package resources and mutable host project workspace.
 */
interface PathResolverInterface
{
    /**
     * Resolves the immutable package resources directory (resources/ in vendor package).
     */
    public function getPackageResourcesDir(): string;

    /**
     * Resolves the host application workspace root directory.
     */
    public function getProjectRootDir(): string;

    /**
     * Resolves the host project mutable storage directory ({ProjectRoot}/.agent-os/).
     */
    public function getProjectStorageDir(): string;

    /**
     * Resolves the host project configuration directory ({ProjectRoot}/.agent-os/config/).
     */
    public function getProjectConfigDir(): string;

    /**
     * Resolves the host project runtime state directory ({ProjectRoot}/.agent-os/state/).
     */
    public function getProjectStateDir(): string;

    /**
     * Resolves the host project reports and audit directory ({ProjectRoot}/.agent-os/reports/).
     */
    public function getProjectReportsDir(): string;

    /**
     * Resolves the host project Gate 4 authorizations directory ({ProjectRoot}/.agent-os/authorizations/).
     */
    public function getProjectAuthorizationsDir(): string;

    /**
     * Confines an arbitrary relative path within the project root.
     */
    public function resolveProjectPath(string $relativePath): string;

    /**
     * Confines an arbitrary relative path within package resources.
     */
    public function resolveResourcePath(string $relativeResource): string;
}
