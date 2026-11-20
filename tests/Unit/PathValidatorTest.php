<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Security\PathValidator;
use UniversalAgentOS\Exceptions\DirectoryEscapeException;
use UniversalAgentOS\Exceptions\GovernanceViolationException;
use UniversalAgentOS\Tests\Assert;

class PathValidatorTest
{
    public static function run(): void
    {
        $resolver = new PathResolver();
        $validator = new PathValidator($resolver);

        // 1. Valid existing path
        $valid = $validator->validatePath('composer.json', true);
        Assert::assertTrue(file_exists($valid), "Valid existing path resolves canonical realpath");

        // 2. Normalization
        $normalized = $validator->normalizePath('foo\\bar/baz//');
        Assert::assertEquals('foo/bar/baz', $normalized, "Normalizes separators and strips trailing slashes");

        // 3. Traversal rejection
        Assert::assertThrows(DirectoryEscapeException::class, function () use ($validator) {
            $validator->validatePath('../../windows/system32', false);
        }, "Traversal attack throws DirectoryEscapeException");

        // 4. Protected file rejection
        Assert::assertThrows(GovernanceViolationException::class, function () use ($validator) {
            $validator->validateWriteTarget('.env');
        }, "Writing to .env throws GovernanceViolationException");

        Assert::assertThrows(GovernanceViolationException::class, function () use ($validator) {
            $validator->validateWriteTarget('.git/config');
        }, "Writing to .git throws GovernanceViolationException");
    }
}
