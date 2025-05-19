<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\OverpassLocation;

class OsmNameService
{
    public function getName(OverpassLocation $location): ?string
    {
        $name = $location->tags['name'] ?? null;

        if ($name === null) {
            if (isset($location->tags['amenity'])) {
                $name = $this->getAmenityName($location);
            }
        }

        return $name;
    }

    private function getAmenityName(OverpassLocation $location): ?string
    {
        if ($location->tags['amenity'] === 'parcel_locker') {
            $operator = $location->tags['operator'] ?? null;
            $brand = $location->tags['brand'] ?? null;
            $ref = $location->tags['ref'] ?? null;
            $name = $brand ?? $operator;
            return $ref ? $name . ' ' . $ref : $name;
        }

        return null;
    }
}
