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

        if ($location->tags['amenity'] === 'charging_station') {
            $operator = $location->tags['operator'] ?? null;
            $ref = $location->tags['ref'] ?? null;
            $name = 'Charging Station';

            if ($ref) {
                $name .= ' ' . $ref;
            } elseif ($operator) {
                $name .= sprintf(' (%s)', $operator);
            }

            return $name;
        }

        if ($location->tags['amenity'] === 'car_sharing' && !empty($location->tags['ref'])) {
            $operator = $location->tags['brand'] ?? $location->tags['network'] ?? $location->tags['operator'] ?? null;
            $name = $location->tags['ref'];

            if ($operator) {
                $name .= sprintf(' (%s)', $operator);
            }

            return $name;
        }

        return null;
    }
}
