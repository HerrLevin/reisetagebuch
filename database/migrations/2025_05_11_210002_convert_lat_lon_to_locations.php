<?php

use App\Models\Location;
use App\Models\RequestLocation;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Location::chunk(200, function ($locations) {
            foreach ($locations as $location) {
                $location->update([
                    'location' => Point::makeGeodetic($location->latitude, $location->longitude),
                ]);
            }
        });

        RequestLocation::chunk(200, function ($locations) {
            foreach ($locations as $location) {
                $location->update([
                    'location' => Point::makeGeodetic($location->latitude, $location->longitude),
                ]);
            }
        });
    }

    public function down(): void
    {
        Location::chunk(200, function ($locations) {
            foreach ($locations as $location) {
                $location->update([
                    'latitude' => $location->location->getLatitude(),
                    'longitude' => $location->location->getLongitude(),
                ]);
            }
        });
        RequestLocation::chunk(200, function ($locations) {
            foreach ($locations as $location) {
                $location->update([
                    'latitude' => $location->location->getLatitude(),
                    'longitude' => $location->location->getLongitude(),
                ]);
            }
        });
    }
};
