<?php

declare(strict_types=1);

namespace UniversalAgentOS\Mcp;

use UniversalAgentOS\Exceptions\McpException;

/**
 * Remote MCP transport connecting over HTTP/HTTPS with streaming and OAuth/Bearer header support.
 * Implements strict fail-closed SSRF protection, IP pinning, and redirect revalidation.
 */
class StreamableHttpTransport implements McpTransportInterface
{
    private const BLOCKED_PORTS = [
        0, 20, 21, 22, 23, 25, 53, 69, 110, 111, 119, 123, 135, 137, 138, 139,
        143, 161, 389, 445, 465, 514, 515, 587, 636, 873, 993, 995, 1080, 1433,
        1521, 2049, 2375, 2376, 3306, 3389, 5432, 5900, 6379, 9200, 11211, 27017,
    ];

    private static ?bool $curlAvailableForTesting = null;
    /** @var callable|null */
    private static $dnsResolverForTesting = null;

    private string $endpointUrl;
    /** @var array<string, string> */
    private array $headers;
    private int $timeoutSeconds;
    private bool $allowPrivate;
    private ?string $pinnedIp = null;
    private bool $open = false;
    /** @var array<int, McpMessage> */
    private array $incomingQueue = [];

    public function __construct(string $endpointUrl, array $headers = [], int $timeoutSeconds = 15, bool $allowPrivate = false)
    {
        if (!self::isCurlAvailable()) {
            throw new McpException(
                "Remote Streamable HTTP MCP transport requires 'ext-curl' for destination IP pinning and SSRF protection. The curl extension is not available."
            );
        }
        $this->pinnedIp = $this->assertValidEndpoint($endpointUrl, $allowPrivate);
        $this->endpointUrl = $endpointUrl;
        $this->headers = $headers;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->allowPrivate = $allowPrivate;
        $this->open = true;
    }

    public static function setCurlAvailableForTesting(?bool $available): void { self::$curlAvailableForTesting = $available; }
    public static function setDnsResolverForTesting(?callable $resolver): void { self::$dnsResolverForTesting = $resolver; }
    public static function isCurlAvailable(): bool { return self::$curlAvailableForTesting ?? extension_loaded('curl'); }

    public function send(McpMessage $message): void
    {
        if (!$this->isOpen()) {
            throw new McpException("Cannot send message on closed HTTP transport.");
        }
        $payload = $message->toJson();
        $targetUrl = $this->endpointUrl;
        $pinnedIp = $this->pinnedIp;
        $hops = 0;

        while ($hops < 3) {
            $resp = $this->executeHttpRequest($targetUrl, $payload, $pinnedIp);
            if ($resp['status'] >= 300 && $resp['status'] < 400 && isset($resp['location'])) {
                $hops++;
                $targetUrl = $this->resolveRedirectUrl($targetUrl, $resp['location']);
                $pinnedIp = $this->assertValidEndpoint($targetUrl, $this->allowPrivate);
                continue;
            }
            if ($resp['status'] >= 400) {
                throw new McpException("Remote MCP server returned HTTP error {$resp['status']}: {$resp['body']}");
            }
            if (trim($resp['body']) !== '') {
                $this->incomingQueue[] = McpMessage::fromJson($resp['body']);
            }
            return;
        }
        throw new McpException("HTTP transport exceeded maximum redirect hops (3) targeting '{$targetUrl}'");
    }

    public function receive(int $timeoutSeconds = 10): ?McpMessage
    {
        return !empty($this->incomingQueue) ? array_shift($this->incomingQueue) : null;
    }

    public function close(): void
    {
        $this->open = false;
        $this->incomingQueue = [];
    }

    public function isOpen(): bool
    {
        return $this->open;
    }

    public function getDiagnostics(): array
    {
        return [
            'transport' => 'streamable_http',
            'endpoint' => $this->endpointUrl,
            'pinned_ip' => $this->pinnedIp,
            'timeout_seconds' => $this->timeoutSeconds,
            'is_open' => $this->isOpen(),
        ];
    }

