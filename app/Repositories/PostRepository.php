<?php

namespace App\Repositories;

use App\Dto\PostPaginationDto;
use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\PostMetaInfo\TravelRole;
use App\Enums\Visibility;
use App\Exceptions\NegativePeriodException;
use App\Exceptions\OriginAfterDestinationException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\PostHydrator;
use App\Models\Location;
use App\Models\Post;
use App\Models\TransportPost as TransportPostModel;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Carbon\Carbon;
use Clickbar\Magellan\Data\Geometries\LineString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use Throwable;

class PostRepository
{
    private PostHydrator $postHydrator;

    private HashTagRepository $hashTagRepository;

    private PostMetaInfoRepository $postMetaInfoRepository;

    public function __construct(?PostHydrator $postHydrator = null, ?HashTagRepository $hashTagRepository = null, ?PostMetaInfoRepository $postMetaInfoRepository = null)
    {
        $this->postHydrator = $postHydrator ?? new PostHydrator;
        $this->hashTagRepository = $hashTagRepository ?? new HashTagRepository;
        $this->postMetaInfoRepository = $postMetaInfoRepository ?? new PostMetaInfoRepository;
    }

    public function storeLocation(
        User $user,
        Location $location,
        Visibility $visibility,
        ?string $body = null,
        array $hashTagIds = [],
        TravelReason $travelReason = TravelReason::LEISURE
    ): BasePost|LocationPost|TransportPost {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);
            // create location post
            $post->locationPost()->create([
                'location_id' => $location->id,
            ]);

            if (! empty($hashTagIds)) {
                $this->hashTagRepository->syncHashTagsByValue($post, $hashTagIds);
            }
            $this->postMetaInfoRepository->setTravelReason($post, $travelReason);

            DB::commit();

            $post->refresh()->load('locationPost', 'hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function storeText(
        User $user,
        Visibility $visibility,
        string $body,
        array $tags = []
    ): BasePost|LocationPost|TransportPost {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);

            if (! empty($tags)) {
                $this->hashTagRepository->syncHashTagsByValue($post, $tags);
            }

            DB::commit();

