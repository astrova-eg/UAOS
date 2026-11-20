<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Retrieval;

use UniversalAgentOS\Exceptions\RetrievalSecurityException;
use UniversalAgentOS\Research\Contracts\RetrievalProviderInterface;
use UniversalAgentOS\Research\Models\SourceDefinition;

/**
 * Structured REST API retrieval provider for package registries, vulnerability databases, and academic indexes.
 */
class RestApiRetrievalProvider implements RetrievalProviderInterface
{
    public const PROVIDER_NAME = 'rest_api';

    public function __construct(
        private ?SecureHttpClient $client = null
    ) {
        $this->client = $client ?? new SecureHttpClient();
    }

    public function getName(): string
    {
        return self::PROVIDER_NAME;
    }

    public function supports(SourceDefinition $source): bool
    {
        foreach ($source->accessMechanisms as $mech) {
            if (($mech['mechanism_type'] ?? '') === self::PROVIDER_NAME && ($mech['is_active'] ?? false)) {
                return true;
            }
        }
        return false;
    }

    public function retrieve(string $target, SourceDefinition $source, array $options = []): array
    {
        $headers = (array)($options['headers'] ?? []);
        $headers['User-Agent'] = $headers['User-Agent'] ?? 'UniversalAgentOS-RDEI/1.0';
        $headers['Accept'] = 'application/json, text/json;q=0.9';

        $mechanism = $this->resolveMechanism($source);
        $headers = array_merge($headers, $this->resolveAuthHeaders($source, $mechanism));

        $url = trim($target) !== '' ? $target : ($mechanism['endpoint_url'] ?? $source->canonicalUrl);
        $resp = $this->client->get($url, $headers);

        if ($resp['status_code'] < 200 || $resp['status_code'] >= 400) {
            throw new RetrievalSecurityException("REST API returned error status {$resp['status_code']} for '{$url}'");
        }

        $body = (string)$resp['body'];
        if (trim($body) === '') {
            throw new RetrievalSecurityException("REST API returned empty response for '{$url}'");
        }

        $decoded = json_decode($body, true);
        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new RetrievalSecurityException("REST API returned malformed JSON from '{$url}': " . json_last_error_msg());
        }

        return [
            'content' => $body,
            'mime_type' => 'application/json',
            'headers' => $resp['headers'],
            'status_code' => $resp['status_code'],
            'hash' => hash('sha256', $body),
            'url' => $resp['effective_url'] ?? $url,
            'source_id' => $source->sourceId,
            'retrieved_at' => gmdate('Y-m-d\TH:i:s\Z'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveMechanism(SourceDefinition $source): array
    {
        foreach ($source->accessMechanisms as $mech) {
            if (($mech['mechanism_type'] ?? '') === self::PROVIDER_NAME && ($mech['is_active'] ?? false)) {
                return $mech;
            }
        }
        throw new RetrievalSecurityException("No active 'rest_api' mechanism found in source '{$source->sourceId}'");
    }

    /**
     * @param array<string, mixed> $mechanism
     * @return array<string, string>
     */
    private function resolveAuthHeaders(SourceDefinition $source, array $mechanism): array
    {
        $authReq = $mechanism['auth_requirement'] ?? null;
        $requiresAuth = $authReq['requires_auth'] ?? ($source->authProfile['requires_auth'] ?? false);
        if (!$requiresAuth) {
            return [];
        }

        $authType = $authReq['auth_type'] ?? ($source->authProfile['auth_type'] ?? 'none');
        $envKey = $authReq['env_var_key'] ?? ($source->authProfile['env_var_key'] ?? null);

        if ($authType === 'none' || $authType === 'inherit') {
            return [];
        }

        if ($envKey === null || trim($envKey) === '') {
            throw new RetrievalSecurityException("Source '{$source->sourceId}' mandates authentication but no env_var_key is specified.");
        }

        $token = getenv($envKey);
        if ($token === false || trim($token) === '') {
            throw new RetrievalSecurityException("Source '{$source->sourceId}' requires credentials in environment variable '{$envKey}', which is missing or empty.");
        }

        return match ($authType) {
            'bearer_token' => ['Authorization' => "Bearer {$token}"],
            'api_key_header' => ['X-API-Key' => $token],
            'basic_auth' => ['Authorization' => 'Basic ' . base64_encode($token)],
            default => [],
        };
    }
}
