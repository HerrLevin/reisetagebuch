<?php

namespace Tests\Unit\Services;

use App\Dto\OverpassLocation;
use App\Services\OsmNameService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OsmNameServiceTest extends TestCase
{
    public static function getNameWithAmenityTagDataProvider()
    {
        return [
            'parcel_locker without brand' => [
                'expectedName' => 'Packstation 1234',
                'tags' => [
                    'amenity' => 'parcel_locker',
                    'operator' => 'DHL',
                    'brand' => 'Packstation',
                    'ref' => '1234',
                ],
            ],
            'parcel_locker with brand' => [
                'expectedName' => 'DHL Packstation',
                'tags' => [
                    'amenity' => 'parcel_locker',
                    'operator' => 'DPD',
                    'brand' => 'DHL Packstation',
                ],
            ],
            'parcel_locker with ref' => [
                'expectedName' => 'DHL Packstation 1234',
                'tags' => [
                    'amenity' => 'parcel_locker',
                    'operator' => 'DPD',
                    'brand' => 'DHL Packstation',
                    'ref' => '1234',
                ],
            ],
            'car_sharing with ref and brand' => [
                'expectedName' => '42 (Flinkster)',
                'tags' => [
                    'amenity' => 'car_sharing',
                    'brand' => 'Flinkster',
                    'ref' => '42',
                ],
            ],
            'car_sharing prefers brand over network' => [
                'expectedName' => '42 (Flinkster)',
                'tags' => [
                    'amenity' => 'car_sharing',
                    'brand' => 'Flinkster',
                    'network' => 'DB',
                    'ref' => '42',
                ],
            ],
            'car_sharing prefers network over operator' => [
                'expectedName' => '42 (Flinkster)',
                'tags' => [
                    'amenity' => 'car_sharing',
                    'network' => 'Flinkster',
                    'operator' => 'DB Connect',
                    'ref' => '42',
                ],
            ],
            'car_sharing without ref' => [
                'expectedName' => null,
                'tags' => [
                    'amenity' => 'car_sharing',
                    'brand' => 'Flinkster',
                ],
            ],
            'other amenity with ref' => [
                'expectedName' => null,
                'tags' => [
                    'amenity' => 'stuff',
                    'ref' => '42',
                ],
            ],
            'amenity without anything' => [
                'expectedName' => null,
                'tags' => [
                    'amenity' => 'stuff',
                ],
            ],
        ];
    }

    public function test_get_name_with_no_tags(): void
    {
        $osmNameService = new OsmNameService;
        $location = $this->getMockLocation([]);

        $name = $osmNameService->getName($location);
        $this->assertNull($name);
    }

    public function test_get_name_with_name_tag(): void
    {
        $osmNameService = new OsmNameService;
        $location = $this->getMockLocation(['name' => 'Test Name']);

        $name = $osmNameService->getName($location);
        $this->assertEquals('Test Name', $name);
    }

    #[DataProvider('getNameWithAmenityTagDataProvider')]
    public function test_get_name_with_tag(?string $expectedName, array $tags): void
    {
        $osmNameService = new OsmNameService;
        $location = $this->getMockLocation($tags);

        $name = $osmNameService->getName($location);
        $this->assertEquals($expectedName, $name);
    }

    private function getMockLocation(array $tags): OverpassLocation
    {
        return new OverpassLocation(
            osmId: 123,
            latitude: 1.1,
            longitude: 1.1,
            osmType: 'node',
            tags: $tags,
        );
    }
}