    public function resolveRedirectUrl(string $baseUrl, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) {
            return $location;
        }
        $p = parse_url($baseUrl);
        $scheme = $p['scheme'] ?? 'http';
        $host = $p['host'] ?? '';
        $port = isset($p['port']) ? ":{$p['port']}" : '';
        if (str_starts_with($location, '/')) {
            return "{$scheme}://{$host}{$port}{$location}";
        }
        $path = dirname($p['path'] ?? '/');
        $path = ($path === '/' || $path === '\\') ? '/' : rtrim($path, '/') . '/';
        return "{$scheme}://{$host}{$port}{$path}{$location}";
    }

    public function assertValidEndpoint(string $url, bool $allowPrivate): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new McpException("Invalid remote MCP endpoint URL: '{$url}'");
        }
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            throw new McpException("Malformed MCP URL: '{$url}'");
        }
        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            throw new McpException("Unsupported MCP URL scheme '{$parts['scheme']}'. Only http and https permitted.");
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new McpException("SSRF blocked: Embedded credentials in URL prohibited: '{$url}'");
        }
        $port = $parts['port'] ?? (strtolower($parts['scheme']) === 'https' ? 443 : 80);
        if (in_array($port, self::BLOCKED_PORTS, true)) {
            throw new McpException("SSRF blocked: Prohibited service port {$port} targeting '{$url}'");
        }
        $host = trim($parts['host']);
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $host = substr($host, 1, -1);
        }
        if ($allowPrivate) {
            return filter_var($host, FILTER_VALIDATE_IP) ? $host : null;
        }
        return $this->assertNotPrivateOrRestrictedHost($host);
    }

    private function assertNotPrivateOrRestrictedHost(string $host): string
    {
        if ($host === 'localhost' || ctype_digit($host) || preg_match('/^(0[0-9]|0x[0-9a-fA-F])/i', $host)) {
            throw new McpException("SSRF blocked: Restricted or ambiguous host identifier: '{$host}'");
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isRestrictedIp($host)) {
                throw new McpException("SSRF blocked: Destination targets restricted IP: {$host}");
            }
            return $host;
        }
        $resolved = [];
        if (self::$dnsResolverForTesting !== null) {
            $records = (self::$dnsResolverForTesting)($host);
        } else {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        }
        if (is_array($records)) {
            foreach ($records as $r) {
                if (isset($r['ip'])) { $resolved[] = $r['ip']; }
                if (isset($r['ipv6'])) { $resolved[] = $r['ipv6']; }
            }
        }
        if (empty($resolved) && self::$dnsResolverForTesting === null) {
            $ipv4s = @gethostbynamel($host);
            if (is_array($ipv4s)) { $resolved = $ipv4s; }
        }
        if (empty($resolved)) {
            throw new McpException("SSRF blocked: Host '{$host}' could not be resolved to any IP address.");
        }
        foreach ($resolved as $rip) {
            if ($this->isRestrictedIp($rip)) {
                throw new McpException("SSRF blocked: Host '{$host}' resolves to restricted IP: {$rip}");
            }
        }
        return $resolved[0];
    }

    public function isRestrictedIp(string $ip): bool
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            }
            $l = ip2long($ip);
            if ($l === false) { return true; }
            $ranges = [
                [0x00000000, 0xFF000000], [0x0A000000, 0xFF000000], [0x7F000000, 0xFF000000],
                [0xA9FE0000, 0xFFFF0000], [0xAC100000, 0xFFF00000], [0xC0A80000, 0xFFFF0000],
                [0x64400000, 0xFFC00000], [0xE0000000, 0xF0000000], [0xF0000000, 0xF0000000],
            ];
            foreach ($ranges as [$net, $mask]) {
                if (($l & $mask) === $net) { return true; }
            }
            return false;
        }
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $bin = inet_pton($ip);
            if ($bin === false) { return true; }
            if (str_starts_with($bin, "\0\0\0\0\0\0\0\0\0\0\xFF\xFF")) {
                return $this->isRestrictedIp((string)inet_ntop(substr($bin, 12, 4)));
            }
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            }
            if ($bin === "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\1" || $bin === str_repeat("\0", 16)) { return true; }
            $b0 = ord($bin[0]);
            return ($b0 & 0xFE) === 0xFC || ($b0 === 0xFE && (ord($bin[1]) & 0xC0) === 0x80) || $b0 === 0xFF;
        }
        return true;
    }

    /**
     * @return array{status: int, body: string, location?: string}
     */
    private function executeHttpRequest(string $url, string $payload, ?string $pinnedIp): array
    {
        if (!self::isCurlAvailable()) {
            throw new McpException("Remote Streamable HTTP MCP transport requires 'ext-curl' for destination IP pinning.");
        }
        $hdrs = array_merge(['Content-Type: application/json', 'Accept: application/json, text/event-stream'], $this->formatHeaders($this->headers));
        return $this->executeCurlRequest($url, $payload, $hdrs, $pinnedIp);
    }

    /**
     * @param array<int, string> $headers
     * @return array{status: int, body: string, location?: string}
     */
    private function executeCurlRequest(string $url, string $payload, array $headers, ?string $pinnedIp): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $payload, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true, CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(5, $this->timeoutSeconds), CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true, CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        if ($pinnedIp !== null) {
            $p = parse_url($url);
            $h = $p['host'] ?? '';
            $pt = $p['port'] ?? (($p['scheme'] ?? '') === 'https' ? 443 : 80);
            if ($h !== '') {
                $formattedIp = str_contains($pinnedIp, ':') && !str_starts_with($pinnedIp, '[') ? "[{$pinnedIp}]" : $pinnedIp;
                curl_setopt($ch, CURLOPT_RESOLVE, ["{$h}:{$pt}:{$formattedIp}"]);
            }
        }
        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new McpException("HTTP transport POST failed to '{$url}': {$err}");
        }
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $hdr = substr((string)$raw, 0, $hdrSize);
        $body = substr((string)$raw, $hdrSize);
        curl_close($ch);
        $res = ['status' => $status, 'body' => $body];
        if (preg_match('/^Location:\s*(\S+)/im', $hdr, $m)) { $res['location'] = trim($m[1]); }
        return $res;
    }

    /**
     * @param array<string, string> $headers
     * @return array<int, string>
     */
    private function formatHeaders(array $headers): array
    {
        $out = [];
        foreach ($headers as $k => $v) { $out[] = "{$k}: {$v}"; }
        return $out;
    }
}
