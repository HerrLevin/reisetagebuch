<?php

namespace Tests\Repository;

use App\Dto\OverpassLocation;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Repositories\LocationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test Cafe', 'amenity' => 'cafe']),
        ]);

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
        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['highway' => 'residential']),
        ]);

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

        app(LocationRepository::class)->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'New Name']),
        ]);

        $this->assertDatabaseCount('locations', 1);
        $this->assertDatabaseCount('location_identifiers', 1);
        $this->assertSame('New Name', $location->fresh()->name);
    }

    /**
     * @throws Throwable
     */
    public function test_re_importing_a_feature_with_a_changed_tag_value_overwrites_it_in_place(): void
    {
        $repository = app(LocationRepository::class);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'cafe']),
        ]);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'bar']),
        ]);

        $tags = LocationIdentifier::where('identifier', '1')->first()->location->tags;
        $this->assertCount(2, $tags);
        $this->assertSame('bar', $tags->firstWhere('key', 'amenity')->value);
    }

    /**
     * @throws Throwable
     */
    public function test_re_importing_a_feature_that_dropped_a_tag_key_keeps_the_stale_tag_row(): void
    {
        $repository = app(LocationRepository::class);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test', 'amenity' => 'cafe']),
        ]);

        $repository->bulkUpsertOsmLocations([
            $this->osmLocation('1', ['name' => 'Test']),
        ]);

        $tags = LocationIdentifier::where('identifier', '1')->first()->location->tags;
        $this->assertNotNull($tags->firstWhere('key', 'amenity'));
    }
}
