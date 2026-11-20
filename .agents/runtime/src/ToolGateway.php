<?php
declare(strict_types=1);

namespace UniversalCore\Runtime;

final class ToolGateway
{
    private string $projectRoot;
    private Gate4Verifier $gate4Verifier;
    /** @var list<array<string, mixed>> */
    private array $auditTrail = [];

    public function __construct(?string $projectRoot = null, ?Gate4Verifier $gate4Verifier = null)
    {
        $this->projectRoot = $projectRoot ?? dirname(__DIR__, 3);
        $this->gate4Verifier = $gate4Verifier ?? new Gate4Verifier($this->projectRoot);
    }

    /**
     * @param array{
     *     agent_id: string,
     *     department: string,
     *     operation: string,
     *     target?: string,
     *     content?: string,
     *     command?: string,
     *     stage?: int,
     *     gate4_token_path?: string,
     *     secret_key?: string
     * } $request
     * @return array{permitted: bool, executed: bool, output: mixed, error: string|null, violation: string|null}
     */
    public function evaluateAndExecute(array $request): array
    {
        $agentId = (string)($request['agent_id'] ?? 'UNKNOWN');
        $department = (string)($request['department'] ?? 'UNKNOWN');
        $operation = (string)($request['operation'] ?? 'UNKNOWN');
        $stage = (int)($request['stage'] ?? 1);

        // 1. Mandatory Invariant Checks
        // Prohibition 1: Gate 4 self-approval or signing by agents
        if ($operation === 'approve_gate4' || $operation === 'sign_gate4') {
            return $this->reject($request, "Agents are constitutionally forbidden from approving or signing Gate 4.", "INVARIANT-1");
        }

        // Prohibition 2: Modifying Tier 1-3 Governance files by non-Dept A agents
        if (in_array($operation, ['write_file', 'delete_file'], true)) {
            $target = str_replace('\\', '/', (string)($request['target'] ?? ''));
            if (str_contains($target, '.agents/governance/') && !str_starts_with($department, 'DEPT-A') && $agentId !== 'GOV-001') {
                return $this->reject($request, "Agent {$agentId} in {$department} lacks authority to modify governance documents.", "INVARIANT-5");
            }
        }

        // Prohibition 3: Code Implementation (Stage 10) requires verified Gate 4 Token
        if ($stage === 10 && in_array($operation, ['write_file', 'modify_code'], true)) {
            $tokenPath = (string)($request['gate4_token_path'] ?? '');
            if ($tokenPath === '') {
                return $this->reject($request, "Stage 10 code writing blocked: Missing mandatory Gate 4 Human Authorization Token.", "GATE-4-ENFORCEMENT");
            }
            $vCheck = $this->gate4Verifier->verifyToken($tokenPath, $request['secret_key'] ?? null);
            if (!$vCheck['valid']) {
                return $this->reject($request, "Stage 10 code writing blocked: Invalid Gate 4 Token ({$vCheck['error']}).", "GATE-4-INVALID");
            }
        }

        // Prohibition 4: Dangerous Shell Commands
        if ($operation === 'run_command') {
            $cmd = (string)($request['command'] ?? '');
            $forbidden = ['rm -rf /', 'drop database', 'format', 'mkfs', 'del /f /s /q c:'];
            foreach ($forbidden as $bad) {
                if (stripos($cmd, $bad) !== false) {
                    return $this->reject($request, "Forbidden destructive command detected: '{$bad}'.", "SECURITY-DESTRUCTIVE-BLOCK");
                }
            }
        }

        // 2. Execute Authorized Operation
        return $this->executeAuthorized($request);
    }

