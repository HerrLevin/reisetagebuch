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
            'amenity without anything' => [
                'expectedName' => null,
                'tags' => [
                    'amenity' => 'stuff',
                ],
            ],
        ];
    }

    public function testGetNameWithNoTags(): void
    {
        $osmNameService = new OsmNameService();
        $location = $this->getMockLocation([]);

        $name = $osmNameService->getName($location);
        $this->assertNull($name);
    }

    public function testGetNameWithNameTag(): void
    {
        $osmNameService = new OsmNameService();
        $location = $this->getMockLocation(['name' => 'Test Name']);

        $name = $osmNameService->getName($location);
        $this->assertEquals('Test Name', $name);
    }

    #[DataProvider('getNameWithAmenityTagDataProvider')]
    public function testGetNameWithTag(?string $expectedName, array $tags): void
    {
        $osmNameService = new OsmNameService();
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
