<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Unit;

use UniversalAgentOS\Contracts\McpClientInterface;
use UniversalAgentOS\Exceptions\RetrievalSecurityException;
use UniversalAgentOS\Research\Models\SourceDefinition;
use UniversalAgentOS\Research\Retrieval\HttpRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\McpRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RestApiRetrievalProvider;
use UniversalAgentOS\Research\Retrieval\RetrievalManager;
use UniversalAgentOS\Research\Retrieval\SecureHttpClient;
use UniversalAgentOS\Tests\Assert;
use UniversalCore\Tests\RdeiSchemaFixtures;

require_once __DIR__ . '/../../.agents/tests/RdeiSchemaFixtures.php';

class RetrievalProviderAndOrchestrationTest
{
    public static function run(): void
    {
        self::testHttpRetrievalProvider();
        self::testRestApiRetrievalProvider();
        self::testMcpRetrievalProvider();
        self::testRetrievalManagerGovernance();
    }

    private static function testHttpRetrievalProvider(): void
    {
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $fixture['auth_profile'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $fixture['access_mechanisms'][0]['auth_requirement'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $source = SourceDefinition::fromArray($fixture);

        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url, $headers) {
            return [
                'status_code' => 200,
                'headers' => ['content-type' => 'text/html'],
                'body' => '<html><body><h1>Documentation</h1><p>Content</p></body></html>',
            ];
        });

        $provider = new HttpRetrievalProvider(new SecureHttpClient(allowPrivate: true));
        Assert::assertEquals('http_direct', $provider->getName(), "Provider name is http_direct");
        Assert::assertTrue($provider->supports($source), "Supports valid HTTP source");

        $res = $provider->retrieve('https://example.com/docs', $source);
        Assert::assertEquals(200, $res['status_code'], "Retrieval returned 200");
        Assert::assertTrue(str_contains($res['content'], '<h1>Documentation</h1>'), "Returned HTML content");
        Assert::assertEquals(hash('sha256', $res['content']), $res['hash'], "Computed correct SHA-256 hash");

        // Auth requirement with missing env var fails closed
        $authFixture = $fixture;
        $authFixture['auth_profile']['requires_auth'] = true;
        $authFixture['auth_profile']['auth_type'] = 'bearer_token';
        $authFixture['auth_profile']['env_var_key'] = 'NONEXISTENT_TEST_TOKEN_XYZ';
        $authFixture['access_mechanisms'][0]['auth_requirement'] = [
            'requires_auth' => true,
            'auth_type' => 'bearer_token',
            'env_var_key' => 'NONEXISTENT_TEST_TOKEN_XYZ',
        ];
        $authSource = SourceDefinition::fromArray($authFixture);

        putenv('NONEXISTENT_TEST_TOKEN_XYZ'); // ensure unset
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($provider, $authSource) {
            $provider->retrieve('https://example.com/docs', $authSource);
        }, "Missing required environment credential fails closed");

        // Test with present credential
        putenv('NONEXISTENT_TEST_TOKEN_XYZ=secret_token_123');
        $authRes = $provider->retrieve('https://example.com/docs', $authSource);
        Assert::assertEquals(200, $authRes['status_code'], "Retrieval succeeded with token");
        putenv('NONEXISTENT_TEST_TOKEN_XYZ'); // clean up

