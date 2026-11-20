<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Contracts;

use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Validation contract for authoritative source definitions.
 */
interface SourceValidatorInterface
{
    /**
     * Validates raw or semi-structured source definition array against the canonical schema.
     *
     * @param array<string, mixed> $data Raw source definition data
     * @return array<int, string> List of validation errors (empty if valid)
     */
    public function validateArray(array $data): array;

    /**
     * Validates a typed SourceDefinition instance.
     *
     * @param SourceDefinition $source Source instance to validate
     * @return array<int, string> List of validation errors (empty if valid)
     */
    public function validate(SourceDefinition $source): array;

    /**
     * Asserts that a source definition is strictly valid, throwing on error.
     *
     * @throws \InvalidArgumentException If validation fails
     */
    public function assertValid(SourceDefinition $source): void;
}
