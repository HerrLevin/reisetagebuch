<?php

namespace Tests\Unit\Services;

use App\Services\TimeZoneLookupService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TimeZoneLookupServiceTest extends TestCase
{
    public function test_returns_the_timezone_from_a_successful_response(): void
    {
        Http::fake([
            '*' => Http::response(['tz' => 'Europe/Berlin'], 200),
        ]);

        $service = new TimeZoneLookupService;

        $this->assertSame('Europe/Berlin', $service->lookup(52.52, 13.405));
    }

    public function test_returns_null_when_the_request_fails(): void
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $service = new TimeZoneLookupService;

        $this->assertNull($service->lookup(52.52, 13.405));
    }

    public function test_returns_null_when_the_connection_throws(): void
    {
        Http::fake(function () {
            throw new ConnectionException('timed out');
        });

        $service = new TimeZoneLookupService;

        $this->assertNull($service->lookup(52.52, 13.405));
    }

    public function test_returns_null_for_an_unrecognised_timezone_identifier(): void
    {
        Http::fake([
            '*' => Http::response(['tz' => 'Not/ARealZone'], 200),
        ]);

        $service = new TimeZoneLookupService;

        $this->assertNull($service->lookup(52.52, 13.405));
    }

    public function test_sends_the_coordinate_in_the_request_path(): void
    {
        Http::fake([
            '*' => Http::response(['tz' => 'Europe/Berlin'], 200),
        ]);

        (new TimeZoneLookupService)->lookup(52.52, 13.405);

        Http::assertSent(function ($request) {
            return str_ends_with($request->url(), '/52.52/13.405');
        });
    }
}
