<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Exceptions\McpException;
use UniversalAgentOS\Mcp\StreamableHttpTransport;
use UniversalAgentOS\Tests\Assert;

class StreamableHttpTransportSsrfTest
{
    public static function run(): void
    {
        // 1. IPv6 loopback and unspecified addresses
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[::1]/mcp');
        }, "IPv6 loopback [::1] blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[::]/mcp');
        }, "IPv6 unspecified [::] blocked fail-closed");

        // 2. IPv6 link-local and unique-local
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[fe80::1]/mcp');
        }, "IPv6 link-local [fe80::1] blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[fc00::1]/mcp');
        }, "IPv6 unique-local [fc00::1] blocked fail-closed");

        // 3. IPv4-mapped IPv6 prohibited destinations
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[::ffff:127.0.0.1]/mcp');
        }, "IPv4-mapped IPv6 loopback blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[::ffff:169.254.169.254]/mcp');
        }, "IPv4-mapped IPv6 metadata blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://[::ffff:10.0.0.1]/mcp');
        }, "IPv4-mapped IPv6 RFC1918 private blocked fail-closed");

        // 4. Ambiguous numeric/encoded host notations
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://2130706433/mcp');
        }, "Dword integer IP representation blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://0177.0.0.1/mcp');
        }, "Octal IP representation blocked fail-closed");

        // 5. Embedded credentials (userinfo) in authority
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://user:secret@example.com/mcp');
        }, "Userinfo credentials in URL blocked fail-closed");

        // 6. Prohibited service ports
        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://1.1.1.1:22/mcp');
        }, "Prohibited SSH port 22 blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://1.1.1.1:6379/mcp');
        }, "Prohibited Redis port 6379 blocked fail-closed");

        Assert::assertThrows(McpException::class, function () {
            new StreamableHttpTransport('http://1.1.1.1:25/mcp');
        }, "Prohibited SMTP port 25 blocked fail-closed");

        // 7. AllowPrivate bypass flag behavior
        $privTransport = new StreamableHttpTransport('http://127.0.0.1:8080/mcp', [], 10, true);
        Assert::assertEquals('streamable_http', $privTransport->getDiagnostics()['transport'], "allowPrivate permits private IP for local testing");
        $privTransport->close();
    }
}
