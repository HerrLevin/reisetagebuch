<?php

namespace Tests\Unit\Resources;

use App\Enums\TransportMode;
use App\Http\Resources\LocationDto;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Http\Resources\StopDto;
use App\Http\Resources\TripDto;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class BasePostTest extends TestCase
{
    #[DataProvider('maliciousBodyDataProvider')]
    public function test_get_html_body_escapes_html_in_body(string $body, string $expectedHtml): void
    {
        $post = new BasePost;
        $post->body = $body;

        $this->assertSame($expectedHtml, $post->getHtmlBody());
    }

    public static function maliciousBodyDataProvider(): array
    {
        return [
            'script tag' => [
                '<script>alert(1)</script>',
                '&lt;script&gt;alert(1)&lt;/script&gt;',
            ],
            'onerror attribute' => [
                '<img src=x onerror=alert(1)>',
                '&lt;img src=x onerror=alert(1)&gt;',
            ],
            'plain text with special chars' => [
                '5 < 10 & 10 > 5',
                '5 &lt; 10 &amp; 10 &gt; 5',
            ],
        ];
    }

    public function test_get_html_body_returns_null_for_null_body(): void
    {
        $post = new BasePost;
        $post->body = null;

        $this->assertNull($post->getHtmlBody());
    }

    public function test_location_post_html_body_escapes_but_does_not_double_escape(): void
    {
        $post = $this->makeLocationPost('5 < 10');

        $html = $post->getHtmlBody();

        $this->assertStringContainsString('5 &lt; 10', $html);
        $this->assertStringNotContainsString('&amp;lt;', $html);
    }

    public function test_transport_post_html_body_escapes_but_does_not_double_escape(): void
    {
        $post = $this->makeTransportPost('5 < 10');

        $html = $post->getHtmlBody();

        $this->assertStringContainsString('5 &lt; 10', $html);
        $this->assertStringNotContainsString('&amp;lt;', $html);
    }

    private function makeLocationPost(string $body): LocationPost
    {
        $post = new ReflectionClass(LocationPost::class)->newInstanceWithoutConstructor();
        $post->body = $body;

        $location = new ReflectionClass(LocationDto::class)->newInstanceWithoutConstructor();
        $location->name = 'Berlin';
        $post->location = $location;

        return $post;
    }

    private function makeTransportPost(string $body): TransportPost
    {
        $post = new ReflectionClass(TransportPost::class)->newInstanceWithoutConstructor();
        $post->body = $body;
        $post->distance = 1000;
        $post->duration = 600;

        $originStop = new ReflectionClass(StopDto::class)->newInstanceWithoutConstructor();
        $originStop->name = 'Origin';
        $post->originStop = $originStop;

        $destinationStop = new ReflectionClass(StopDto::class)->newInstanceWithoutConstructor();
        $destinationStop->name = 'Destination';
        $post->destinationStop = $destinationStop;

        $trip = new ReflectionClass(TripDto::class)->newInstanceWithoutConstructor();
        $trip->mode = TransportMode::TRAM;
        $trip->lineName = 'M10';
        $post->trip = $trip;

        return $post;
    }
}
