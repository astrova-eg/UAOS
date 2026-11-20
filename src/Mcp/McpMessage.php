<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

/**
 * Value object representing a JSON-RPC 2.0 message conforming to the Model Context Protocol specification.
 */
readonly class McpMessage
{
    /**
     * @param string $jsonrpc Must be '2.0'
     * @param string|int|null $id Request/Response identifier (null for notifications)
     * @param string|null $method Method name for requests/notifications
     * @param array<string, mixed>|null $params Method parameters
     * @param mixed $result Result payload for successful responses
     * @param array<string, mixed>|null $error Error object for failed responses
     */
    public function __construct(
        public string $jsonrpc = '2.0',
        public string|int|null $id = null,
        public ?string $method = null,
        public ?array $params = null,
        public mixed $result = null,
        public ?array $error = null
    ) {
    }

    public static function request(string|int $id, string $method, ?array $params = null): self
    {
        return new self(jsonrpc: '2.0', id: $id, method: $method, params: $params);
    }

    public static function response(string|int $id, mixed $result): self
    {
        return new self(jsonrpc: '2.0', id: $id, result: $result);
    }

    public static function error(string|int|null $id, int $code, string $message, mixed $data = null): self
    {
        return new self(
            jsonrpc: '2.0',
            id: $id,
            error: [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ]
        );
    }

    public static function fromJson(string $rawJson): self
    {
        $data = json_decode($rawJson, true);
        if (!is_array($data) || ($data['jsonrpc'] ?? '') !== '2.0') {
            throw new \InvalidArgumentException('Malformed JSON-RPC 2.0 message payload.');
        }

        return new self(
            jsonrpc: '2.0',
            id: $data['id'] ?? null,
            method: $data['method'] ?? null,
            params: $data['params'] ?? null,
            result: $data['result'] ?? null,
            error: $data['error'] ?? null
        );
    }

    public function toJson(): string
    {
        $payload = ['jsonrpc' => '2.0'];
        if ($this->id !== null) {
            $payload['id'] = $this->id;
        }
        if ($this->method !== null) {
            $payload['method'] = $this->method;
        }
        if ($this->params !== null) {
            $payload['params'] = $this->params;
        }
        if ($this->result !== null) {
            $payload['result'] = $this->result;
        }
        if ($this->error !== null) {
            $payload['error'] = $this->error;
        }

        return (string)json_encode($payload, JSON_UNESCAPED_SLASHES);
    }
}
