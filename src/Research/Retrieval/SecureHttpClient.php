<?php

declare(strict_types=1);

namespace UniversalAgentOS\Research\Retrieval;

use UniversalAgentOS\Exceptions\RetrievalSecurityException;

/**
 * Hardened HTTP/HTTPS retrieval client with socket-level IP pinning, SSRF defense,
 * TLS verification, max-size guards, and safe redirect revalidation.
 */
class SecureHttpClient
{
    public const DEFAULT_MAX_BYTES = 2097152; // 2MB
    public const DEFAULT_TIMEOUT_SECONDS = 10;
    public const MAX_REDIRECT_HOPS = 3;

    private const BLOCKED_PORTS = [
        0, 20, 21, 22, 23, 25, 53, 69, 110, 111, 119, 123, 135, 137, 138, 139,
        143, 161, 389, 445, 465, 514, 515, 587, 636, 873, 993, 995, 1080, 1433,
        1521, 2049, 2375, 2376, 3306, 3389, 5432, 5900, 6379, 8080, 9200, 11211, 27017,
    ];

    private static ?bool $curlAvailableForTesting = null;
    /** @var callable|null */
    private static $dnsResolverForTesting = null;
    /** @var callable|null */
    private static $mockHandlerForTesting = null;

    /**
     * @param array<int, int> $allowedPorts Custom allowlisted ports beyond 80 and 443
     */
    public function __construct(
        private int $timeoutSeconds = self::DEFAULT_TIMEOUT_SECONDS,
        private int $maxBytes = self::DEFAULT_MAX_BYTES,
        private bool $allowPrivate = false,
        private array $allowedPorts = [80, 443]
    ) {
        if (!self::isCurlAvailable()) {
            throw new RetrievalSecurityException(
                "SecureHttpClient requires 'ext-curl' for socket-level IP pinning and fail-closed SSRF protection."
            );
        }
    }

    public static function setCurlAvailableForTesting(?bool $avail): void { self::$curlAvailableForTesting = $avail; }
    public static function setDnsResolverForTesting(?callable $resolver): void { self::$dnsResolverForTesting = $resolver; }
    public static function setMockHandlerForTesting(?callable $handler): void { self::$mockHandlerForTesting = $handler; }
    public static function isCurlAvailable(): bool { return self::$curlAvailableForTesting ?? extension_loaded('curl'); }

    /**
     * Executes a secure GET request, safely following redirects with revalidation at each hop.
     *
     * @param array<string, string> $headers
     * @return array{status_code: int, headers: array<string, string>, body: string, effective_url: string, pinned_ip: ?string}
     */
    public function get(string $url, array $headers = []): array
    {
        return $this->request('GET', $url, $headers);
    }

    /**
     * Executes a secure HTTP request with fail-closed redirect revalidation.
     *
     * @param array<string, string> $headers
     * @return array{status_code: int, headers: array<string, string>, body: string, effective_url: string, pinned_ip: ?string}
     */
    public function request(string $method, string $url, array $headers = [], ?string $body = null): array
    {
        $currentUrl = $url;
        $hops = 0;

        while ($hops <= self::MAX_REDIRECT_HOPS) {
            $pinnedIp = $this->assertValidEndpoint($currentUrl, $this->allowPrivate);
            $response = $this->executeSingleRequest($method, $currentUrl, $headers, $body, $pinnedIp);

            $status = $response['status_code'];
            if ($status >= 300 && $status < 400 && isset($response['headers']['location'])) {
                if ($hops === self::MAX_REDIRECT_HOPS) {
                    throw new RetrievalSecurityException("HTTP redirect exceeded maximum hops (" . self::MAX_REDIRECT_HOPS . ") targeting '{$currentUrl}'");
                }
                $hops++;
                $currentUrl = $this->resolveRedirectUrl($currentUrl, $response['headers']['location']);
                continue;
            }

            return array_merge($response, ['effective_url' => $currentUrl, 'pinned_ip' => $pinnedIp]);
        }

        throw new RetrievalSecurityException("Maximum redirect limit reached for '{$url}'");
    }

