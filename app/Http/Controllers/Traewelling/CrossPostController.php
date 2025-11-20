<?php

namespace App\Http\Controllers\Traewelling;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\PostMetaInfo\TravelRole;
use App\Enums\TransportMode;
use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\LocationIdentifier;
use App\Models\Post;
use App\Models\SocialAccount;
use App\Models\TransportTripStop;
use App\Repositories\PostMetaInfoRepository;
use App\Services\TraewellingRequestService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class CrossPostController extends Controller
{
    private TraewellingRequestService $traewellingRequestService;

    private PostMetaInfoRepository $postMetaInfoRepository;

    private ?string $traewellingId = null;

    public function __construct(?TraewellingRequestService $traewellingRequestService = null, ?PostMetaInfoRepository $postMetaInfoRepository = null)
    {
        $this->traewellingRequestService = $traewellingRequestService ?? new TraewellingRequestService;
        $this->postMetaInfoRepository = $postMetaInfoRepository ?? new PostMetaInfoRepository;
    }

    private function getPost(string $postId): ?Post
    {
        $post = Post::with([
            'transportPost.originStop.location.identifiers',
            'transportPost.destinationStop.location.identifiers',
            'transportPost.transportTrip',
        ])->find($postId);
        $this->traewellingId = $post?->metaInfos->where('key', MetaInfoKey::TRAEWELLING_TRIP_ID)->first()?->value ?? null;
        $isTrwlUser = SocialAccount::whereUserId($post?->user->id)->whereProvider('traewelling')->exists();

        if (! $isTrwlUser) {
            return null;
        }

        return $post;
    }

    public function crossCheckIn(string $postId): ?bool
    {
        $post = $this->getPost($postId);
        if (! $post || $this->traewellingId) {
            Log::debug('Skipping Traewelling check-in for post '.$postId);

            return null;
        }

        if ($post->transportPost->transportTrip->provider === 'transitous') {
            $data = $this->crosspostTransitous($post);
        } elseif ($post->transportPost->transportTrip->provider === 'reisetagebuch') {
            $data = $this->crosspostManualTrip($post);
        } else {
            Log::error('Unknown trip provider: '.$post->transportPost->transportTrip->provider);

            return false;
        }

        $this->traewellingId = $data['data']['status']['id'] ?? '';

        $meta = $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAEWELLING_TRIP_ID, $this->traewellingId);
        Log::debug('Created Traewelling check-in for post '.$postId.' with trwl-id '.$this->traewellingId, ['response' => $data, 'meta' => $meta]);

        $this->syncTags($post);

        return true;
    }

    private function syncTags(Post $post): void
    {
        $tags = $this->traewellingRequestService->getPostTags($this->traewellingId, $post->user_id);
        Log::debug('Syncing tags for Traewelling post '.$this->traewellingId, ['existing_tags' => $tags]);
        $visibility = $post->visibility->getTraewellingVisibility();

        $travelRole = $this->postMetaInfoRepository->getMetaInfoValue($post, MetaInfoKey::TRAVEL_ROLE);
        $existingTag = collect($tags)->firstWhere('key', 'trwl:role') !== null;
        if ($travelRole) {
            $travelRole = TravelRole::tryFrom($travelRole);
            if ($travelRole) {
                $this->traewellingRequestService->updateOrCreateTag($existingTag, $this->traewellingId, $post->user_id, 'trwl:role', $travelRole->getTraewellingIdentifier(), $visibility);
            }
        } elseif ($existingTag) {
            $this->traewellingRequestService->deletePostTag($this->traewellingId, $post->user_id, 'trwl:role');
        }

        $tripId = $this->postMetaInfoRepository->getMetaInfoValue($post, MetaInfoKey::TRIP_ID);
        $existingTag = collect($tags)->firstWhere('key', 'trwl:journey_number') !== null;
        if ($tripId) {
            $this->traewellingRequestService->updateOrCreateTag($existingTag, $this->traewellingId, $post->user_id, 'trwl:journey_number', $tripId, $visibility);
        } elseif ($existingTag) {
            $this->traewellingRequestService->deletePostTag($this->traewellingId, $post->user_id, 'trwl:journey_number');
        }

        $vehicleIds = $this->getVehicleIds($post);
        $existingTag = collect($tags)->firstWhere('key', 'trwl:journey_number') !== null;
        if ($vehicleIds) {
            $this->traewellingRequestService->updateOrCreateTag($existingTag, $this->traewellingId, $post->user_id, 'trwl:vehicle_number', $vehicleIds, $visibility);
        } elseif ($existingTag) {
            $this->traewellingRequestService->deletePostTag($this->traewellingId, $post->user_id, 'trwl:vehicle_number');
        }
    }

    private function getVehicleIds(Post $post): string
    {
        $vehicleIds = $post->metaInfos
            ->where('key', MetaInfoKey::VEHICLE_ID->value)
            ->sortBy('order')
            ->pluck('value')
            ->filter()
            ->toArray();

        return implode(' + ', $vehicleIds);
    }

    public function updatePost(string $postId): void
    {
        $post = $this->getPost($postId);
        if (! $post || ! $this->traewellingId) {
            Log::debug('Skipping Traewelling post update for post '.$postId, ['traewellingId' => $this->traewellingId]);

            return;
        }

        $payload = $this->getUpdatePayload($post);

        $this->traewellingRequestService->updatePost($this->traewellingId, $post->user_id, $payload);
        $this->syncTags($post);
    }

    public function changeExit(string $postId): void
    {
        $post = $this->getPost($postId);
        if (! $post || ! $this->traewellingId) {
            Log::debug('Skipping Traewelling exit change for post '.$postId, ['traewellingId' => $this->traewellingId]);

            return;
        }
        $this->traewellingRequestService->getAccessToken($post->user_id);
        $payload = $this->getUpdatePayload($post);

        $destinationStop = $post->transportPost->destinationStop;
        $trwlDestinationIdentifier = $this->getTrwlStationIdentifier($destinationStop->location);
        if (! $trwlDestinationIdentifier) {
            Log::debug('Destination not found for exit change of post '.$postId);

            return;
        }
        $payload['destinationId'] = $trwlDestinationIdentifier->identifier;
        $payload['destinationArrivalPlanned'] = $destinationStop->arrival_time?->toIso8601String();

        $this->traewellingRequestService->updatePost($this->traewellingId, $post->user_id, $payload);
    }

    public function getUpdatePayload(Post $post): array
    {
        $reason = $this->postMetaInfoRepository->getMetaInfoValue($post, MetaInfoKey::TRAVEL_REASON);
        Log::debug($reason);
        $reason = TravelReason::tryFrom($reason) ?? TravelReason::LEISURE;

        return [
            'body' => $post->body,
            'visibility' => $post->visibility->getTraewellingVisibility(),
            'manualDeparture' => $post->transportPost->manual_departure?->toIso8601String(),
            'manualArrival' => $post->transportPost->manual_arrival?->toIso8601String(),
            'business' => $reason->getTraewellingReasonIdentifier(),
        ];
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
        $this->traewellingRequestService->getAccessToken($post->user->id);
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
    ): array {
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
        $this->traewellingRequestService->getAccessToken($post->user->id);

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
        $reason = $this->postMetaInfoRepository->getMetaInfoValue($post, MetaInfoKey::TRAVEL_REASON);
        $reason = TravelReason::tryFrom($reason) ?? TravelReason::LEISURE;
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
            'business' => $reason->getTraewellingReasonIdentifier(),
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
