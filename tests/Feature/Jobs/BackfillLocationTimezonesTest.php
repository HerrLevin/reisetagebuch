<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ActivityPub\PushLocationTimezoneChangeToMastodon;
use App\Jobs\BackfillLocationTimezones;
use App\Jobs\LookupLocationTimezoneJob;
use App\Models\Location;
use App\Repositories\LocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use ReflectionProperty;
use Tests\TestCase;

class BackfillLocationTimezonesTest extends TestCase
{
    use RefreshDatabase;

    private function jobProperty(object $job, string $name): mixed
    {
        return new ReflectionProperty($job, $name)->getValue($job);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LocationRepository::getLocationsWithMissingTimezone
    // ──────────────────────────────────────────────────────────────────────────
    public function test_repository_returns_recent_locations_without_timezone(): void
    {
        $missing = Location::factory()->create(['timezone' => null]);
        Location::factory()->create(['timezone' => 'Europe/Berlin']);

        $result = app(LocationRepository::class)->getLocationsWithMissingTimezone(100);

        $this->assertCount(1, $result);
        $this->assertSame($missing->id, $result->first()->id);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BackfillLocationTimezones (sweep job)
    // ──────────────────────────────────────────────────────────────────────────
    public function test_sweep_dispatches_lookup_job_for_each_recent_location_missing_timezone(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Bus::fake();

        (new BackfillLocationTimezones)->handle(app(LocationRepository::class));

        Bus::assertDispatched(LookupLocationTimezoneJob::class, function (LookupLocationTimezoneJob $job) use ($location) {
            return $this->jobProperty($job, 'locationId') === $location->id;
        });
    }

    public function test_sweep_does_not_dispatch_for_locations_that_already_have_a_timezone(): void
    {
        Location::factory()->create(['timezone' => 'Europe/Berlin']);

        Bus::fake();

        (new BackfillLocationTimezones)->handle(app(LocationRepository::class));

        Bus::assertNotDispatched(LookupLocationTimezoneJob::class);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LookupLocationTimezoneJob
    // ──────────────────────────────────────────────────────────────────────────

    public function test_lookup_job_saves_the_resolved_timezone(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(['timeZone' => 'Europe/Berlin']),
        ]);

        (new LookupLocationTimezoneJob($location->id))->handle(app(LocationRepository::class));

        $this->assertSame('Europe/Berlin', $location->fresh()->timezone);
    }

    public function test_lookup_job_leaves_timezone_null_when_lookup_fails(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        (new LookupLocationTimezoneJob($location->id))->handle(app(LocationRepository::class));

        $this->assertNull($location->fresh()->timezone);
    }

    public function test_lookup_job_does_nothing_when_location_already_has_a_timezone(): void
    {
        $location = Location::factory()->create(['timezone' => 'Europe/Berlin']);

        Http::fake([
            '*' => Http::response(['timeZone' => 'Asia/Tokyo']),
        ]);

        (new LookupLocationTimezoneJob($location->id))->handle(app(LocationRepository::class));

        Http::assertNothingSent();
        $this->assertSame('Europe/Berlin', $location->fresh()->timezone);
    }

    public function test_lookup_job_does_nothing_for_missing_location(): void
    {
        Http::fake();

        (new LookupLocationTimezoneJob('00000000-0000-7000-8000-000000000000'))
            ->handle(app(LocationRepository::class));

        Http::assertNothingSent();
    }

    public function test_lookup_job_pushes_active_posts_for_the_location_to_mastodon_when_timezone_is_resolved(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(['timeZone' => 'Europe/Berlin']),
        ]);
        Bus::fake();

        (new LookupLocationTimezoneJob($location->id))->handle(app(LocationRepository::class));

        Bus::assertDispatched(PushLocationTimezoneChangeToMastodon::class, function (PushLocationTimezoneChangeToMastodon $job) use ($location) {
            return $this->jobProperty($job, 'locationId') === $location->id;
        });
    }

    public function test_lookup_job_does_not_push_to_mastodon_when_timezone_lookup_fails(): void
    {
        $location = Location::factory()->create(['timezone' => null]);

        Http::fake([
            '*' => Http::response(null, 500),
        ]);
        Bus::fake();

        (new LookupLocationTimezoneJob($location->id))->handle(app(LocationRepository::class));

        Bus::assertNotDispatched(PushLocationTimezoneChangeToMastodon::class);
    }
}
