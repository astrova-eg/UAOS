<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Exceptions\DirectoryEscapeException;
use UniversalAgentOS\Tests\Assert;

class PathValidatorAdversarialTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);

        // 1. Null byte injection
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath("composer.json\0.evil.php", false);
        }, "Null byte path rejected");

        // 2. Encoded traversal
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath("%2e%2e%2fetc/passwd", false);
        }, "Encoded traversal sequence rejected");

        // 3. UNC remote path injection
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath("\\\\malicious-smb\\share\\exploit.php", false);
        }, "Windows UNC path rejected");

        // 4. NTFS Alternate Data Stream (ADS)
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath("composer.json::\$DATA", false);
        }, "NTFS Alternate Data Stream rejected");

        // 5. 8.3 shortname alias
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath("PROGRA~1/evil.dll", false);
        }, "8.3 shortname rejected");

        // 6. Boundary prefix collision: target matching project path prefix but outside boundary
        $root = $validator->normalizePath($resolver->getProjectRootDir());
        $collisionPath = $root . "-sensitive-backup/secrets.json";
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator, $collisionPath) {
            $validator->validatePath($collisionPath, false);
        }, "Directory boundary prefix collision rejected fail-closed");
    }
}
