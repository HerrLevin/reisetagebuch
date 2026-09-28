<?php

namespace Tests\Repository;

use App\Jobs\LookupLocationTimezoneJob;
use App\Models\Location;
use App\Repositories\LocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use Tests\TestCase;

class LocationRepositoryTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LocationRepository::ensureTimezone (async dispatch, never calls out inline)
    // ──────────────────────────────────────────────────────────────────────────
    public function test_ensure_timezone_dispatches_a_lookup_job_when_timezone_is_missing(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Bus::fake();
        Http::fake();

        app(LocationRepository::class)->ensureTimezone($location);

        Bus::assertDispatched(LookupLocationTimezoneJob::class, function (LookupLocationTimezoneJob $job) use ($location) {
            return $this->jobProperty($job, 'locationId') === $location->id;
        });
        Http::assertNothingSent();
    }

    public function test_ensure_timezone_does_not_dispatch_when_a_timezone_is_already_known(): void
    {
        $location = Location::factory()->create(['timezone' => 'Europe/Berlin']);

        Bus::fake();

        app(LocationRepository::class)->ensureTimezone($location);

        Bus::assertNotDispatched(LookupLocationTimezoneJob::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LocationRepository::resolveTimezoneNow (the synchronous lookup used by the job)
    // ──────────────────────────────────────────────────────────────────────────
    public function test_resolve_timezone_now_resolves_and_saves_a_missing_timezone(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(['timeZone' => 'Europe/Berlin']),
        ]);

        $updated = app(LocationRepository::class)->resolveTimezoneNow($location);

        $this->assertTrue($updated);
        $this->assertSame('Europe/Berlin', $location->fresh()->timezone);
    }

    public function test_resolve_timezone_now_does_nothing_when_a_timezone_is_already_known(): void
    {
        $location = Location::factory()->create(['timezone' => 'Europe/Berlin']);

        Http::fake([
            '*' => Http::response(['timeZone' => 'Asia/Tokyo']),
        ]);

        $updated = app(LocationRepository::class)->resolveTimezoneNow($location);

        $this->assertFalse($updated);
        Http::assertNothingSent();
        $this->assertSame('Europe/Berlin', $location->fresh()->timezone);
    }

    public function test_resolve_timezone_now_leaves_timezone_null_when_lookup_fails(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $updated = app(LocationRepository::class)->resolveTimezoneNow($location);

        $this->assertFalse($updated);
        $this->assertNull($location->fresh()->timezone);
    }
}
