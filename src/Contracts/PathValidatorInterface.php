<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Strict filesystem path boundary confiner across POSIX and Windows.
 */
interface PathValidatorInterface
{
    /**
     * Validates and returns canonical path confined strictly within project root.
     * Handles existing and non-existing paths, symlinks, junctions, UNCs, and case folding.
     *
     * @throws \UniversalAgentOS\Exceptions\DirectoryEscapeException If path traverses out of boundary
     * @throws \UniversalAgentOS\Exceptions\GovernanceViolationException If path targets protected file
     */
    public function validatePath(string $targetPath, bool $mustExist = true): string;

    /**
     * Validates whether a file write target is permitted, checking boundary and protected files.
     */
    public function validateWriteTarget(string $targetPath): string;

    /**
     * Checks if a target path points to a protected file or directory (.git, vendor, .env*).
     */
    public function isProtectedPath(string $canonicalPath): bool;

    /**
     * Normalizes a path string (separator normalization, drive letter case folding on Windows).
     */
    public function normalizePath(string $path): string;
}