    /**
     * @param array<string, mixed> $request
     * @return array{permitted: bool, executed: bool, output: mixed, error: string|null, violation: string|null}
     */
    private function executeAuthorized(array $request): array
    {
        $operation = (string)$request['operation'];
        $output = null;
        $error = null;
        $executed = false;

        try {
            switch ($operation) {
                case 'read_file':
                    $target = (string)$request['target'];
                    $path = $this->resolvePath($target);
                    if (!file_exists($path)) {
                        $error = "File not found: {$path}";
                    } else {
                        $output = file_get_contents($path);
                        $executed = true;
                    }
                    break;

                case 'write_file':
                    $target = (string)$request['target'];
                    $content = (string)($request['content'] ?? '');
                    $path = $this->resolvePath($target);
                    $dir = dirname($path);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0777, true);
                    }
                    $bytes = file_put_contents($path, $content);
                    if ($bytes === false) {
                        $error = "Failed to write file: {$path}";
                    } else {
                        $output = ['path' => $path, 'bytes_written' => $bytes];
                        $executed = true;
                    }
                    break;

                case 'delete_file':
                    $target = (string)$request['target'];
                    $path = $this->resolvePath($target);
                    if (file_exists($path)) {
                        unlink($path);
                        $output = ['path' => $path, 'deleted' => true];
                        $executed = true;
                    } else {
                        $error = "File does not exist: {$path}";
                    }
                    break;

                case 'run_command':
                    $cmd = (string)$request['command'];
                    $descriptors = [
                        0 => ['pipe', 'r'],
                        1 => ['pipe', 'w'],
                        2 => ['pipe', 'w']
                    ];
                    $proc = proc_open($cmd, $descriptors, $pipes, $this->projectRoot);
                    if (is_resource($proc)) {
                        fclose($pipes[0]);
                        $stdout = stream_get_contents($pipes[1]);
                        fclose($pipes[1]);
                        $stderr = stream_get_contents($pipes[2]);
                        fclose($pipes[2]);
                        $exitCode = proc_close($proc);
                        $output = [
                            'stdout' => $stdout,
                            'stderr' => $stderr,
                            'exit_code' => $exitCode
                        ];
                        $executed = ($exitCode === 0);
                        if ($exitCode !== 0) {
                            $error = "Command exited with code {$exitCode}: " . trim((string)$stderr);
                        }
                    } else {
                        $error = "Failed to launch process for command.";
                    }
                    break;

                default:
                    $error = "Unsupported operation '{$operation}'.";
            }
        } catch (\Throwable $e) {
            $error = "Execution exception: " . $e->getMessage();
        }

        $record = [
            'timestamp' => date('c'),
            'agent_id' => $request['agent_id'] ?? 'UNKNOWN',
            'operation' => $operation,
            'permitted' => true,
            'executed' => $executed,
            'error' => $error,
            'output' => $output
        ];
        $this->auditTrail[] = $record;

        return [
            'permitted' => true,
            'executed' => $executed,
            'output' => $output,
            'error' => $error,
            'violation' => null
        ];
    }

    /**
     * @param array<string, mixed> $request
     * @param string $reason
     * @param string $violation
     * @return array{permitted: bool, executed: bool, output: null, error: string, violation: string}
     */
    private function reject(array $request, string $reason, string $violation): array
    {
        $record = [
            'timestamp' => date('c'),
            'agent_id' => $request['agent_id'] ?? 'UNKNOWN',
            'operation' => $request['operation'] ?? 'UNKNOWN',
            'permitted' => false,
            'executed' => false,
            'error' => $reason,
            'violation' => $violation
        ];
        $this->auditTrail[] = $record;

        return [
            'permitted' => false,
            'executed' => false,
            'output' => null,
            'error' => $reason,
            'violation' => $violation
        ];
    }

    private function resolvePath(string $path): string
    {
        $norm = str_replace('\\', '/', $path);
        if (str_starts_with($norm, $this->projectRoot) || preg_match('/^[A-Za-z]:\//', $norm)) {
            return $norm;
        }
        return $this->projectRoot . '/' . ltrim($norm, '/');
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getAuditTrail(): array
    {
        return $this->auditTrail;
    }
}