            $post->load('hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function updateBasePost(
        BasePost $basePost,
        ?Visibility $visibility,
        ?string $body = null,
        array $hashTags = [],
        ?TravelReason $travelReason = null,
        ?array $vehicleIds = null,
        ?string $metaTripId = null,
        ?TravelRole $travelRole = null
    ): BasePost|LocationPost|TransportPost {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::where('id', $basePost->id)->firstOrFail();

            $post->body = $body;
            if ($visibility !== null) {
                $post->visibility = $visibility;
            }
            $post->save();

            if (! empty($hashTags)) {
                $this->hashTagRepository->syncHashTagsByValue($post, $hashTags);
            }

            if ($travelReason !== null) {
                $this->postMetaInfoRepository->setTravelReason($post, $travelReason);
            }

            if ($vehicleIds !== null) {
                $this->postMetaInfoRepository->setVehicleIds($post, $vehicleIds);
            }
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRIP_ID, $metaTripId);
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_ROLE, $travelRole?->value);

            DB::commit();

            $post->load('locationPost', 'transportPost', 'hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post, true);
    }

    public function updateTransportPost(TransportPost $transportPost, string $stopId, TravelReason $travelReason): TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::where('id', $transportPost->id)->with('transportPost.transportTrip.stops')->firstOrFail();
            $stop = TransportTripStop::where('id', $stopId)->firstOrFail();

            $transportPost = $post->transportPost;

            if ($transportPost->transport_trip_id !== $stop->transport_trip_id) {
                throw new StationNotOnTripException;
            }

            foreach ($transportPost->transportTrip->stops as $singleStop) {
                if ($singleStop->id === $transportPost->origin_stop_id) {
                    // reached origin, all good
                    break;
                }
                if ($singleStop->id === $stop->id) {
                    // reached new stop before origin, not good
                    throw new OriginAfterDestinationException;
                }
            }

            $transportPost->destination_stop_id = $stop->id;

            $transportPost->save();
            $this->postMetaInfoRepository->setTravelReason($post, $travelReason);
            DB::commit();

            $post->load('transportPost.destination', 'transportPost.origin', 'transportPost.transportTrip', 'transportPost.originStop.location', 'transportPost.destinationStop.location');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }

        return $this->postHydrator->modelToDto($post, true);
    }

    public function storeTransport(
        User $user,
        TransportTrip $transportTrip,
        TransportTripStop $originStop,
        TransportTripStop $destinationStop,
        Visibility $visibility,
        ?string $body = null,
        array $hashTagIds = [],
        TravelReason $travelReason = TravelReason::LEISURE,
        array $vehicleIds = [],
        ?string $metaTripId = null,
        ?TravelRole $travelRole = null
    ): BasePost|LocationPost|TransportPost {
        try {
            // If published_at would be more than 10 minutes in the future, set it to 10 minutes before departure
            $publishedAt = $originStop->departure_time ?? $originStop->arrival_time ?? Carbon::now();
            $publishedAt = $publishedAt->subMinutes(10);

            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
                'visibility' => $visibility,
                'published_at' => $publishedAt,
            ]);

            // create transport post
            $post->transportPost()->create([
                'transport_trip_id' => $transportTrip->id,
                'origin_stop_id' => $originStop->id,
                'destination_stop_id' => $destinationStop->id,
            ]);

            if (! empty($hashTagIds)) {
                $this->hashTagRepository->syncHashTagsByValue($post, $hashTagIds);
            }

            $this->postMetaInfoRepository->setTravelReason($post, $travelReason);
            $this->postMetaInfoRepository->setVehicleIds($post, $vehicleIds);
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRIP_ID, $metaTripId);
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_ROLE, $travelRole?->value);

            DB::commit();

            $post->load('transportPost', 'hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post, true);
    }

    private function basePostQuery(): Builder
    {
        return Post::with([
            'user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination',
            'transportPost.originStop', 'transportPost.destinationStop',
            'transportPost.originStop.location', 'transportPost.destinationStop.location', 'transportPost.transportTrip', 'hashTags',
        ])
            ->withCount('likes');
    }

    private function timelineQueryForUser(User $user): Builder
    {
        return $this->basePostQuery()
            ->withExists(['likes as liked_by_user' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }])
            ->where('user_id', '=', $user->id);
    }

    public function getTimelineForUser(User $user): PostPaginationDto
    {
        $posts = $this->timelineQueryForUser($user)
            ->orWhere(function ($query) use ($user) {
                $query->whereIn('user_id', $user->followings()->pluck('target_user_id'))
                    ->whereIn('visibility', [Visibility::PUBLIC->value, Visibility::ONLY_AUTHENTICATED->value]);
            })
            ->orderByDesc('published_at')
            ->cursorPaginate(50);

        $mapped = $posts->map(function (Post $post) {
            return $this->postHydrator->modelToDto($post);
        });

        return new PostPaginationDto(
            perPage: $posts->perPage(),
            nextCursor: $posts->nextCursor()?->encode(),
            previousCursor: $posts->previousCursor()?->encode(),
            items: $mapped,
        );
    }

    public function getGlobalTimeline(?User $user = null): PostPaginationDto
    {
        $query = $this->basePostQuery();
        if ($user) {
            $query = $this->timelineQueryForUser($user)
                ->orWhere(function ($query) use ($user) {
                    $query->whereIn('user_id', $user->followings()->pluck('target_user_id'))
                        ->whereIn('visibility', [Visibility::PUBLIC->value, Visibility::ONLY_AUTHENTICATED->value]);
                });
        }
        $posts = $query
            ->orWhereIn('visibility', [Visibility::PUBLIC->value, Visibility::ONLY_AUTHENTICATED->value])
            ->orderByDesc('published_at')
            ->cursorPaginate(50);

        $mapped = $posts->map(function (Post $post) {
            return $this->postHydrator->modelToDto($post);
        });

        return new PostPaginationDto(
            perPage: $posts->perPage(),
            nextCursor: $posts->nextCursor()?->encode(),
            previousCursor: $posts->previousCursor()?->encode(),
            items: $mapped,
        );
    }

    public function getPostsForUserId(string $userId, ?User $visitingUser = null): PostPaginationDto
    {
        $posts = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination', 'hashTags'])
            ->withCount('likes')
            ->where('user_id', $userId);

        if ($visitingUser) {
            $posts->withExists(['likes as liked_by_user' => function ($query) use ($visitingUser) {
                $query->where('user_id', $visitingUser->id);
            }]);

            if ($visitingUser->id !== $userId) {
                // not the owner, show only public posts
                $posts->whereIn('visibility', [Visibility::ONLY_AUTHENTICATED, Visibility::PUBLIC]);
            }
        } else {
            // not logged in, show only public posts
            $posts->where('visibility', Visibility::PUBLIC);
        }

        $posts = $posts
            ->orderByDesc('published_at')
            ->cursorPaginate(50);

        $mapped = $posts->map(function (Post $post) {
            return $this->postHydrator->modelToDto($post);
        });

        return new PostPaginationDto(
            perPage: $posts->perPage(),
            nextCursor: $posts->nextCursor()?->encode(),
            previousCursor: $posts->previousCursor()?->encode(),
            items: $mapped,
        );
    }

    public function internalGetById(string $postId): ?Post
    {
        return $this->basePostQuery()
            ->where('id', $postId)
            ->first();
    }

    public function updateStats(string $postId, int $distance, int $duration): void
    {
        TransportPostModel::where('post_id', $postId)->update(['distance' => $distance, 'duration' => $duration]);
    }

    public function updateTransportGeometry(TransportPostModel $transportPost, ?LineString $geometry): void
    {
        TransportPostModel::where('post_id', $transportPost->post_id)->update(['user_geometry' => $geometry]);
    }

    public function getById(string $postId, ?User $visitingUser = null): BasePost|LocationPost|TransportPost
    {
        $post = $this->basePostQuery()
            ->when($visitingUser, function ($query) use ($visitingUser) {
                $query->withExists(['likes as liked_by_user' => function ($q) use ($visitingUser) {
                    $q->where('user_id', $visitingUser->id);
                }]);
            })
            ->where('id', $postId)
            ->firstOrFail();

        $allowedVisibilities = [Visibility::PUBLIC, Visibility::UNLISTED];
        if ($visitingUser !== null) {
            $allowedVisibilities[] = Visibility::ONLY_AUTHENTICATED;
        }

        if ($visitingUser?->id !== $post->user_id && ! in_array($post->visibility, $allowedVisibilities, true)) {
            abort(403);
        }

        return $this->postHydrator->modelToDto($post, true);
    }

    /**
     * @throws NegativePeriodException
     * @throws Throwable
     */
    public function updateTransportTimes(TransportPost $transportPost, ?string $manualDepartureTime, bool $updateDeparture, ?string $manualArrivalTime, bool $updateArrival): TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::where('id', $transportPost->id)->with('transportPost')->firstOrFail();

            if ($updateDeparture) {
                $post->transportPost->manual_departure = $manualDepartureTime ? Carbon::parse($manualDepartureTime)->toIso8601ZuluString() : null;
            }
            if ($updateArrival) {
                $post->transportPost->manual_arrival = $manualArrivalTime ? Carbon::parse($manualArrivalTime)->toIso8601ZuluString() : null;
            }

            // check that manual arrival is after manual departure
            if ($post->transportPost->manual_departure !== null && $post->transportPost->manual_arrival !== null) {
                $departure = Carbon::parse($post->transportPost->manual_departure);
                $arrival = Carbon::parse($post->transportPost->manual_arrival);
                if ($arrival->lessThanOrEqualTo($departure)) {
                    throw new NegativePeriodException('Manual arrival time must be after manual departure time');
                }
            }
            $post->transportPost->save();

            DB::commit();

            $post->load('transportPost.destination', 'transportPost.origin', 'transportPost.transportTrip', 'transportPost.originStop.location', 'transportPost.destinationStop.location');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }

        return $this->postHydrator->modelToDto($post, true);
    }

    public function delete(BasePost $post): void
    {
        DB::beginTransaction();
        Post::with(['locationPost', 'transportPost'])
            ->where('id', $post->id)
            ->delete();
        DB::commit();
    }

    public function getFilteredPosts(
        User $user,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?array $visibilities = null,
        ?array $travelReasons = null,
        ?array $tags = null
    ): PostPaginationDto {
        $query = Post::with([
            'user',
            'locationPost.location',
            'locationPost.location.tags',
            'transportPost',
            'transportPost.origin',
            'transportPost.destination',
            'transportPost.originStop.location',
            'transportPost.destinationStop.location',
            'transportPost.transportTrip',
            'hashTags',
        ])
            ->where('user_id', $user->id);

        if ($dateFrom) {
            $query->where('published_at', '>=', Carbon::parse($dateFrom)->startOfDay());
        }

        if ($dateTo) {
            $query->where('published_at', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        if (! empty($visibilities)) {
            $query->whereIn('visibility', $visibilities);
        }

        if (! empty($travelReasons)) {
            $query->whereHas('metaInfos', function ($q) use ($travelReasons) {
                $q->where('key', MetaInfoKey::TRAVEL_REASON)
                    ->whereIn('value', $travelReasons);
            });
        }

        if (! empty($tags)) {
            $query->whereHas('hashTags', function ($q) use ($tags) {
                $q->whereIn('value', $tags);
            });
        }

        $posts = $query
            ->orderByDesc('published_at')
            ->cursorPaginate(50);

        $mapped = $posts->map(function (Post $post) {
            return $this->postHydrator->modelToDto($post);
        });

        return new PostPaginationDto(
            perPage: $posts->perPage(),
            nextCursor: $posts->nextCursor()?->encode(),
            previousCursor: $posts->previousCursor()?->encode(),
            items: $mapped,
        );
    }

    /**
     * @return Collection<int,BasePost|LocationPost|TransportPost>
     *
     * @throws Throwable
     */
    public function massEdit(
        User $user,
        array $postIds,
        ?Visibility $visibility = null,
        ?TravelReason $travelReason = null,
        ?array $tags = null,
        bool $addTags = false
    ): Collection {
        try {
            DB::beginTransaction();

            $posts = Post::whereIn('id', $postIds)
                ->where('user_id', $user->id)
                ->get();

            if ($posts->isEmpty()) {
                return collect();
            }

            foreach ($posts as $post) {
                if ($visibility !== null) {
                    $post->visibility = $visibility;
                    $post->save();
                }

                if ($travelReason !== null && ($post->locationPost !== null || $post->transportPost !== null)) {
                    $this->postMetaInfoRepository->setTravelReason($post, $travelReason);
                }

                if ($tags !== null) {
                    if ($addTags) {
                        $existingTags = $post->hashTags->pluck('value')->toArray();
                        $mergedTags = array_unique(array_merge($existingTags, $tags));
                        $this->hashTagRepository->syncHashTagsByValue($post, $mergedTags);
                    } else {
                        $this->hashTagRepository->syncHashTagsByValue($post, $tags);
                    }
                }
            }

            DB::commit();

            return $posts->map(function (Post $post) {
                return $this->postHydrator->modelToDto($post);
            });
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }
    }

    public function getTotalDistanceForUser(string $userId): int
    {
        return Post::where('user_id', $userId)
            ->whereHas('transportPost')
            ->with('transportPost')
            ->get()
            ->pluck('transportPost.distance')
            ->sum();
    }

    public function getTotalDurationForUser(string $userId): int
    {
        return Post::where('user_id', $userId)
            ->whereHas('transportPost')
            ->with('transportPost')
            ->get()
            ->pluck('transportPost.duration')
            ->sum();
    }

    #[ArrayShape(['total' => 'int', 'transport' => 'int', 'location' => 'int', 'text' => 'int'])]
    public function getPostCountsForUser(string $userId): array
    {
        $totalPosts = Post::where('user_id', $userId)->count();
        $transportPosts = Post::where('user_id', $userId)->whereHas('transportPost')->count();
        $locationPosts = Post::where('user_id', $userId)->whereHas('locationPost')->count();
        $textPosts = $totalPosts - $transportPosts - $locationPosts;

        return [
            'total' => $totalPosts,
            'transport' => $transportPosts,
            'location' => $locationPosts,
            'text' => $textPosts,
        ];
    }

    public function getVisitedLocationsForUser(string $userId): int
    {
        return Post::where('user_id', $userId)
            ->whereHas('locationPost')
            ->with('locationPost')
            ->get()
            ->pluck('locationPost.location_id')
            ->unique()
            ->count();
    }
}
