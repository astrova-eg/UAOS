<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

use UniversalAgentOS\Exceptions\McpException;

/**
 * Local process-based MCP transport communicating via standard input/output (STDIO).
 */
class StdioTransport implements McpTransportInterface
{
    private string $command;
    /** @var array<int, string> */
    private array $arguments;
    /** @var array<string, string> */
    private array $env;
    /** @var resource|null */
    private $process = null;
    /** @var array<int, resource> */
    private array $pipes = [];

    /**
     * @param string $command Executable path or command
     * @param array<int, string> $arguments Command arguments
     * @param array<string, string> $env Environment variables
     */
    public function __construct(string $command, array $arguments = [], array $env = [])
    {
        $this->command = $command;
        $this->arguments = $arguments;
        $this->env = $env;
    }

    public function start(): void
    {
        if ($this->isOpen()) {
            return;
        }

        $cmdLine = $this->command;
        if (!empty($this->arguments)) {
            $cmdLine .= ' ' . implode(' ', array_map('escapeshellarg', $this->arguments));
        }

        $descriptors = [
            0 => ['pipe', 'r'], // STDIN
            1 => ['pipe', 'w'], // STDOUT
            2 => ['pipe', 'w'], // STDERR
        ];

        $envMerged = array_merge($_ENV, getenv(), $this->env);
        $this->process = proc_open($cmdLine, $descriptors, $this->pipes, null, $envMerged);

        if (!is_resource($this->process)) {
            throw new McpException("Failed spawning local MCP server process: {$cmdLine}");
        }

        stream_set_blocking($this->pipes[1], false);
        stream_set_blocking($this->pipes[2], false);
    }

    public function send(McpMessage $message): void
    {
        if (!$this->isOpen()) {
            $this->start();
        }

        $jsonLine = $message->toJson() . "\n";
        $written = fwrite($this->pipes[0], $jsonLine);
        fflush($this->pipes[0]);

        if ($written === false) {
            throw new McpException("Failed writing JSON-RPC message to MCP stdin pipe.");
        }
    }

    public function receive(int $timeoutSeconds = 10): ?McpMessage
    {
        if (!$this->isOpen()) {
            throw new McpException("Cannot receive from closed MCP STDIO transport.");
        }

        $buffer = '';
        $startTime = microtime(true);

        while ((microtime(true) - $startTime) < $timeoutSeconds) {
            $read = [$this->pipes[1]];
            $write = null;
            $except = null;

            $numChanged = stream_select($read, $write, $except, 0, 100000); // 100ms
            if ($numChanged > 0) {
                $chunk = fgets($this->pipes[1]);
                if ($chunk !== false) {
                    $buffer .= $chunk;
                    if (str_ends_with(trim($buffer), "\n") || str_contains($buffer, "\n")) {
                        $line = trim($buffer);
                        if ($line !== '') {
                            try {
                                return McpMessage::fromJson($line);
                            } catch (\InvalidArgumentException $e) {
                                throw new McpException("Malformed JSON-RPC received from MCP: {$line}", 0, $e);
                            }
                        }
                    }
                }
            }

            // Check if process terminated unexpectedly
            $status = proc_get_status($this->process);
            if (!$status['running']) {
                $stderr = stream_get_contents($this->pipes[2]) ?: '';
                $this->close();
                throw new McpException("MCP process terminated prematurely. STDERR: {$stderr}");
            }
        }

        return null; // Timed out
    }

    public function close(): void
    {
        foreach ($this->pipes as $pipe) {
            if (is_resource($pipe)) {
                fclose($pipe);
            }
        }
        $this->pipes = [];

        if (is_resource($this->process)) {
            proc_terminate($this->process);
            proc_close($this->process);
        }
        $this->process = null;
    }

    public function isOpen(): bool
    {
        if (!is_resource($this->process)) {
            return false;
        }
        $status = proc_get_status($this->process);
        return (bool)($status['running'] ?? false);
    }

    public function getDiagnostics(): array
    {
        return [
            'transport' => 'stdio',
            'command' => $this->command,
            'arguments' => $this->arguments,
            'is_open' => $this->isOpen(),
        ];
    }

    public function __destruct()
    {
        $this->close();
    }
}
