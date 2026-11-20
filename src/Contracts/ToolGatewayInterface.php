<?php

declare(strict_types=1);

namespace UniversalAgentOS\Contracts;

/**
 * Execution boundary enforcing Constitutional Invariants, dangerous shell blocking, and I/O safety.
 */
interface ToolGatewayInterface
{
    /**
     * Executes a file read operation with path confinement.
     */
    public function readFile(string $path, int $offset = 0, ?int $limit = null): string;

    /**
     * Executes a file write operation subject to Gate 4 clearance and PathValidator.
     *
     * @param string $path File path relative or absolute
     * @param string $content Content to write
     * @param string|null $gate4Token Cryptographic authorization token for Stage 10+
     * @param string|null $taskId Associated task ID
     * @return array<string, mixed> Outcome with byte length, sha256, and status
     */
    public function writeFile(
        string $path,
        string $content,
        ?string $gate4Token = null,
        ?string $taskId = null
    ): array;

    /**
     * Executes a shell command subject to Invariant 5 dangerous pattern blocking.
     *
     * @param string $command Shell command string
     * @param string|null $workingDirectory Working directory (default project root)
     * @param string|null $gate4Token Cryptographic authorization token
     * @return array<string, mixed> Array containing exit_code, stdout, stderr, execution_time
     */
    public function executeShell(
        string $command,
        ?string $workingDirectory = null,
        ?string $gate4Token = null
    ): array;

    /**
     * Checks whether a command string violates Invariant 5 dangerous command patterns.
     */
    public function isDangerousCommand(string $command): bool;
}
