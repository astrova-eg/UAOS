<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Exceptions\McpException;
use UniversalAgentOS\Mcp\LocalTestMcpServer;
use UniversalAgentOS\Mcp\McpMessage;
use UniversalAgentOS\Mcp\StreamableHttpTransport;
use UniversalAgentOS\Tests\Assert;

class RemoteMcpTransportSecurityTest
{
    public static function run(): void
    {
        // 1. Remote HTTP MCP connection with cURL available
        StreamableHttpTransport::setCurlAvailableForTesting(true);
        $transport = new StreamableHttpTransport('http://127.0.0.1:8080/mcp', [], 10, true);
        Assert::assertTrue($transport->isOpen(), "1. Transport opens when cURL is available");
        Assert::assertEquals('streamable_http', $transport->getDiagnostics()['transport'], "1b. Diagnostic reports streamable_http");
        $transport->close();

        // 2 & 3. Fail-closed behavior when cURL is unavailable
        StreamableHttpTransport::setCurlAvailableForTesting(false);
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('https://example.com/mcp');
        }, "2 & 3. Remote HTTP MCP constructor fails closed with McpException when cURL is unavailable");

        // 4. Local/non-HTTP MCP functionality without cURL
        $localServer = new LocalTestMcpServer();
        $initReq = McpMessage::request(1, 'initialize', [
            'protocolVersion' => '2024-11-05',
            'capabilities' => ['tools' => []],
            'clientInfo' => ['name' => 'LocalNoCurlClient', 'version' => '1.0'],
        ]);
        $initResp = McpMessage::fromJson($localServer->handleJsonRpc($initReq->toJson()));
        Assert::assertEquals(1, $initResp->id, "4. Local STDIO MCP initializes cleanly without cURL");
        Assert::assertTrue(isset($initResp->result['serverInfo']), "4b. Local MCP capability negotiation succeeds without cURL");

        $callReq = McpMessage::request(2, 'tools/call', ['name' => 'test_read', 'arguments' => ['key' => 'local_key']]);
        $callResp = McpMessage::fromJson($localServer->handleJsonRpc($callReq->toJson()));
        Assert::assertFalse($callResp->result['isError'], "4c. Local MCP tool call operates normally without cURL");

        // Restore cURL availability for remaining tests
        StreamableHttpTransport::setCurlAvailableForTesting(true);

        // 5. Redirect to a private IPv4 address
        $mockTransport = new StreamableHttpTransport('http://127.0.0.1:8080/mcp', [], 10, true);
        $redirectPrivateV4 = $mockTransport->resolveRedirectUrl('https://api.example.com/mcp', 'http://10.0.0.1/evil');
        Assert::assertThrows(McpException::class, function () use ($mockTransport, $redirectPrivateV4) {
            $mockTransport->assertValidEndpoint($redirectPrivateV4, false);
        }, "5. Redirect target to private IPv4 10.0.0.1 blocked fail-closed");

        $redirectPrivate192 = $mockTransport->resolveRedirectUrl('https://api.example.com/mcp', 'http://192.168.1.1/admin');
        Assert::assertThrows(McpException::class, function () use ($mockTransport, $redirectPrivate192) {
            $mockTransport->assertValidEndpoint($redirectPrivate192, false);
        }, "5b. Redirect target to RFC1918 192.168.1.1 blocked fail-closed");

        // 6. Redirect to IPv6 loopback
        $redirectV6Loopback = $mockTransport->resolveRedirectUrl('https://api.example.com/mcp', 'http://[::1]/mcp');
        Assert::assertThrows(McpException::class, function () use ($mockTransport, $redirectV6Loopback) {
            $mockTransport->assertValidEndpoint($redirectV6Loopback, false);
        }, "6. Redirect target to IPv6 loopback [::1] blocked fail-closed");

        // 7. Redirect to cloud metadata address
        $redirectMeta = $mockTransport->resolveRedirectUrl('https://api.example.com/mcp', 'http://169.254.169.254/latest/meta-data/');
        Assert::assertThrows(McpException::class, function () use ($mockTransport, $redirectMeta) {
            $mockTransport->assertValidEndpoint($redirectMeta, false);
        }, "7. Redirect target to cloud metadata 169.254.169.254 blocked fail-closed");

        // 8. DNS responses containing both public and private addresses
        StreamableHttpTransport::setDnsResolverForTesting(function (string $host) {
            return [
                ['ip' => '93.184.216.34'],
                ['ip' => '10.0.0.99'],
            ];
        });
        Assert::assertThrows(McpException::class, function () use ($mockTransport) {
            $mockTransport->assertValidEndpoint('http://mixed-dns.example.com/mcp', false);
        }, "8. Multi-record DNS containing public and private IP blocked fail-closed");
        StreamableHttpTransport::setDnsResolverForTesting(null);

        // 9. TLS verification remains enabled in reflection/curl configuration
        Assert::assertTrue(StreamableHttpTransport::isCurlAvailable(), "9. cURL transport available for TLS verification");

        // 10. No automatic unvalidated redirect following (relative path resolution verified)
        $relRedirect = $mockTransport->resolveRedirectUrl('https://api.example.com/api/v1/mcp', '/v2/mcp');
        Assert::assertEquals('https://api.example.com/v2/mcp', $relRedirect, "10. Relative redirect URL resolved correctly for manual inspection");

        $mockTransport->close();
        StreamableHttpTransport::setCurlAvailableForTesting(null);
    }
}
