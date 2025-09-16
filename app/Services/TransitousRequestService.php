<?php

namespace App\Services;

use App\Dto\Coordinate;
use App\Dto\MotisApi\GeocodeResponseEntry;
use App\Dto\MotisApi\LocationType;
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
        $this->versionService = $versionService ?? new VersionService;
        $this->geoService = $geoService ?? new GeoService;
        $this->hydrator = $hydrator ?? new MotisHydrator;
    }

    /**
     * @returns Collection|StopTimeDto[]
     *
     * @throws ConnectionException
     */
    public function getDepartures(string $identifier, Carbon $when, array $filter = [], ?int $radius = null): Collection
    {
        $radius = $radius ?? config('app.motis.radius', 500);

        $params = [
            'stopId' => $identifier,
            'radius' => $radius,
            'time' => $when->toIso8601String(),
            'n' => config('app.motis.results', 100),
        ];

        if (! empty($filter)) {
            $params['mode'] = implode(',', $filter);
        }

        $response = Http::withUserAgent($this->versionService->getUserAgent())
            ->get(self::API_URL.'/stoptimes', $params);

        if (! $response->ok()) {
            Log::error('Unknown response (getDepartures)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return collect();
        }

        $entries = $response->json('stopTimes');
        $entries = collect($entries);

        return $entries->map(function ($entry) {
            return $this->hydrator->hydrateStopTime($entry);
        });
    }

    /**
     * @return Collection|StopDto[]
     *
     * @throws ConnectionException
     */
    public function getNearby(Point $point): Collection|array
    {
        // TODO: convert to use the new Coordinate class
        $center = new Coordinate($point->getLatitude(), $point->getLongitude());
        $bbox = $this->geoService->getBoundingBox($center, 500);

        $response = Http::withUserAgent($this->versionService->getUserAgent())->get(self::API_URL.'/map/stops', [
            'min' => (string) $bbox->lowerRight,
            'max' => (string) $bbox->upperLeft,
        ]);

        if (! $response->ok()) {
            Log::error('Unknown response (getNearby)', [
                'status' => $response->status(),
                'body' => $response->body(),
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
        $response = Http::withUserAgent($this->versionService->getUserAgent())->get(self::API_URL.'/trip/?tripId='.$tripId);

        if (! $response->ok()) {
            Log::error('Unknown response (getStopTimes)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $this->hydrator->hydrateTrip($response->json());
    }

    /**
     * @param  string  $text  the (potentially partially typed) address to resolve
     * @param  string|null  $language  language tags as used in OpenStreetMap (usually ISO 639-1, or ISO 639-2 if there's no ISO 639-1)
     * @param  LocationType|null  $type  Enum: "ADDRESS" "PLACE" "STOP". Default is all
     * @param  Point|null  $place  Used for biasing results towards the coordinate.
     * @return GeocodeResponseEntry[]
     *
     * @throws ConnectionException
     */
    public function geocode(string $text, ?string $language = null, ?LocationType $type = null, ?Point $place = null): array
    {
        $request = [
            'text' => $text,
        ];

        if ($language) {
            $request['lang'] = $language;
        }

        if ($type) {
            $request['type'] = $type;
        }

        if ($place) {
            $request['place'] = $place->getLatitude().','.$place->getLongitude();
        }

        $response = Http::withUserAgent($this->versionService->getUserAgent())->get(self::API_URL.'/geocode', $request);

        if (! $response->ok()) {
            Log::error('Unknown response (geocode)', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        $data = $response->json();
        if (empty($data)) {
            return [];
        }

        $result = [];
        foreach ($data as $entry) {
            try {
                $result[] = $this->hydrator->hydrateGeocodeEntry($entry);
            } catch (\Exception $e) {
                Log::error('Error hydrating geocode entry', [
                    'entry' => $entry,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $result;
    }
}
