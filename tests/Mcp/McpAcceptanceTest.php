<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Mcp;

use UniversalAgentOS\Mcp\LocalTestMcpServer;
use UniversalAgentOS\Mcp\McpClient;
use UniversalAgentOS\Mcp\McpMessage;
use UniversalAgentOS\Mcp\McpServerRegistry;
use UniversalAgentOS\Mcp\StdioTransport;
use UniversalAgentOS\Mcp\StreamableHttpTransport;
use UniversalAgentOS\Runtime\PathResolver;
use UniversalAgentOS\Exceptions\McpException;
use UniversalAgentOS\Tests\Assert;

class McpAcceptanceTest
{
    public static function run(): void
    {
        $testServer = new LocalTestMcpServer();
        $resolver = new PathResolver();

        try {
            // 1. Local STDIO Initialization & Capability Negotiation
            $initReq = McpMessage::request(1, 'initialize', [
                'protocolVersion' => '2024-11-05',
                'capabilities' => ['tools' => []],
                'clientInfo' => ['name' => 'TestClient', 'version' => '1.0'],
            ]);
            $initResp = McpMessage::fromJson($testServer->handleJsonRpc($initReq->toJson()));
            Assert::assertEquals(1, $initResp->id, "1. Local STDIO init response ID matches");
            Assert::assertTrue(isset($initResp->result['serverInfo']), "3. Capability negotiation returned serverInfo");

            // 4. Tool Discovery & 5. Tool Registration
            $toolsReq = McpMessage::request(2, 'tools/list');
            $toolsResp = McpMessage::fromJson($testServer->handleJsonRpc($toolsReq->toJson()));
            $tools = (array)($toolsResp->result['tools'] ?? []);
            Assert::assertTrue(count($tools) >= 5, "4 & 5. Discovered registered tools on test server");

            // 6. Valid Tool Invocation (test_read)
            $callReq = McpMessage::request(3, 'tools/call', [
                'name' => 'test_read',
                'arguments' => ['key' => 'sample_data'],
            ]);
            $callResp = McpMessage::fromJson($testServer->handleJsonRpc($callReq->toJson()));
            Assert::assertFalse($callResp->result['isError'], "6. Valid tool invocation executed without error");
            Assert::assertStringContains('READ_SUCCESS', $callResp->result['content'][0]['text'], "Read tool returned expected text");

            // 7. Invalid Arguments
            $invalidArgReq = McpMessage::request(4, 'tools/call', [
                'name' => 'test_invalid_args',
                'arguments' => ['expected_number' => 'not_a_number'],
            ]);
            $invalidArgResp = McpMessage::fromJson($testServer->handleJsonRpc($invalidArgReq->toJson()));
            Assert::assertTrue($invalidArgResp->result['isError'], "7. Invalid argument was flagged with isError: true");

            // 8, 9, 11. Unauthorized write & Gate 4 Denial
            $unauthReq = McpMessage::request(5, 'tools/call', [
                'name' => 'test_unauthorized_write',
                'arguments' => ['filename' => 'forbidden.txt'],
            ]);
            $unauthResp = McpMessage::fromJson($testServer->handleJsonRpc($unauthReq->toJson()));
            Assert::assertTrue($unauthResp->result['isError'], "8, 9, 11. Unauthorized write rejected with Gate 4 error");
            Assert::assertStringContains('Gate 4 Clearance Denied', $unauthResp->result['content'][0]['text'], "Gate 4 clearance denial verified");

            // 10. Authorized Write
            $authWriteReq = McpMessage::request(6, 'tools/call', [
                'name' => 'test_write',
                'arguments' => ['filename' => 'valid.txt', 'content' => 'hello mcp'],
            ]);
            $authWriteResp = McpMessage::fromJson($testServer->handleJsonRpc($authWriteReq->toJson()));
            Assert::assertFalse($authWriteResp->result['isError'], "10. Authorized write succeeded");

            // 23. Evidence Generation
            $evidenceReq = McpMessage::request(7, 'tools/call', [
                'name' => 'test_evidence',
                'arguments' => ['action' => 'DEPLOY'],
            ]);
            $evidenceResp = McpMessage::fromJson($testServer->handleJsonRpc($evidenceReq->toJson()));
            Assert::assertStringContains('DEPLOY', $evidenceResp->result['content'][0]['text'], "23. Evidence generated with action");

            // 19. Malformed / Untrusted Tool Call
            $malformedReq = '{"jsonrpc": "2.0", "id": 8, "method": "tools/call", "params": {"name": "non_existent"}}';
            $malformedResp = McpMessage::fromJson($testServer->handleJsonRpc($malformedReq));
            Assert::assertTrue($malformedResp->error !== null, "19. Non-existent tool call returned error");

            // 20 & 21. Multiple MCP Server Registration & Enable/Disable
            $registry = new McpServerRegistry($resolver);
            $registry->registerServer('local_test_1', [
                'name' => 'Server 1',
                'transport' => 'stdio',
                'command' => 'php',
                'enabled' => true,
                'allowed_tools' => ['test_read'],
            ]);
            $registry->registerServer('remote_test_2', [
                'name' => 'Server 2',
                'transport' => 'http',
                'url' => 'https://api.example.com/mcp',
                'enabled' => false,
            ]);

            Assert::assertTrue($registry->isToolAllowed('local_test_1', 'test_read'), "20. Tool allowed on Server 1");
            Assert::assertFalse($registry->isToolAllowed('local_test_1', 'test_write'), "20. Tool not on allowlist denied");
            Assert::assertFalse($registry->isToolAllowed('remote_test_2', 'any_tool'), "21. Disabled Server 2 denies tools");

            $registry->enableServer('remote_test_2');
            Assert::assertTrue($registry->all()['remote_test_2']['enabled'], "21. Server 2 enabled successfully");

            // 24. Remote Endpoint Validation
            Assert::assertThrows(McpException::class, function () {
                new StreamableHttpTransport('ftp://invalid-endpoint');
            }, "24. Invalid remote transport scheme rejected");

            // 25. SSRF and Private Network Defenses
            Assert::assertThrows(McpException::class, function () {
                new StreamableHttpTransport('http://169.254.169.254/latest/meta-data');
            }, "25. SSRF to cloud metadata endpoint blocked");
            Assert::assertThrows(McpException::class, function () {
                new StreamableHttpTransport('http://127.0.0.1:8080/internal');
            }, "25. SSRF to local loopback blocked");

            // 2. Remote Streamable HTTP Transport Init Validation
            $httpTransport = new StreamableHttpTransport('https://httpbin.org/status/200');
            Assert::assertEquals('streamable_http', $httpTransport->getDiagnostics()['transport'], "2. Remote Streamable HTTP transport initialized");
            $httpTransport->close();

        } finally {
            $testServer->cleanup();
        }
    }
}
