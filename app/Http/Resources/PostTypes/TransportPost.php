<?php

namespace App\Http\Resources\PostTypes;

use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Http\Resources\StopDto;
use App\Http\Resources\TripDto;
use App\Http\Resources\UserDto;
use App\Models\ActivityPubPost;
use App\Models\Post;
use Carbon\Carbon;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TransportPost',
    description: 'Transport Post Resource',
    required: ['originStop', 'destinationStop', 'trip', 'travelReason', 'manualDepartureTime', 'manualArrivalTime', 'distance', 'duration', 'userGeometry'],
    type: 'object'
)]
class TransportPost extends BasePost
{
    #[OA\Property(
        property: 'originStop',
        ref: StopDto::class,
        description: 'Origin stop details of the transport post'
    )]
    public StopDto $originStop;

    #[OA\Property(
        property: 'destinationStop',
        ref: StopDto::class,
        description: 'Destination stop details of the transport post'
    )]
    public StopDto $destinationStop;

    #[OA\Property(
        property: 'trip',
        ref: TripDto::class,
        description: 'Trip details associated with the transport post'
    )]
    public TripDto $trip;

    #[OA\Property(
        property: 'manualDepartureTime',
        description: 'Manually specified departure time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualDepartureTime = null;

    #[OA\Property(
        property: 'manualArrivalTime',
        description: 'Manually specified arrival time in ISO 8601 format',
        type: 'string',
        format: 'date-time',
        nullable: true
    )]
    public ?string $manualArrivalTime = null;

    #[OA\Property(
        property: 'travelReason',
        ref: TravelReason::class,
        description: 'Reason for travel associated with the transport post',
        nullable: true
    )]
    public ?TravelReason $travelReason;

    #[OA\Property(
        property: 'distance',
        description: 'Distance traveled in meters',
        type: 'integer'
    )]
    public int $distance;

    #[OA\Property(
        property: 'duration',
        description: 'Duration of the trip in seconds',
        type: 'integer'
    )]
    public int $duration;

    #[OA\Property(
        property: 'userGeometry',
        description: 'User-uploaded track geometry as GeoJSON',
        type: 'object',
        nullable: true
    )]
    public ?array $userGeometry = null;

    public function __construct(?Post $post = null, ?UserDto $userDto = null, bool $withGeometry = false)
    {
        parent::__construct($post, $userDto);

        if ($post === null) {
            return;
        }

        $asdf = $post->transportPost->originStop;
        $this->originStop = new StopDto($asdf);
        $this->destinationStop = new StopDto($post->transportPost->destinationStop);
        $this->trip = new TripDto($post->transportPost->transportTrip);
        $this->manualDepartureTime = $post->transportPost->manual_departure?->toIso8601String();
        $this->manualArrivalTime = $post->transportPost->manual_arrival?->toIso8601String();
        $this->travelReason = TravelReason::tryFrom($post->metaInfos->where('key', MetaInfoKey::TRAVEL_REASON)->first()?->value);
        $this->distance = $post->transportPost->distance;
        $this->duration = $post->transportPost->duration;
        $this->updatedAt = $this->getUpdatedAt($post)?->toIso8601String();

        if ($withGeometry && $post->transportPost->user_geometry !== null) {
            $this->userGeometry = (new GeojsonGenerator)->generate($post->transportPost->user_geometry);
        }
    }

    public static function fromRemote(ActivityPubPost $post, UserDto $userDto, array $data = []): static
    {
        $dto = parent::fromRemote($post, $userDto);
        $dto->originStop = StopDto::fromRemote($data['originStop'] ?? []);
        $dto->destinationStop = StopDto::fromRemote($data['destinationStop'] ?? []);
        $dto->trip = TripDto::fromRemote($data['trip'] ?? []);
        $dto->manualDepartureTime = $data['manualDepartureTime'] ?? null;
        $dto->manualArrivalTime = $data['manualArrivalTime'] ?? null;
        $dto->travelReason = isset($data['travelReason']) ? TravelReason::tryFrom($data['travelReason']) : null;
        $dto->distance = (int) ($data['distance'] ?? 0);
        $dto->duration = (int) ($data['duration'] ?? 0);
        $dto->userGeometry = $data['userGeometry'] ?? null;

        return $dto;
    }

    private function getUpdatedAt(Post $post)
    {
        if ($post->transportPost->updated_at === null) {
            return $post->updated_at;
        }

        if ($post->transportPost->updated_at->gte($post->updated_at)) {
            return $post->transportPost->updated_at;
        }

        return $post->updated_at;
    }

    private function parseTime(?string $timeString, ?string $timezone): ?Carbon
    {
        if ($timeString === null) {
            return null;
        }

        return Carbon::parse($timeString)->setTimezone($timezone ?? 'UTC');
    }

    private function getDelay(?string $manualTime, ?Carbon $actualTime, ?int $defaultDelay): ?string
    {
        $delay = $defaultDelay;

        if ($manualTime !== null && $actualTime !== null) {
            $delay = (int) $actualTime->diffInSeconds(Carbon::parse($manualTime));
        }

        if ($delay !== null && $delay > 0) {
            return '+'.round($delay / 60);
        }

        return $delay;
    }

    private function formatTime(?Carbon $time, ?string $delay): ?string
    {
        if ($time === null) {
            return null;
        }

        $formattedTime = e($time->format('H:i'));
        if ($delay !== null && $delay !== 0) {
            $formattedTime .= " ($delay min)";
        }

        return $formattedTime;
    }

    public function getHtmlBody(): ?string
    {
        $parentBody = parent::getHtmlBody();
        $emoji = $this->trip->mode?->getEmoji();
        $line = e($this->trip->displayName ?? $this->trip->lineName);
        $number = $this->trip->tripShortName ? ' ('.e($this->trip->tripShortName).')' : '';
        $origin = e($this->originStop->name);
        $destination = e($this->destinationStop->name);
        $duration = round($this->duration / 60);
        $distance = round($this->distance / 1000, 1);

        $departureTime = $this->parseTime($this->originStop->departureTime ?? $this->originStop->arrivalTime, $this->originStop->location->timezone ?? 'UTC');
        $departureDelay = $this->getDelay($this->manualDepartureTime, $departureTime, $this->originStop->departureDelay);
        $departure = $this->formatTime($departureTime, $departureDelay);

        $arrivalTime = $this->parseTime($this->destinationStop->arrivalTime ?? $this->destinationStop->departureTime, $this->destinationStop->location->timezone ?? 'UTC');
        $arrivalDelay = $this->getDelay($this->manualArrivalTime, $arrivalTime, $this->destinationStop->arrivalDelay);
        $arrival = $this->formatTime($arrivalTime, $arrivalDelay);

        $body = "$emoji <strong>$line</strong> $number<br>".
            "🛤️ $origin → $destination<br>".
            "⏱️ $duration min • 📏 $distance km<br>".
            $this->travelReason?->getEmoji();

        if ($departure !== null) {
            $body .= "<br>▶️ $departure";
        }
        if ($arrival !== null) {
            $body .= "<br>⏹️ $arrival";
        }

        return $parentBody ? nl2br($parentBody."\n\n").$body : $body;
    }

    public function getSummary(): ?string
    {
        return sprintf(
            '%s@ %s, %s ➜ %s',
            $this->formattedBody(),
            $this->trip->displayName ?? $this->trip->tripShortName ?? $this->trip->lineName ?? $this->trip->routeLongName,
            $this->originStop->name,
            $this->destinationStop->name
        );
    }
}
