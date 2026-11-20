<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\PathValidatorInterface;
use UniversalAgentOS\Exceptions\DirectoryEscapeException;
use UniversalAgentOS\Exceptions\GovernanceViolationException;

/**
 * Strict canonical filesystem boundary confiner across POSIX and Windows.
 */
class PathValidator implements PathValidatorInterface
{
    private PathResolverInterface $pathResolver;
    private string $projectRoot;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
        $this->projectRoot = $this->normalizePath($pathResolver->getProjectRootDir());
    }

    public function validatePath(string $targetPath, bool $mustExist = true): string
    {
        $this->assertSafePathString($targetPath);

        $normalized = $this->normalizePath($targetPath);
        if (!$this->isAbsolutePath($normalized)) {
            $normalized = $this->projectRoot . '/' . ltrim($normalized, '/');
        }

        if (file_exists($normalized)) {
            $real = realpath($normalized);
            if ($real === false) {
                throw new DirectoryEscapeException("Failed resolving canonical path: {$targetPath}");
            }
            $canonical = $this->normalizePath($real);
            $this->assertConfinedWithinRoot($canonical, $targetPath);
            return $canonical;
        }

        if ($mustExist) {
            throw new DirectoryEscapeException("Target path does not exist: {$targetPath}");
        }

        $canonicalAncestor = $this->resolveNearestAncestor($normalized);
        $this->assertConfinedWithinRoot($canonicalAncestor, $targetPath);

        return $normalized;
    }

    public function validateWriteTarget(string $targetPath): string
    {
        $canonical = $this->validatePath($targetPath, false);

        if ($this->isProtectedPath($canonical)) {
            throw new GovernanceViolationException("Write operation rejected on protected path: {$targetPath}");
        }

        return $canonical;
    }

    public function isProtectedPath(string $canonicalPath): bool
    {
        $norm = $this->normalizePath($canonicalPath);
        $protectedPatterns = [
            '/\.git(\/|$)/i',
            '/\.env(\..+)?$/i',
            '/\.agent-os\/governance(\/|$)/i',
            '/\/vendor\/universal-agent-os\/agent-os\/resources(\/|$)/i',
        ];

        foreach ($protectedPatterns as $pattern) {
            if (preg_match($pattern, $norm) === 1) {
                return true;
            }
        }

        return false;
    }

    public function normalizePath(string $path): string
    {
        $cleaned = str_replace('\\', '/', trim($path));
        // Normalize drive letters to lowercase on Windows: c:/ -> c:/
        if (preg_match('/^([a-zA-Z]):(\/.*)$/', $cleaned, $matches)) {
            $cleaned = strtolower($matches[1]) . ':' . $matches[2];
        }
        return rtrim($cleaned, '/');
    }

    private function assertSafePathString(string $path): void
    {
        if (str_contains($path, "\0")) {
            throw new DirectoryEscapeException("Null byte detected in path: {$path}");
        }

        if (preg_match('/(%00|%2e%2e|::\$DATA)/i', $path) === 1) {
            throw new DirectoryEscapeException("Encoded traversal or stream alias detected: {$path}");
        }

        if (str_starts_with($path, '//') || str_starts_with($path, '\\\\')) {
            throw new DirectoryEscapeException("UNC paths are prohibited: {$path}");
        }

        if (preg_match('/~[0-9]/', $path) === 1) {
            throw new DirectoryEscapeException("8.3 shortnames are prohibited: {$path}");
        }
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[a-zA-Z]:\//', $path) === 1;
    }

    private function assertConfinedWithinRoot(string $canonical, string $original): void
    {
        $root = $this->projectRoot;
        $isSame = ($canonical === $root);
        $isChild = str_starts_with($canonical, $root . '/');

        if (!$isSame && !$isChild) {
            throw new DirectoryEscapeException(
                "Path boundary escape detected: '{$original}' resolves to '{$canonical}' outside '{$root}'"
            );
        }
    }

    private function resolveNearestAncestor(string $path): string
    {
        $current = dirname($path);
        while ($current !== '' && $current !== '.' && $current !== '/') {
            if (file_exists($current)) {
                $real = realpath($current);
                if ($real !== false) {
                    return $this->normalizePath($real);
                }
            }
            $parent = dirname($current);
            if ($parent === $current) {
                break;
            }
            $current = $parent;
        }

        return $this->projectRoot;
    }
}
