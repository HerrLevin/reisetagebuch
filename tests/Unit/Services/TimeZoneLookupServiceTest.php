<?php

namespace Tests\Unit\Services;

use App\Models\TimezoneBoundary;
use App\Services\TimeZoneLookupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeZoneLookupServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_tzid_of_the_boundary_containing_the_point(): void
    {
        TimezoneBoundary::factory()->create([
            'tzid' => 'Europe/Berlin',
            'geometry' => TimezoneBoundary::factory()->squareAround(52.52, 13.405, 1),
        ]);

        $this->assertSame('Europe/Berlin', (new TimeZoneLookupService)->lookup(52.52, 13.405));
    }

    public function test_returns_null_when_no_boundary_contains_the_point(): void
    {
        TimezoneBoundary::factory()->create([
            'tzid' => 'Europe/Berlin',
            'geometry' => TimezoneBoundary::factory()->squareAround(52.52, 13.405, 1),
        ]);

        $this->assertNull((new TimeZoneLookupService)->lookup(-33.86, 151.21));
    }
}
