<?php

declare(strict_types=1);

namespace UniversalAgentOS\Runtime;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Exceptions\PathResolutionException;

/**
 * Dual-path locator resolving immutable package resources and mutable host project workspace.
 */
class PathResolver implements PathResolverInterface
{
    private string $packageResourcesDir;
    private string $projectRootDir;

    public function __construct(?string $packageResourcesDir = null, ?string $projectRootDir = null)
    {
        $this->packageResourcesDir = $packageResourcesDir !== null
            ? $this->canonicalizeExistingDir($packageResourcesDir, 'Package resources directory')
            : $this->detectPackageResourcesDir();

        $this->projectRootDir = $projectRootDir !== null
            ? $this->canonicalizeExistingDir($projectRootDir, 'Host project root directory')
            : $this->detectProjectRootDir();
    }

    public function getPackageResourcesDir(): string
    {
        return $this->packageResourcesDir;
    }

    public function getProjectRootDir(): string
    {
        return $this->projectRootDir;
    }

    public function getProjectStorageDir(): string
    {
        return $this->projectRootDir . DIRECTORY_SEPARATOR . '.agent-os';
    }

    public function getProjectConfigDir(): string
    {
        return $this->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'config';
    }

    public function getProjectStateDir(): string
    {
        return $this->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'state';
    }

    public function getProjectReportsDir(): string
    {
        return $this->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'reports';
    }

    public function getProjectAuthorizationsDir(): string
    {
        return $this->getProjectStorageDir() . DIRECTORY_SEPARATOR . 'authorizations';
    }

    public function resolveProjectPath(string $relativePath): string
    {
        $trimmed = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath), DIRECTORY_SEPARATOR);
        return $this->projectRootDir . DIRECTORY_SEPARATOR . $trimmed;
    }

    public function resolveResourcePath(string $relativeResource): string
    {
        $trimmed = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeResource), DIRECTORY_SEPARATOR);
        $full = $this->packageResourcesDir . DIRECTORY_SEPARATOR . $trimmed;
        if (!file_exists($full)) {
            throw new PathResolutionException("Resource not found: {$trimmed}");
        }
        return realpath($full) ?: $full;
    }

    private function detectPackageResourcesDir(): string
    {
        // 1. Direct package root candidate: dirname(__DIR__, 2)/resources
        $candidate = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'resources';
        if (is_dir($candidate)) {
            $real = realpath($candidate);
            if ($real !== false) {
                return $real;
            }
        }

        // 2. Legacy fallback during transition (.agents/)
        $legacy = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.agents';
        if (is_dir($legacy) && is_dir($legacy . DIRECTORY_SEPARATOR . 'agents')) {
            $realLegacy = realpath($legacy);
            if ($realLegacy !== false) {
                return $realLegacy;
            }
        }

        throw new PathResolutionException('Package resources directory could not be located.');
    }

    private function detectProjectRootDir(): string
    {
        // 1. Environment variable override
        $env = getenv('AGENT_OS_PROJECT_ROOT');
        if (is_string($env) && $env !== '' && is_dir($env)) {
            $real = realpath($env);
            if ($real !== false) {
                return $real;
            }
        }

        // 2. Upward traversal from current working directory
        $cwd = getcwd();
        if ($cwd !== false) {
            $found = $this->traverseUpwardForRoot($cwd);
            if ($found !== null) {
                return $found;
            }
        }

        // 3. Upward traversal from package directory (handles vendor/ installation)
        $pkgDir = dirname(__DIR__, 4);
        if (is_dir($pkgDir)) {
            $found = $this->traverseUpwardForRoot($pkgDir);
            if ($found !== null) {
                return $found;
            }
        }

        // 4. Fallback to package root parent if valid
        $fallback = dirname(__DIR__, 2);
        if (file_exists($fallback . DIRECTORY_SEPARATOR . 'composer.json')) {
            $real = realpath($fallback);
            if ($real !== false) {
                return $real;
            }
        }

        throw new PathResolutionException('Host project root could not be determined.');
    }

    private function traverseUpwardForRoot(string $startDir): ?string
    {
        $current = $startDir;
        while (true) {
            if (
                file_exists($current . DIRECTORY_SEPARATOR . 'composer.json') ||
                is_dir($current . DIRECTORY_SEPARATOR . '.agent-os') ||
                is_dir($current . DIRECTORY_SEPARATOR . '.git')
            ) {
                $real = realpath($current);
                if ($real !== false) {
                    return $real;
                }
            }
            $parent = dirname($current);
            if ($parent === $current || $parent === '') {
                break;
            }
            $current = $parent;
        }
        return null;
    }

    private function canonicalizeExistingDir(string $path, string $label): string
    {
        if (!is_dir($path)) {
            throw new PathResolutionException("{$label} does not exist: {$path}");
        }
        $real = realpath($path);
        if ($real === false) {
            throw new PathResolutionException("{$label} could not be resolved: {$path}");
        }
        return $real;
    }
}