        SecureHttpClient::setMockHandlerForTesting(null);
    }

    private static function testRestApiRetrievalProvider(): void
    {
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $fixture['auth_profile'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $fixture['access_mechanisms'][0]['auth_requirement'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $fixture['access_mechanisms'][0]['mechanism_type'] = 'rest_api';
        $fixture['access_mechanisms'][0]['endpoint_url'] = 'https://packagist.org/packages.json';
        $source = SourceDefinition::fromArray($fixture);

        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url) {
            return [
                'status_code' => 200,
                'headers' => ['content-type' => 'application/json'],
                'body' => '{"packages": {"vendor/package": {"dev-main": {}}}}',
            ];
        });

        $provider = new RestApiRetrievalProvider(new SecureHttpClient(allowPrivate: true));
        Assert::assertEquals('rest_api', $provider->getName(), "Provider name is rest_api");
        Assert::assertTrue($provider->supports($source), "Supports rest_api source");

        $res = $provider->retrieve('https://packagist.org/packages.json', $source);
        Assert::assertEquals(200, $res['status_code'], "Status 200");
        Assert::assertEquals('application/json', $res['mime_type'], "JSON mime type");

        // Malformed JSON response fails closed
        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url) {
            return [
                'status_code' => 200,
                'headers' => ['content-type' => 'application/json'],
                'body' => 'Invalid Non-JSON response',
            ];
        });

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($provider, $source) {
            $provider->retrieve('https://packagist.org/packages.json', $source);
        }, "Malformed REST API JSON response throws RetrievalSecurityException");

        SecureHttpClient::setMockHandlerForTesting(null);
    }

    private static function testMcpRetrievalProvider(): void
    {
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $fixture['access_mechanisms'][0]['mechanism_type'] = 'mcp_server';
        $fixture['access_mechanisms'][0]['mcp_server_id'] = 'mcp-fetch-server';
        $source = SourceDefinition::fromArray($fixture);

        $mockClient = new class implements McpClientInterface {
            public function connect(): array { return ['status' => 'connected']; }
            public function discoverTools(): array { return []; }
            public function callTool(string $toolName, array $arguments = []): array {
                return ['content' => [['type' => 'text', 'text' => 'MCP fetched documentation']]];
            }
            public function disconnect(): void {}
            public function isConnected(): bool { return true; }
            public function getDiagnostics(): array { return []; }
        };

        $provider = new McpRetrievalProvider(['mcp-fetch-server' => $mockClient]);
        Assert::assertEquals('mcp_server', $provider->getName(), "Provider name is mcp_server");
        Assert::assertTrue($provider->supports($source), "Supports mcp_server source");

        $res = $provider->retrieve('https://example.com/spec', $source);
        Assert::assertEquals('MCP fetched documentation', $res['content'], "Retrieved content from MCP tool");
        Assert::assertEquals(200, $res['status_code'], "Status 200");
    }

    private static function testRetrievalManagerGovernance(): void
    {
        $fixture = RdeiSchemaFixtures::getValidSourceDef();
        $fixture['auth_profile'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $fixture['access_mechanisms'][0]['auth_requirement'] = ['requires_auth' => false, 'auth_type' => 'none'];
        $fixture['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', time() - 3600);
        $fixture['access_mechanisms'][0]['verified_at'] = $fixture['last_verified_at'];
        $fixture['access_mechanisms'][0]['verification_status'] = 'fully_tested';
        $fixture['verification_profile']['connectivity_tested'] = true;
        $fixture['verification_profile']['endpoint_verified'] = true;
        $source = SourceDefinition::fromArray($fixture);

        SecureHttpClient::setMockHandlerForTesting(static function () {
            return [
                'status_code' => 200,
                'headers' => ['content-type' => 'text/plain'],
                'body' => 'Trusted technical knowledge base.',
            ];
        });

        $httpProvider = new HttpRetrievalProvider(new SecureHttpClient(allowPrivate: true));
        $manager = new RetrievalManager([$httpProvider]);

        // 1. Eligible source succeeds
        $res = $manager->retrieve('https://example.com/docs', $source);
        Assert::assertEquals('Trusted technical knowledge base.', $res['content'], "Retrieval succeeded");

        // 2. Ineligible / Revoked or Deprecated source fails closed
        $depFixture = $fixture;
        $depFixture['status'] = SourceDefinition::STATUS_DEPRECATED;
        $depSource = SourceDefinition::fromArray($depFixture);

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($manager, $depSource) {
            $manager->retrieve('https://example.com/docs', $depSource);
        }, "Deprecated source blocked by retrieval eligibility governance");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($manager, $source) {
            $manager->retrieve('https://example.com/docs', $source, ['lifecycle_state' => 'revoked']);
        }, "Revoked lifecycle state blocked by retrieval eligibility governance");

        // 3. Expired security source fails closed
        $expiredSecurity = $fixture;
        $expiredSecurity['domains'] = ['C'];
        $expiredSecurity['subdomains'] = ['C.01'];
        $expiredSecurity['last_verified_at'] = gmdate('Y-m-d\TH:i:s\Z', time() - (48 * 3600)); // 48h expired
        $expiredSecurity['access_mechanisms'][0]['verified_at'] = $expiredSecurity['last_verified_at'];
        $expSecSource = SourceDefinition::fromArray($expiredSecurity);

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($manager, $expSecSource) {
            $manager->retrieve('https://example.com/docs', $expSecSource);
        }, "Expired security source blocked by retrieval eligibility governance");

        SecureHttpClient::setMockHandlerForTesting(null);
    }
}
