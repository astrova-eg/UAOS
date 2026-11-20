<?php

declare(strict_types=1);

namespace UniversalAgentOS\Security;

use UniversalAgentOS\Contracts\EvidenceCollectorInterface;
use UniversalAgentOS\Contracts\Gate4VerifierInterface;
use UniversalAgentOS\Contracts\PathResolverInterface;
use UniversalAgentOS\Contracts\PathValidatorInterface;
use UniversalAgentOS\Contracts\ToolGatewayInterface;
use UniversalAgentOS\Exceptions\GovernanceViolationException;

/**
 * Execution boundary enforcing Invariants 1-5, shell sanitization, and atomic I/O.
 */
class ToolGateway implements ToolGatewayInterface
{
    private PathResolverInterface $pathResolver;
    private PathValidatorInterface $pathValidator;
    private ?Gate4VerifierInterface $gate4Verifier;
    private ?EvidenceCollectorInterface $evidenceCollector;
    private ?KeyManager $keyManager;

    public function __construct(
        PathResolverInterface $pathResolver, PathValidatorInterface $pathValidator,
        ?Gate4VerifierInterface $gate4Verifier = null, ?EvidenceCollectorInterface $evidenceCollector = null,
        ?KeyManager $keyManager = null
    ) {
        $this->pathResolver = $pathResolver;
        $this->pathValidator = $pathValidator;
        $this->gate4Verifier = $gate4Verifier;
        $this->evidenceCollector = $evidenceCollector;
        $this->keyManager = $keyManager;
    }

    public function readFile(string $path, int $offset = 0, ?int $limit = null): string
    {
        $canonical = $this->pathValidator->validatePath($path, true);
        $content = file_get_contents($canonical);
        if ($content === false) { throw new GovernanceViolationException("Failed reading file: {$path}"); }
        if ($this->evidenceCollector !== null) {
            $this->evidenceCollector->recordEvent('read_file', $canonical, 'PERMITTED', ['bytes' => strlen($content)]);
        }
        return ($offset > 0 || $limit !== null) ? substr($content, $offset, $limit ?? strlen($content)) : $content;
    }

