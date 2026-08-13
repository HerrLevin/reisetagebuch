<?php

namespace Tests\Feature\Commands;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ImportCountriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_countries_and_resolves_iso_codes_with_fallbacks(): void
    {
        Artisan::call('app:import-countries', ['--path' => 'tests/Fixtures/countries-fixture.geojson']);

        $this->assertSame(['DE', 'FR', 'XK'], Country::query()->orderBy('iso_a2')->pluck('iso_a2')->all());
        $this->assertSame('Germany', Country::where('iso_a2', 'DE')->value('name'));
        // France only has a resolvable code via ISO_A2_EH, not ISO_A2
        $this->assertSame('France', Country::where('iso_a2', 'FR')->value('name'));
        // Kosovo has no ISO_A2/ISO_A2_EH at all, resolved via the ADM0_A3 override map
        $this->assertSame('Kosovo', Country::where('iso_a2', 'XK')->value('name'));
        // Nowhereland has no resolvable code anywhere and must be skipped, not crash the import
        $this->assertNull(Country::where('name', 'Nowhereland')->first());
    }

    public function test_drops_far_flung_overseas_components_for_main_landmass_only_countries(): void
    {
        Artisan::call('app:import-countries', ['--path' => 'tests/Fixtures/countries-fixture.geojson']);

        // the fixture's France feature has 2 polygons: mainland Europe and a
        // French-Guiana-like polygon ~7000km away, which must be dropped
        $france = Country::where('iso_a2', 'FR')->first();
        $this->assertCount(1, $france->geometry);
    }

    public function test_is_idempotent_when_run_twice(): void
    {
        Artisan::call('app:import-countries', ['--path' => 'tests/Fixtures/countries-fixture.geojson']);
        Artisan::call('app:import-countries', ['--path' => 'tests/Fixtures/countries-fixture.geojson']);

        $this->assertSame(3, Country::count());
    }
}
