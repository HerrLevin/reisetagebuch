<?php

namespace Tests\Unit\Models;

use App\Models\License;
use PHPUnit\Framework\TestCase;

class LicenseTest extends TestCase
{
    public function test_attribution_text_states_license_name_when_both_are_known(): void
    {
        $license = new License([
            'attribution_text' => 'Data by Transitous',
            'spdx' => 'ODbL-1.0',
        ]);

        $this->assertSame(
            'Data by Transitous (Open Database License (ODbL))',
            $license->getAttributionText(),
        );
    }

    public function test_attribution_text_falls_back_to_license_name_without_free_text(): void
    {
        $license = new License(['spdx' => 'MIT']);

        $this->assertSame('Data provided by MIT License', $license->getAttributionText());
    }

    public function test_attribution_text_states_both_feed_and_license_name(): void
    {
        $license = new License([
            'human_name' => 'Deutsche Bahn GTFS feed',
            'spdx' => 'CC-BY-4.0',
        ]);

        $this->assertSame(
            'Data provided by Deutsche Bahn GTFS feed under Creative Commons Attribution 4.0 International (CC BY 4.0)',
            $license->getAttributionText(),
        );
    }

    public function test_attribution_text_is_used_as_is_without_known_license_name(): void
    {
        $license = new License(['attribution_text' => 'Data by Transitous']);

        $this->assertSame('Data by Transitous', $license->getAttributionText());
    }

    public function test_attribution_text_is_null_without_any_information(): void
    {
        $license = new License;

        $this->assertNull($license->getAttributionText());
    }
}
