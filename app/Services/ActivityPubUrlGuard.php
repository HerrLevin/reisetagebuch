<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class ActivityPubUrlGuard
{
    /**
     * Reject URLs that could be used to make the server issue requests to
     * internal/loopback/link-local infrastructure (SSRF), e.g. cloud metadata
     * endpoints reachable via 169.254.169.254.
     *
     * @return string the validated IP the caller must pin its connection to
     *                (via {@see pinOption}), so a later independent DNS
     *                resolution by the HTTP client can't rebind past this check.
     */
    public function assertSafe(string $url): string
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = $parts['host'] ?? null;

        if ($scheme !== 'https') {
            throw new RuntimeException("Rejected non-HTTPS URL: {$url}");
        }

        if (! $host) {
            throw new RuntimeException("Rejected URL with no host: {$url}");
        }

        $ips = $this->resolveIps($host);
        foreach ($ips as $ip) {
            if (! $this->isPublicIp($ip)) {
                throw new RuntimeException("Rejected URL resolving to a non-public address: {$url} ({$ip})");
            }
        }

        return $ips[0];
    }

    /**
     * Guzzle/curl options that pin an outbound request for $url to the IP
     * already validated by {@see assertSafe}, preserving the original
     * Host header/SNI. Merge this into the request's 'curl' option.
     */
    public function pinOption(string $url, string $ip): array
    {
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT) ?? 443;

        return [CURLOPT_RESOLVE => ["{$host}:{$port}:{$ip}"]];
    }

    /**
     * @return string[]
     */
    private function resolveIps(string $host): array
    {
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return [$host];
        }

        $ips = [];

        foreach (@dns_get_record($host, DNS_A) ?: [] as $record) {
            if (isset($record['ip'])) {
                $ips[] = $record['ip'];
            }
        }

        foreach (@dns_get_record($host, DNS_AAAA) ?: [] as $record) {
            if (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }

        if ($ips === []) {
            throw new RuntimeException("Could not resolve host: {$host}");
        }

        return $ips;
    }

    private function isPublicIp(string $ip): bool
    {
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
