<?php

namespace Tests\Unit\Services;

use App\Services\ActivityPubUrlGuard;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ActivityPubUrlGuardTest extends TestCase
{
    #[DataProvider('unsafeUrlDataProvider')]
    public function test_assert_safe_rejects_unsafe_urls(string $url): void
    {
        $this->expectException(RuntimeException::class);

        new ActivityPubUrlGuard()->assertSafe($url);
    }

    public static function unsafeUrlDataProvider(): array
    {
        return [
            'link-local metadata endpoint' => ['https://169.254.169.254/latest/meta-data/'],
            'loopback via http scheme' => ['http://127.0.0.1/'],
            'loopback via https scheme' => ['https://127.0.0.1/'],
            'private class A' => ['https://10.0.0.5/'],
            'private class B' => ['https://172.16.0.5/'],
            'private class C' => ['https://192.168.1.5/'],
            'ipv6 loopback' => ['https://[::1]/'],
            'non-https scheme' => ['gopher://example.com/'],
            'file scheme' => ['file:///etc/passwd'],
            'no host' => ['https:///path'],
        ];
    }

    public function test_assert_safe_allows_public_https_url(): void
    {
        $this->expectNotToPerformAssertions();

        new ActivityPubUrlGuard()->assertSafe('https://1.1.1.1/');
    }
}
