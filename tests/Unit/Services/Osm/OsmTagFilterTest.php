<?php

namespace Tests\Unit\Services\Osm;

use App\Services\Osm\OsmTagFilter;
use PHPUnit\Framework\TestCase;

class OsmTagFilterTest extends TestCase
{
    public function test_matches_plain_filter_tag(): void
    {
        $this->assertTrue(OsmTagFilter::matches(['shop' => 'bakery']));
    }

    public function test_matches_filter_tag_with_allowed_value(): void
    {
        $this->assertTrue(OsmTagFilter::matches(['railway' => 'station']));
    }

    public function test_does_not_match_filter_tag_with_disallowed_value(): void
    {
        $this->assertFalse(OsmTagFilter::matches(['railway' => 'rail']));
    }

    public function test_excluded_amenity_value_does_not_match(): void
    {
        $this->assertFalse(OsmTagFilter::matches(['amenity' => 'waste_basket']));
    }

    public function test_non_excluded_amenity_value_matches(): void
    {
        $this->assertTrue(OsmTagFilter::matches(['amenity' => 'restaurant']));
    }

    public function test_force_name_tag_requires_name(): void
    {
        $this->assertFalse(OsmTagFilter::matches(['leisure' => 'park']));
        $this->assertTrue(OsmTagFilter::matches(['leisure' => 'park', 'name' => 'Central Park']));
    }

    public function test_unrelated_tags_do_not_match(): void
    {
        $this->assertFalse(OsmTagFilter::matches(['surface' => 'asphalt']));
    }

    public function test_all_keys_contains_plain_and_force_name_keys(): void
    {
        $keys = OsmTagFilter::allKeys();

        $this->assertContains('shop', $keys);
        $this->assertContains('leisure', $keys);
        $this->assertContains('admin_level', $keys);
    }
}