    public function assertValidEndpoint(string $url, bool $allowPrivate): ?string
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new RetrievalSecurityException("Invalid endpoint URL: '{$url}'");
        }
        $parts = parse_url($url);
        if ($parts === false || !isset($parts['scheme'], $parts['host'])) {
            throw new RetrievalSecurityException("Malformed URL: '{$url}'");
        }
        $scheme = strtolower($parts['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new RetrievalSecurityException("Unsupported URL scheme '{$scheme}'. Only http and https permitted.");
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new RetrievalSecurityException("SSRF blocked: Embedded credentials in URL prohibited: '{$url}'");
        }
        $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
        if (in_array($port, self::BLOCKED_PORTS, true) || (!in_array($port, $this->allowedPorts, true) && !$allowPrivate)) {
            throw new RetrievalSecurityException("SSRF blocked: Prohibited service port {$port} targeting '{$url}'");
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
            throw new RetrievalSecurityException("SSRF blocked: Restricted or ambiguous host identifier: '{$host}'");
        }
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if ($this->isRestrictedIp($host)) {
                throw new RetrievalSecurityException("SSRF blocked: Destination targets restricted IP: {$host}");
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
            throw new RetrievalSecurityException("SSRF blocked: Host '{$host}' could not be resolved to any IP address.");
        }
        foreach ($resolved as $rip) {
            if ($this->isRestrictedIp($rip)) {
                throw new RetrievalSecurityException("SSRF blocked: Host '{$host}' resolves to restricted IP: {$rip}");
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

    public function resolveRedirectUrl(string $baseUrl, string $location): string
    {
        if (preg_match('#^https?://#i', $location)) { return $location; }
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

    /**
     * @param array<string, string> $headers
     * @return array{status_code: int, headers: array<string, string>, body: string}
     */
    private function executeSingleRequest(string $method, string $url, array $headers, ?string $body, ?string $pinnedIp): array
    {
        if (self::$mockHandlerForTesting !== null) {
            $res = (self::$mockHandlerForTesting)($method, $url, $headers, $body, $pinnedIp);
            if (isset($res['body']) && strlen((string)$res['body']) > $this->maxBytes) {
                throw new RetrievalSecurityException("Response size exceeds configured ceiling ({$this->maxBytes} bytes)");
            }
            return $res;
        }
        return $this->executeCurl($method, $url, $headers, $body, $pinnedIp);
    }

    /**
     * @param array<string, string> $headers
     * @return array{status_code: int, headers: array<string, string>, body: string}
     */
    private function executeCurl(string $method, string $url, array $headers, ?string $body, ?string $pinnedIp): array
    {
        $ch = curl_init($url);
        $formattedHeaders = [];
        foreach ($headers as $k => $v) { $formattedHeaders[] = "{$k}: {$v}"; }

        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => min(5, $this->timeoutSeconds),
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER => $formattedHeaders,
        ];
        if (strtoupper($method) === 'POST') {
            $opts[CURLOPT_POST] = true;
            if ($body !== null) { $opts[CURLOPT_POSTFIELDS] = $body; }
        } elseif (strtoupper($method) !== 'GET') {
            $opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
        }

        if ($pinnedIp !== null) {
            $p = parse_url($url);
            $h = $p['host'] ?? '';
            $pt = $p['port'] ?? (($p['scheme'] ?? '') === 'https' ? 443 : 80);
            if ($h !== '') {
                $fip = str_contains($pinnedIp, ':') && !str_starts_with($pinnedIp, '[') ? "[{$pinnedIp}]" : $pinnedIp;
                $opts[CURLOPT_RESOLVE] = ["{$h}:{$pt}:{$fip}"];
            }
        }
        curl_setopt_array($ch, $opts);
        $raw = curl_exec($ch);
        if ($raw === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new RetrievalSecurityException("HTTP request failed to '{$url}': {$err}");
        }
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $rawHdr = substr((string)$raw, 0, $hdrSize);
        $bodyStr = substr((string)$raw, $hdrSize);
        curl_close($ch);

        if (strlen($bodyStr) > $this->maxBytes) {
            throw new RetrievalSecurityException("Response size exceeds configured ceiling ({$this->maxBytes} bytes)");
        }

        $parsedHeaders = [];
        foreach (explode("\r\n", $rawHdr) as $line) {
            if (str_contains($line, ':')) {
                [$k, $v] = explode(':', $line, 2);
                $parsedHeaders[strtolower(trim($k))] = trim($v);
            }
        }
        return ['status_code' => $statusCode, 'headers' => $parsedHeaders, 'body' => $bodyStr];
    }
}
