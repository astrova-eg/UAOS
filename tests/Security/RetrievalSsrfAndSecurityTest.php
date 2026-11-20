<?php

declare(strict_types=1);

namespace UniversalAgentOS\Tests\Security;

use UniversalAgentOS\Exceptions\RetrievalSecurityException;
use UniversalAgentOS\Research\Retrieval\SecureHttpClient;
use UniversalAgentOS\Tests\Assert;

class RetrievalSsrfAndSecurityTest
{
    public static function run(): void
    {
        // Set deterministic test DNS resolver for all hostnames in test
        SecureHttpClient::setDnsResolverForTesting(static function (string $host) {
            if ($host === 'rebind-attack.internal.test') {
                return [['ip' => '127.0.0.1']];
            }
            if ($host === 'example.com') {
                return [['ip' => '93.184.216.34']];
            }
            return [];
        });

        $client = new SecureHttpClient(timeoutSeconds: 5, maxBytes: 1024, allowPrivate: false);

        // 1. IPv4 Loopback and Private Addresses
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://127.0.0.1/doc');
        }, "IPv4 loopback 127.0.0.1 blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://10.0.0.1/doc');
        }, "IPv4 RFC1918 10.0.0.1 blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://192.168.1.1/doc');
        }, "IPv4 RFC1918 192.168.1.1 blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://172.16.0.1/doc');
        }, "IPv4 RFC1918 172.16.0.1 blocked fail-closed");

        // 2. Cloud Metadata Service (169.254.169.254)
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://169.254.169.254/latest/meta-data/');
        }, "Link-local cloud metadata IP blocked fail-closed");

        // 3. IPv6 Loopback, Unspecified, Link-Local, and Unique-Local
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[::1]/doc');
        }, "IPv6 loopback [::1] blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[::]/doc');
        }, "IPv6 unspecified [::] blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[fe80::1]/doc');
        }, "IPv6 link-local [fe80::1] blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[fc00::1]/doc');
        }, "IPv6 unique-local [fc00::1] blocked fail-closed");

        // 4. IPv4-Mapped IPv6
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[::ffff:127.0.0.1]/doc');
        }, "IPv4-mapped IPv6 loopback blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://[::ffff:169.254.169.254]/doc');
        }, "IPv4-mapped IPv6 cloud metadata blocked fail-closed");

        // 5. Ambiguous and Encoded Hosts
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://localhost/doc');
        }, "localhost hostname blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://2130706433/doc');
        }, "Dword integer IP notation blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://0177.0.0.1/doc');
        }, "Octal IP notation blocked fail-closed");

        // 6. Embedded Userinfo Credentials
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://admin:secret@example.com/doc');
        }, "Embedded URL credentials blocked fail-closed");

        // 7. Prohibited Ports
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://93.184.216.34:22/doc');
        }, "SSH port 22 blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://93.184.216.34:6379/doc');
        }, "Redis port 6379 blocked fail-closed");

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://93.184.216.34:8080/doc');
        }, "Non-standard port 8080 without allowlist blocked fail-closed");

        // 8. DNS Rebinding / Restricted Resolution
        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('http://rebind-attack.internal.test/doc');
        }, "DNS resolving to loopback IP blocked fail-closed");

        // 9. Redirect Revalidation Security
        // Test redirect to private IP at hop 2
        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url) {
            if ($url === 'https://example.com/start') {
                return [
                    'status_code' => 302,
                    'headers' => ['location' => 'http://169.254.169.254/latest/meta-data/'],
                    'body' => '',
                ];
            }
            return ['status_code' => 200, 'headers' => [], 'body' => 'OK'];
        });

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('https://example.com/start');
        }, "Redirect to link-local metadata blocked at redirect revalidation");

        // 10. Redirect Hop Ceiling
        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url) {
            return [
                'status_code' => 302,
                'headers' => ['location' => 'https://example.com/infinite-redirect'],
                'body' => '',
            ];
        });

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('https://example.com/infinite-redirect');
        }, "Redirect exceeding maximum hops blocked fail-closed");

        // 11. Response Size Limit Ceiling
        SecureHttpClient::setMockHandlerForTesting(static function ($method, $url) {
            return [
                'status_code' => 200,
                'headers' => ['content-type' => 'text/plain'],
                'body' => str_repeat('A', 2048), // 2KB exceeds 1024 bytes maxBytes
            ];
        });

        Assert::assertThrows(RetrievalSecurityException::class, static function () use ($client) {
            $client->get('https://example.com/oversized');
        }, "Response exceeding maxBytes ceiling blocked fail-closed");

        // 12. ext-curl Absence Check
        SecureHttpClient::setCurlAvailableForTesting(false);
        Assert::assertThrows(RetrievalSecurityException::class, static function () {
            new SecureHttpClient();
        }, "SecureHttpClient fails closed when ext-curl is unavailable");
        SecureHttpClient::setCurlAvailableForTesting(null);

        // Clean up testing hooks
        SecureHttpClient::setMockHandlerForTesting(null);
        SecureHttpClient::setDnsResolverForTesting(null);
    }
}
