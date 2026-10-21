<?php

namespace App\Services;

use App\Models\TimezoneBoundary;
use Clickbar\Magellan\Data\Geometries\Point;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use DateTimeZone;
use Exception;

class TimeZoneLookupService
{
    /**
     * Resolve the IANA timezone identifier for a coordinate via the vendored
     * timezone-boundary-builder polygons (see database/data/README.md). Returns
     * null only if the coordinate falls outside all boundaries, which shouldn't
     * happen since the oceans-included dataset covers the whole globe.
     */
    public function lookup(float $latitude, float $longitude): ?string
    {
        $point = Point::makeGeodetic($latitude, $longitude);

        $tzid = TimezoneBoundary::query()
            ->where(ST::covers('geometry', $point), '=', true)
            ->value('tzid');

        if (! is_string($tzid)) {
            return null;
        }

        try {
            // DateTimeZone::listIdentifiers() excludes Etc/GMT±N, which the oceans
            // boundaries legitimately return for open water, so construct-and-catch
            // instead of checking membership in that list.
            new DateTimeZone($tzid);
        } catch (Exception) {
            return null;
        }

        return $tzid;
    }
}
