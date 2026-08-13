<?php

namespace App\Repositories;

use App\Models\Country;
use App\Models\Location;
use Clickbar\Magellan\Database\PostgisFunctions\ST;
use Illuminate\Support\Facades\DB;

class CountryRepository
{
    /**
     * @return string[]
     */
    public function getVisitedCountryCodes(string $userId): array
    {
        $fromLocationPosts = Location::query()
            ->join('location_posts', 'locations.id', '=', 'location_posts.location_id')
            ->join('posts', 'location_posts.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->whereNotNull('locations.country_code')
            ->pluck('locations.country_code');

        // transport posts don't store their origin/destination location directly; they
        // reference a trip stop, which in turn references the location (mirrors
        // LocationRepository::getTransportPostLocationsForUser)
        $fromTransportEndpoints = DB::table('transport_posts')
            ->join('posts', 'transport_posts.post_id', '=', 'posts.id')
            ->join('transport_trip_stops as origin_stops', 'origin_stops.id', '=', 'transport_posts.origin_stop_id')
            ->join('transport_trip_stops as destination_stops', 'destination_stops.id', '=', 'transport_posts.destination_stop_id')
            ->leftJoin('locations as origin', 'origin_stops.location_id', '=', 'origin.id')
            ->leftJoin('locations as destination', 'destination_stops.location_id', '=', 'destination.id')
            ->where('posts.user_id', $userId)
            ->select('origin.country_code as origin_code', 'destination.country_code as dest_code')
            ->get()
            ->flatMap(fn ($row) => [$row->origin_code, $row->dest_code]);

        return $fromLocationPosts->merge($fromTransportEndpoints)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  string[]  $visitedCodes
     * @return string[]
     */
    public function getTransitedOnlyCountryCodes(string $userId, array $visitedCodes): array
    {
        $transited = DB::table('transport_posts')
            ->join('posts', 'transport_posts.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->whereNotNull('transport_posts.transited_country_codes')
            ->pluck('transport_posts.transited_country_codes')
            ->flatMap(fn ($json) => json_decode($json, true) ?? [])
            ->unique();

        return $transited->diff($visitedCodes)->values()->all();
    }

    // simplified (1:50m) country polygons can miss a route by a couple of kilometers
    // right at a coastline or border, so use a small tolerance instead of exact intersection
    public function getCountriesAlongLine($lineString): array
    {
        return Country::query()
            ->where(ST::dWithinGeography('geometry', $lineString, 5000), '=', true)
            ->pluck('iso_a2')
            ->toArray();
    }
}
