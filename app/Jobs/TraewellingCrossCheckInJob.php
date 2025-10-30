<?php

namespace App\Jobs;

use App\Enums\TransportMode;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\Post;
use App\Models\PostMetaInfo;
use App\Models\SocialAccount;
use App\Models\TransportTripStop;
use App\Services\TraewellingRequestService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class TraewellingCrossCheckInJob implements ShouldQueue
{
    use Queueable;

    private string $postId;

    private TraewellingRequestService $traewellingRequestService;

    public function __construct(string $postId, ?TraewellingRequestService $traewellingRequestService = null)
    {
        $this->postId = $postId;
        $this->traewellingRequestService = $traewellingRequestService ?? new TraewellingRequestService;
    }

    /**
     * @throws GuzzleException
     * @throws Exception|Throwable
     */
    public function handle(): void
    {
        $post = Post::with(['transportPost.originStop.location.identifiers', 'transportPost.destinationStop.location.identifiers', 'transportPost.transportTrip'])->find($this->postId);
        $hasTrwlMeta = $post?->metaInfos->where('key', 'traewelling_trip_id')->count() > 0;
        $isTrwlUser = SocialAccount::whereUserId($post?->user->id)->whereProvider('traewelling')->exists();

        if (! $isTrwlUser || $hasTrwlMeta) {
            Log::debug('Skipping Traewelling check-in for post '.$this->postId);

            return;
        }

        if ($post->transportPost->transportTrip->provider === 'transitous') {
            $data = $this->crosspostTransitous($post);
        } elseif ($post->transportPost->transportTrip->provider === 'reisetagebuch') {
            $data = $this->crosspostManualTrip($post);
        } else {
            Log::error('Unknown trip provider: '.$post->transportPost->transportTrip->provider);

            return;
        }

        $meta = new PostMetaInfo;
        $meta->post_id = $post->id;
        $meta->key = 'traewelling_trip_id';
        $meta->value = $data['status']['id'] ?? '';
        $meta->save();
        Log::debug('Created Traewelling check-in for post '.$this->postId, ['response' => $data, 'meta' => $meta]);
    }

    private function getTrwlStationIdentifier(Location $location): ?LocationIdentifier
    {
        Log::debug('Getting TRWL-id for location '.$location->name, ['location_id' => $location->id]);
        $traewellingIdentifier = $location->identifiers->firstWhere('origin', 'traewelling') ?? null;
        if ($traewellingIdentifier !== null) {
            Log::debug('Found existing TRWL-id for location '.$location->name, ['identifier' => $traewellingIdentifier]);

            return $traewellingIdentifier;
        }

        $stationMotisIdentifier = $location->identifiers->firstWhere('origin', 'motis') ?? null;
        if ($stationMotisIdentifier !== null) {
            $trwlStation = $this->traewellingRequestService->getTrwlStationFromIdentifier($stationMotisIdentifier, $this);
            if ($trwlStation !== null) {
                Log::debug('Found TRWL-id from MOTIS identifier for location '.$location->name, ['identifier' => $trwlStation]);

                return LocationIdentifier::updateOrCreate(
                    ['origin' => 'traewelling', 'type' => 'stop', 'location_id' => $location->id],
                    ['identifier' => $trwlStation['id'], 'name' => $trwlStation['name']]
                );
            }
        }

        $trwlStation = $this->traewellingRequestService->getTrwlStationFromLocation($location->location, $this);
        if ($trwlStation !== null) {
            Log::debug('Found TRWL-id from location for location '.$location->name, ['identifier' => $trwlStation]);

            return LocationIdentifier::updateOrCreate(
                ['origin' => 'traewelling', 'type' => 'stop', 'location_id' => $location->id],
                ['identifier' => $trwlStation['id'], 'name' => $trwlStation['name']]
            );
        }

        Log::debug('No TRWL-id found for location '.$location->name);

        return null;
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function crosspostManualTrip(Post $post): ?array
    {
        $this->traewellingRequestService->getAccessToken($post);
        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;

        $trwlOriginIdentifier = $this->getTrwlStationIdentifier($originStop->location);
        $trwlDestinationIdentifier = $this->getTrwlStationIdentifier($destinationStop->location);
        if (! $trwlOriginIdentifier || ! $trwlDestinationIdentifier) {
            throw new Exception('Origin or destination departure board not found');
        }

        // post to traewelling
        $trip = $this->createTrip($post, $trwlOriginIdentifier, $trwlDestinationIdentifier);

        return $this->checkin($post, $trwlOriginIdentifier, $trwlDestinationIdentifier, false, $trip['data']['id'] ?? null);
    }

    /**
     * @param  Collection<int, TransportTripStop>  $stopovers
     */
    private function createStopovers(Collection $stopovers): Collection
    {
        Log::debug('Creating stopovers');
        Log::debug(sprintf('Stopovers count: %d', $stopovers->count()));
        if ($stopovers->count() <= 2) {
            return collect();
        }
        // if no times are given, interpolate them evenly between origin and destination
        $firstStop = $stopovers->first();
        $lastStop = $stopovers->last();
        $totalDuration = $firstStop->departure_time && $lastStop->arrival_time ?
            $firstStop->departure_time->diffInSeconds($lastStop->arrival_time) : null;
        Log::debug('Total duration: '.($totalDuration ?? 'null'));

        $numStops = $stopovers->count() - 1;
        // remove first and last stop from stopovers
        $stopovers = $stopovers->slice(1, $stopovers->count() - 2);
        Log::debug('Number of intermediate stops: '.$numStops);

        return $stopovers->map(function (TransportTripStop $stop, int $index) use ($firstStop, $totalDuration, $numStops) {
            $interpolatedArrival = null;
            $interpolatedDeparture = null;
            if ($totalDuration !== null && $numStops > 0) {
                // interpolate evenly
                $interpolatedArrival = $firstStop->departure_time?->copy()->addSeconds(intval($totalDuration * ($index + 1) / ($numStops + 1)));
                $interpolatedDeparture = $interpolatedArrival?->copy()->addMinutes(2); // assume 2 minutes stopover
                Log::debug(sprintf('Interpolated stop %d: arrival %s, departure %s', $index, $interpolatedArrival?->toIso8601String(), $interpolatedDeparture?->toIso8601String()));
            }

            return $this->createStopover($stop, $interpolatedDeparture, $interpolatedArrival);
        })->filter(fn ($stop) => $stop !== null);

    }

    private function createStopover(TransportTripStop $stop, ?Carbon $interpolatedDeparture = null, ?Carbon $interpolatedArrival = null): ?array
    {
        $trwlStation = $this->getTrwlStationIdentifier($stop->location);
        if (! $trwlStation) {
            Log::debug('Stopover station '.$stop->location->name.' not found');

            return null;
        }

        return [
            'stationId' => (int) $trwlStation->identifier,
            'arrival' => $interpolatedArrival?->toIso8601String() ?? $stop->arrival_time?->toIso8601String() ?? null,
            'departure' => $interpolatedDeparture?->toIso8601String() ?? $stop->departure_time?->toIso8601String() ?? null,
        ];
    }

    private function getLineName(Post $post): string
    {
        if ($post->transportPost->transportTrip->line_name) {
            return $post->transportPost->transportTrip->line_name;
        }

        if ($post->transportPost->transportTrip->trip_short_name) {
            return $post->transportPost->transportTrip->trip_short_name;
        }

        return '_';
    }

    private function createTrip(
        Post $post,
        LocationIdentifier $trwlOrigin,
        LocationIdentifier $trwlDestination,
    ): mixed {
        $traewellingCategory = TransportMode::from($post->transportPost->transportTrip->mode)->getTraewellingType();

        $stops = $post->transportPost->transportTrip->stops;
        $stops = $stops->sortBy('stop_sequence');
        $originStop = $stops->first();
        $destinationStop = $stops->last();

        $stopovers = $this->createStopovers($stops);

        Log::debug('Creating Traewelling trip');

        $requestBody = [
            'originId' => (int) $trwlOrigin->identifier,
            'originDeparturePlanned' => $originStop->departure_time?->toIso8601String() ?? $originStop->arrival_time?->toIso8601String() ?? '',
            'destinationId' => (int) $trwlDestination->identifier,
            'destinationArrivalPlanned' => $destinationStop->arrival_time?->toIso8601String() ?? $destinationStop->departure_time?->toIso8601String() ?? '',
            'lineName' => $this->getLineName($post),
            'journeyNumber' => $this->getJourneyNumber($post),
            'operatorId' => null,
            'category' => $traewellingCategory,
            'stopovers' => array_values($stopovers->toArray()),
        ];

        Log::debug('Traewelling API create trip request', ['body' => $requestBody]);

        return $this->traewellingRequestService->createTrip($requestBody);
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function crosspostTransitous(Post $post): ?array
    {
        $this->traewellingRequestService->getAccessToken($post);

        $originStop = $post->transportPost->originStop;
        $destinationStop = $post->transportPost->destinationStop;

        $trwlOriginIdentifier = $this->getTrwlStationIdentifier($originStop->location);
        $trwlDestinationIdentifier = $this->getTrwlStationIdentifier($destinationStop->location);
        if (! $trwlOriginIdentifier || ! $trwlDestinationIdentifier) {
            throw new Exception('Origin or destination not found');
        }

        // post to traewelling
        $data = $this->checkin($post, $trwlOriginIdentifier, $trwlDestinationIdentifier);
        if (isset($data['error'])) {
            throw new Exception('Traewelling API error: '.$data['error']);
        }

        return $data;
    }

    /**
     * @throws GuzzleException
     * @throws Throwable
     */
    private function checkin(
        Post $post,
        LocationIdentifier $trwlOrigin,
        LocationIdentifier $trwlDestination,
        bool $force = false,
        ?string $tripId = null,
    ): ?array {
        $body = [
            'body' => $post->body,
            'start' => $trwlOrigin->identifier,
            'destination' => $trwlDestination->identifier,
            'tripId' => $tripId ?? $post->transportPost->transportTrip->foreign_trip_id,
            'departure' => $post->transportPost->originStop->departure_time?->toIso8601String(),
            'arrival' => $post->transportPost->destinationStop->arrival_time?->toIso8601String(),
            'lineName' => $this->getLineName($post),
            'visibility' => $post->visibility->getTraewellingVisibility(),
            'force' => $force,
        ];
        try {
            Log::debug('Traewelling API checkin request', [
                'post_id' => $post->id,
                'body' => $body,
            ]);

            return $this->traewellingRequestService->checkin($body);
        } catch (Throwable $e) {
            if ($e->getCode() === 409 && ! $force) {
                return $this->checkin($post, $trwlOrigin, $trwlDestination, true, $tripId);
            } else {
                throw $e;
            }
        }
    }

    private function getJourneyNumber(Post $post): ?int
    {
        $shortName = $post->transportPost->transportTrip->trip_short_name;
        if ($shortName) {
            if (is_numeric($shortName)) {
                return (int) $shortName;
            }
            if (preg_match('/\d+/', $shortName, $matches)) {
                return (int) $matches[0];
            }
        }

        return null;
    }
}