    public function writeFile(string $path, string $content, ?string $gate4Token = null, ?string $taskId = null): array
    {
        $canonical = $this->pathValidator->validateWriteTarget($path);
        $this->assertGate4Clearance($canonical, $gate4Token, $taskId);
        $targetDir = dirname($canonical);
        if (!is_dir($targetDir)) { mkdir($targetDir, 0755, true); }
        $tempFile = $targetDir . DIRECTORY_SEPARATOR . 'tmp_' . bin2hex(random_bytes(8)) . '.tmp';
        $handle = fopen($tempFile, 'w');
        if ($handle === false) { throw new GovernanceViolationException("Failed opening temporary file for write: {$tempFile}"); }
        try {
            if (!flock($handle, LOCK_EX)) { throw new GovernanceViolationException("Failed acquiring exclusive file lock for write: {$tempFile}"); }
            fwrite($handle, $content);
            fflush($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
        if (!rename($tempFile, $canonical)) {
            @unlink($tempFile);
            throw new GovernanceViolationException("Atomic rename failed for write target: {$canonical}");
        }
        $result = ['path' => $canonical, 'bytes_written' => strlen($content), 'sha256' => hash('sha256', $content), 'status' => 'WRITTEN'];
        if ($this->evidenceCollector !== null) { $this->evidenceCollector->recordEvent('write_file', $canonical, 'PERMITTED', $result); }
        return $result;
    }

    public function executeShell(string $command, ?string $workingDirectory = null, ?string $gate4Token = null): array
    {
        if ($this->isDangerousCommand($command)) {
            if ($this->evidenceCollector !== null) { $this->evidenceCollector->recordEvent('shell_exec', $command, 'REJECTED', ['reason' => 'Invariant 5 violation']); }
            throw new GovernanceViolationException("Dangerous shell command blocked under Constitutional Invariant 5: {$command}");
        }
        $tokens = $this->tokenizeCommand($command);
        if (empty($tokens) || (count($tokens) === 1 && $tokens[0] === '')) { throw new GovernanceViolationException("Cannot execute empty shell command."); }
        $workDir = $workingDirectory !== null ? $this->pathValidator->validatePath($workingDirectory, true) : $this->pathResolver->getProjectRootDir();
        $this->assertCommandGate4Clearance($tokens, $gate4Token);

        $startTime = microtime(true);
        $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
        $process = proc_open($tokens, $descriptors, $pipes, $workDir);
        if (!is_resource($process)) { throw new GovernanceViolationException("Failed spawning process for command: {$command}"); }
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);
        $result = [
            'command' => $command, 'exit_code' => $exitCode,
            'stdout' => is_string($stdout) ? $stdout : '', 'stderr' => is_string($stderr) ? $stderr : '',
            'execution_time_seconds' => round(microtime(true) - $startTime, 4),
        ];
        if ($this->evidenceCollector !== null) {
            $this->evidenceCollector->recordEvent('shell_exec', $command, $exitCode === 0 ? 'PERMITTED' : 'FLAGGED', $result);
        }
        return $result;
    }

    public function isDangerousCommand(string $command): bool
    {
        $cmd = trim($command);
        if ($cmd === '') { return true; }
        $denylist = [
            '/\b(rm\s+-[rf]{1,2}|rmdir\s+\/s|rd\s+\/s|del\s+\/[fsq]|erase\s+\/[fsq]|Remove-Item\b.*-(Recurse|r|Force|fo)|unlink\b)\b/i',
            '/\b(rm\s+.*-(r|f).*|drop\s+(database|table|schema)|truncate\s+table)\b/i',
            '/\bgit\s+push\b.*(--force|--force-with-lease|-f\b|\+[a-z0-9_\-\/]+|--delete|:\S+)/i',
            '/\bgit\s+(reset\s+(--hard|--merge)|clean\s+-[a-z]*[xdf]+|branch\s+.*-D)\b/i',
            '/\b(mkfs|dd\s+if=|\bformat\s+[a-z]:|diskpart|fdisk|chmod\s+777)\b/i',
            '/\b(curl|wget|irm|iwr|Invoke-WebRequest|Invoke-RestMethod)\b.*(\|\s*(sh|bash|powershell|pwsh|iex|Invoke-Expression)|-OutFile|>)/i',
            '/\b(certutil\b.*-urlcache|bitsadmin\b.*\/transfer|mshta\b|cscript\b|wscript\b|powershell\b.*(-enc|-encodedcommand|-e\s+))\b/i',
            '/\b(shutdown|reboot|init\s+0|taskkill\s+\/f|(cmd\.exe|cmd)\b.*\/c\b|(powershell\.exe|powershell|pwsh)\b.*-c(ommand)?\b)\b/i',
        ];
        foreach ($denylist as $pattern) {
            if (preg_match($pattern, $cmd) === 1) { return true; }
        }
        if ($this->hasShellInjectionOperators($cmd)) { return true; }
        $tokens = $this->tokenizeCommand($cmd);
        if (empty($tokens)) { return true; }
        $cleanExe = preg_replace('/\.(exe|cmd|bat)$/i', '', strtolower(basename(str_replace('\\', '/', $tokens[0]))));
        $allowed = ['php', 'composer', 'git', 'npm', 'npx', 'node', 'phpunit', 'agent-os', 'echo', 'dir', 'ls', 'pwd', 'whoami', 'cat', 'type'];
        if (!in_array($cleanExe, $allowed, true)) { return true; }
        if ($cleanExe === 'git') {
            $sub = strtolower($tokens[1] ?? '');
            if ($sub === 'push') {
                foreach (array_slice($tokens, 2) as $arg) {
                    if (in_array(strtolower($arg), ['--force', '--force-with-lease', '-f', '--delete'], true)) { return true; }
                    if (str_starts_with($arg, '+') || str_starts_with($arg, ':')) { return true; }
                }
            }
            if ($sub === 'reset') {
                foreach (array_slice($tokens, 2) as $arg) { if (in_array(strtolower($arg), ['--hard', '--merge'], true)) { return true; } }
            }
            if ($sub === 'clean') {
                foreach (array_slice($tokens, 2) as $arg) { if (preg_match('/^-[a-z]*[fdx]/i', $arg) === 1) { return true; } }
            }
            if ($sub === 'branch') {
                foreach (array_slice($tokens, 2) as $arg) { if ($arg === '-D' || strtolower($arg) === '--delete') { return true; } }
            }
            if ($sub === 'checkout' || $sub === 'switch') {
                foreach (array_slice($tokens, 2) as $arg) { if (in_array(strtolower($arg), ['-f', '--force', '--discard-changes'], true)) { return true; } }
            }
        }
        if ($cleanExe === 'php') {
            $fullArgs = implode(' ', array_slice($tokens, 1));
            if (str_contains($fullArgs, '-r') && preg_match('/(system|exec|passthru|shell_exec|popen|proc_open|unlink|rmdir)\s*\(/i', $fullArgs)) {
                return true;
            }
        }
        return false;
    }

    public function tokenizeCommand(string $cmd): array
    {
        $tokens = [];
        $len = strlen($cmd);
        $current = '';
        $inQuote = false;
        $quoteChar = '';
        $hadQuote = false;
        for ($i = 0; $i < $len; $i++) {
            $c = $cmd[$i];
            if ($c === '\\' && $inQuote && $i + 1 < $len && ($cmd[$i + 1] === $quoteChar || $cmd[$i + 1] === '\\')) {
                $current .= $cmd[$i + 1];
                $i++;
                continue;
            }
            if (!$inQuote && ($c === '"' || $c === "'")) {
                $inQuote = true;
                $quoteChar = $c;
                $hadQuote = true;
            } elseif ($inQuote && $c === $quoteChar) {
                $inQuote = false;
                $quoteChar = '';
            } elseif (!$inQuote && ctype_space($c)) {
                if ($current !== '' || $hadQuote) {
                    $tokens[] = $current;
                    $current = '';
                    $hadQuote = false;
                }
            } else {
                $current .= $c;
            }
        }
        if ($current !== '' || $hadQuote) { $tokens[] = $current; }
        return $tokens;
    }

    private function hasShellInjectionOperators(string $cmd): bool
    {
        if (preg_match('/(\`|\$\(|\r|\n)/', $cmd) === 1) { return true; }
        $len = strlen($cmd);
        $inQuote = false;
        $quoteChar = '';
        for ($i = 0; $i < $len; $i++) {
            $c = $cmd[$i];
            if (!$inQuote && ($c === '"' || $c === "'")) {
                $inQuote = true;
                $quoteChar = $c;
            } elseif ($inQuote && $c === $quoteChar) {
                $inQuote = false;
                $quoteChar = '';
            } elseif (!$inQuote && ($c === ';' || $c === '&' || $c === '|')) {
                return true;
            }
        }
        return false;
    }

    private function assertCommandGate4Clearance(array $tokens, ?string $gate4Token): void
    {
        if ($this->gate4Verifier === null) { return; }
        $exe = strtolower(preg_replace('/\.(exe|cmd|bat)$/i', '', basename(str_replace('\\', '/', $tokens[0]))));
        $sub = strtolower($tokens[1] ?? '');
        if (!$this->isMutatingCommand($exe, $sub, array_slice($tokens, 2))) { return; }
        if ($gate4Token === null || trim($gate4Token) === '') {
            throw new GovernanceViolationException("State-mutating shell command '{$exe} {$sub}' requires Gate 4 clearance token.");
        }
        $secret = $this->keyManager !== null ? $this->keyManager->getKey() : 'default-secret';
        $request = new Gate4Request(
            projectId: basename($this->pathResolver->getProjectRootDir()),
            projectRoot: $this->pathResolver->getProjectRootDir(),
            taskId: 'COMMAND-' . strtoupper($exe),
            targetArtifacts: [$this->pathResolver->getProjectRootDir()]
        );
        $this->gate4Verifier->verifyToken($gate4Token, $request, $secret);
    }

    private function isMutatingCommand(string $exe, string $sub, array $subArgs): bool
    {
        if ($exe === 'composer') { return in_array($sub, ['require', 'remove', 'update', 'install'], true); }
        if ($exe === 'git') {
            if (in_array($sub, ['push', 'commit', 'merge', 'rebase', 'apply', 'reset', 'clean', 'revert', 'cherry-pick'], true)) { return true; }
            if ($sub === 'remote') {
                $subSub = strtolower($subArgs[0] ?? '');
                return in_array($subSub, ['set-url', 'add', 'remove', 'rm', 'rename', 'prune'], true);
            }
            if ($sub === 'branch') {
                if (empty($subArgs)) { return false; }
                foreach ($subArgs as $a) {
                    if (in_array($a, ['-d', '-D', '-m', '-M', '-c', '-C', '--delete', '--move'], true) || !str_starts_with($a, '-')) { return true; }
                }
                return false;
            }
            if ($sub === 'tag') {
                if (empty($subArgs)) { return false; }
                foreach ($subArgs as $a) {
                    if (in_array($a, ['-d', '-a', '-s', '--delete'], true) || !str_starts_with($a, '-')) { return true; }
                }
                return false;
            }
            if ($sub === 'checkout' || $sub === 'switch') {
                foreach ($subArgs as $a) {
                    if (in_array($a, ['-b', '-B', '-c', '-C', '-f', '--force', '--discard-changes'], true)) { return true; }
                }
                return false;
            }
        }
        return false;
    }

    private function assertGate4Clearance(string $canonical, ?string $gate4Token, ?string $taskId): void
    {
        $storageDir = $this->pathResolver->getProjectStorageDir();
        if (str_starts_with($canonical, $storageDir)) { return; }
        if ($this->gate4Verifier === null) { return; }
        if ($gate4Token === null || trim($gate4Token) === '') {
            throw new GovernanceViolationException("Stage 10+ code modification requires Gate 4 clearance token.");
        }
        $secret = $this->keyManager !== null ? $this->keyManager->getKey() : 'default-secret';
        $request = new Gate4Request(
            projectId: basename($this->pathResolver->getProjectRootDir()),
            projectRoot: $this->pathResolver->getProjectRootDir(),
            taskId: $taskId ?? 'UNSPECIFIED-TASK',
            targetArtifacts: [$canonical]
        );
        $this->gate4Verifier->verifyToken($gate4Token, $request, $secret);
    }
}
