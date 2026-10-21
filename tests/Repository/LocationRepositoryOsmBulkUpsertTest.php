<?php

namespace Tests\Repository;

use App\Dto\OverpassLocation;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Repositories\LocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Throwable;

class LocationRepositoryOsmBulkUpsertTest extends TestCase
{
    use RefreshDatabase;

    private function osmLocation(string $osmId, array $tags = ['name' => 'Test Place'], string $osmType = 'node'): OverpassLocation
    {
        return new OverpassLocation($osmId, 52.5, 13.4, $osmType, $tags);
    }

    /**
     * @throws Throwable
     */
    public function test_a_new_feature_creates_location_identifier_and_tags(): void
    {
        Http::fake();

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test Cafe', 'amenity' => 'cafe']),
        ], fetchTimezone: false);

        $identifier = LocationIdentifier::where(['type' => 'node', 'origin' => 'osm', 'identifier' => '1'])->first();
        $this->assertNotNull($identifier);
        $this->assertSame('Test Cafe', $identifier->location->name);
        $this->assertSame(2, $identifier->location->tags()->count());
    }

    /**
     * @throws Throwable
     */
    public function test_a_feature_with_no_resolvable_name_is_skipped_entirely(): void
    {
        Http::fake();

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['highway' => 'residential']),
        ], fetchTimezone: false);

        $this->assertDatabaseCount('location_identifiers', 0);
        $this->assertDatabaseCount('locations', 0);
    }

    /**
     * @throws Throwable
     */
    public function test_re_importing_an_existing_feature_updates_in_place_without_duplicating(): void
    {
        $location = Location::factory()->create(['name' => 'Old Name']);
        LocationIdentifier::create([
            'name' => 'Existing',
            'location_id' => $location->id,
            'type' => 'node',
            'origin' => 'osm',
            'identifier' => '1',
        ]);

        Http::fake();

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'New Name']),
        ], fetchTimezone: false);

        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('location_identifiers', 1);
        $this->assertSame('New Name', $location->fresh()->name);
    }

    /**
     * @throws Throwable
     */
    public function test_timezone_lookup_is_skipped_once_already_known(): void
    {
        $location = Location::factory()->create(['timezone' => 'Europe/Berlin']);
        LocationIdentifier::create([
            'name' => 'Existing',
            'location_id' => $location->id,
            'type' => 'node',
            'origin' => 'osm',
            'identifier' => '1',
        ]);

        Http::fake();

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1'),
        ], fetchTimezone: true);

        Http::assertNothingSent();
        $this->assertSame('Europe/Berlin', $location->fresh()->timezone);
    }

    /**
     * @throws Throwable
     */
    public function test_timezone_is_resolved_and_persisted_for_a_new_location(): void
    {
        Http::fake(['*' => Http::response(['tz' => 'Europe/Berlin'])]);

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1'),
        ], fetchTimezone: true);

        $identifier = LocationIdentifier::where('identifier', '1')->first();
        $this->assertSame('Europe/Berlin', $identifier->location->timezone);
    }

    /**
     * @throws Throwable
     */
    public function test_a_mixed_chunk_resolves_each_row_independently_in_one_upsert(): void
    {
        $known = Location::factory()->create(['timezone' => 'Europe/Berlin']);
        LocationIdentifier::create([
            'name' => 'Existing',
            'location_id' => $known->id,
            'type' => 'node',
            'origin' => 'osm',
            'identifier' => 'known',
        ]);

        Http::fake(['*' => Http::response(null, 500)]);

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('known'),
            $this->osmLocation('failing'),
        ], fetchTimezone: true);

        $this->assertSame('Europe/Berlin', $known->fresh()->timezone);
        $failing = LocationIdentifier::where('identifier', 'failing')->first();
        $this->assertNull($failing->location->timezone);
    }

    /**
     * @throws Throwable
     */
    public function test_re_importing_a_feature_with_a_changed_tag_value_overwrites_it_in_place(): void
    {
        Http::fake();
        $repository = app(LocationRepository::class);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'cafe']),
        ], fetchTimezone: false);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'bar']),
        ], fetchTimezone: false);

        $tags = LocationIdentifier::where('identifier', '1')->first()->location->tags;
        $this->assertCount(2, $tags);
        $this->assertSame('bar', $tags->firstWhere('key', 'amenity')->value);
    }

    /**
     * @throws Throwable
     */
    public function test_re_importing_a_feature_that_dropped_a_tag_key_keeps_the_stale_tag_row(): void
    {
        Http::fake();
        $repository = app(LocationRepository::class);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'cafe']),
        ], fetchTimezone: false);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test']),
        ], fetchTimezone: false);

        $tags = LocationIdentifier::where('identifier', '1')->first()->location->tags;
        $this->assertNotNull($tags->firstWhere('key', 'amenity'));
    }
}
