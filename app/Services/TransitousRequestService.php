<?php

namespace App\Services;

use App\Dto\Coordinate;
use App\Dto\MotisApi\StopDto;
use App\Dto\MotisApi\StopTimeDto;
use App\Dto\MotisApi\TripDto;
use App\Hydrators\MotisHydrator;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TransitousRequestService
{
    private const API_URL = 'https://api.transitous.org/api/v1';

    private VersionService $versionService;
    private GeoService $geoService;
    private MotisHydrator $hydrator;

    public function __construct(
        ?VersionService $versionService = null,
        ?GeoService $geoService = null,
        ?MotisHydrator $hydrator = null
    ) {
        $this->versionService = $versionService ?? new VersionService();
        $this->geoService = $geoService ?? new GeoService();
        $this->hydrator = $hydrator ?? new MotisHydrator();
    }

    /**
     * @returns Collection|StopTimeDto[]
     * @throws ConnectionException
     */
    public function getDepartures(string $identifier, Carbon $when): Collection
    {
        $params = [
            'stopId' => $identifier,
            'radius' => config('app.motis.radius', 500),
            'time'   => $when->toIso8601String(),
            'n'      => config('app.motis.results', 100),
        ];

        $response = Http::withUserAgent($this->versionService->getUserAgent())
            ->get(self::API_URL . '/stoptimes', $params);

        if (!$response->ok()) {
            Log::error('Unknown response (getDepartures)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            return collect();
        }

        $entries    = $response->json('stopTimes');
        $entries    = collect($entries);
        return $entries->map(function ($entry) {
            return $this->hydrator->hydrateStopTime($entry);
        });
    }

    /**
     * @param Point $point
     * @return Collection|StopDto[]
     * @throws ConnectionException
     */
    public function getNearby(Point $point): Collection|array {
        // TODO: convert to use the new Coordinate class
        $center = new Coordinate($point->getLatitude(), $point->getLongitude());
        $bbox   = $this->geoService->getBoundingBox($center, 500);

        $response = Http::withUserAgent($this->versionService->getUserAgent())->get(self::API_URL . '/map/stops', [
            'min' => (string) $bbox->lowerRight,
            'max' => (string) $bbox->upperLeft,
        ]);

        if (!$response->ok()) {
            Log::error('Unknown response (getNearby)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            return collect();
        }

        $stops = $response->json();
        $stops = collect($stops);
        $stops = $stops->map(function ($stop) use ($center) {
            $distance = $this->geoService->getDistance(
                new Coordinate($stop['lat'], $stop['lon']),
                $center
            );

            return $this->hydrator->hydrateStop($stop, $distance);
        });

        return $stops->sortBy('distance')->values();
    }

    public function getStopTimes(string $tripId): ?TripDto
    {
        $response = Http::withUserAgent($this->versionService->getUserAgent())->get(self::API_URL . '/trip/?tripId=' . $tripId);

        if (!$response->ok()) {
            Log::error('Unknown response (getStopTimes)', [
                'status' => $response->status(),
                'body'   => $response->body()
            ]);
            return null;
        }

        return $this->hydrator->hydrateTrip($response->json());
    }
}
