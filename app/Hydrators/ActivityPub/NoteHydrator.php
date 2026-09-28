<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Note;
use App\Http\Resources\LocationDto;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Http\Resources\StopDto;

class NoteHydrator
{
    private const int RTB_EXTENSION_VERSION = 1;

    public function hydrate(BasePost $post, string $actorUrl, string $followersUrl, bool $context = false): Note
    {
        $note = new Note;
        $note->id = route('ap.post-object', ['id' => $post->id]);
        $note->published = $post->createdAt;
        $note->attributedTo = $actorUrl;
        $note->content = $post->getHtmlBody() ?? '';
        $note->to = ['https://www.w3.org/ns/activitystreams#Public'];
        $note->cc = [$followersUrl];

        if ($extension = $this->buildExtension($post)) {
            $note->rtbExtension = $extension;
        }

        if ($context) {
            $note->setContext();
        }

        return $note;
    }

    /**
     * Builds the reisetagebuch-to-reisetagebuch structured extension payload for
     * Location/Transport posts, so two RTB instances can exchange full-fidelity data
     * instead of just the flattened HTML in `content`. Returns null for any other post
     * type, leaving the Note's `rtbExtension` property unset (see Note::$rtbExtension).
     */
    private function buildExtension(BasePost $post): ?array
    {
        return match (true) {
            $post instanceof LocationPost => [
                'rtbVersion' => self::RTB_EXTENSION_VERSION,
                'postType' => 'location',
                'location' => $this->buildLocationPayload($post),
            ],
            $post instanceof TransportPost => [
                'rtbVersion' => self::RTB_EXTENSION_VERSION,
                'postType' => 'transport',
                'transport' => $this->buildTransportPayload($post),
            ],
            default => null,
        };
    }

    private function buildLocationPayload(LocationPost $post): array
    {
        return [
            ...$this->buildLocationCore($post->location),
            'travelReason' => $post->travelReason?->value,
            'visitedAt' => $post->visitedAt,
        ];
    }

    private function buildTransportPayload(TransportPost $post): array
    {
        return [
            'originStop' => $this->buildStopPayload($post->originStop),
            'destinationStop' => $this->buildStopPayload($post->destinationStop),
            'trip' => [
                'id' => $post->trip->id,
                'foreignId' => $post->trip->foreignId,
                'mode' => $post->trip->mode?->value,
                'lineName' => $post->trip->lineName,
                'routeLongName' => $post->trip->routeLongName,
                'tripShortName' => $post->trip->tripShortName,
                'displayName' => $post->trip->displayName,
                'routeColor' => $post->trip->routeColor,
                'routeTextColor' => $post->trip->routeTextColor,
            ],
            'manualDepartureTime' => $post->manualDepartureTime,
            'manualArrivalTime' => $post->manualArrivalTime,
            'travelReason' => $post->travelReason?->value,
            'distance' => $post->distance,
            'duration' => $post->duration,
            'userGeometry' => $post->userGeometry,
        ];
    }

    private function buildStopPayload(StopDto $stop): array
    {
        return [
            'id' => $stop->id,
            'name' => $stop->name,
            'location' => $this->buildLocationCore($stop->location),
            'arrivalTime' => $stop->arrivalTime,
            'departureTime' => $stop->departureTime,
            'arrivalDelay' => $stop->arrivalDelay,
            'departureDelay' => $stop->departureDelay,
        ];
    }

    /**
     * Fields shared between a LocationPost's own location and a transport stop's
     * location. distance is deliberately excluded: it's viewer-relative (distance to
     * a nearby-search origin), meaningless once federated to another instance.
     */
    private function buildLocationCore(LocationDto $location): array
    {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'timezone' => $location->timezone,
            'emoji' => $location->emoji,
            'tags' => array_map(
                fn ($tag) => ['key' => $tag->key, 'value' => $tag->value],
                $location->tags,
            ),
            'identifiers' => array_map(
                fn ($identifier) => [
                    'type' => $identifier->type,
                    'origin' => $identifier->origin,
                    'identifier' => $identifier->identifier,
                ],
                $location->identifiers,
            ),
        ];
    }
}
