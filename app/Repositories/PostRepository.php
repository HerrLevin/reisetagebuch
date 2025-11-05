<?php

namespace App\Repositories;

use App\Dto\PostPaginationDto;
use App\Enums\PostMetaInfo\MetaInfoKey;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Exceptions\OriginAfterDestinationException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\PostHydrator;
use App\Models\Location;
use App\Models\Post;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_REASON, $travelReason->value);

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
        ?TravelReason $travelReason = null
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
                $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_REASON, $travelReason->value);
            }

            DB::commit();

            $post->load('locationPost', 'transportPost', 'hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
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
            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_REASON, $travelReason->value);
            DB::commit();

            $post->load('transportPost.destination', 'transportPost.origin', 'transportPost.transportTrip', 'transportPost.originStop.location', 'transportPost.destinationStop.location');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function storeTransport(
        User $user,
        TransportTrip $transportTrip,
        TransportTripStop $originStop,
        TransportTripStop $destinationStop,
        Visibility $visibility,
        ?string $body = null,
        array $hashTagIds = [],
        TravelReason $travelReason = TravelReason::LEISURE
    ): BasePost|LocationPost|TransportPost {
        try {
            $publishedAt = Carbon::now();
            if ($publishedAt < $originStop->departure_time) {
                $publishedAt = $originStop->departure_time->subMinutes(10);
            }

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

            $this->postMetaInfoRepository->updateOrCreateMetaInfo($post, MetaInfoKey::TRAVEL_REASON, $travelReason->value);

            DB::commit();

            $post->load('transportPost', 'hashTags');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function getDashboardForUser(User $user): PostPaginationDto
    {
        $posts = Post::with([
            'user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination',
            'transportPost.originStop.location', 'transportPost.destinationStop.location', 'transportPost.transportTrip', 'hashTags',
        ])
            ->where('user_id', '=', $user->id)
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

    public function getPostsForUser(User|string $user, ?User $visitingUser = null): PostPaginationDto
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $posts = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination', 'hashTags'])
            ->where('user_id', $user);

        if ($visitingUser && $visitingUser->id !== $user) {
            // not the owner, show only public posts
            $posts->whereIn('visibility', [Visibility::ONLY_AUTHENTICATED, Visibility::PUBLIC]);
        } elseif (! $visitingUser) {
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

    public function getById(string $postId, ?User $visitingUser = null): BasePost|LocationPost|TransportPost
    {
        $post = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination', 'hashTags'])
            ->where('id', $postId)
            ->firstOrFail();

        $allowedVisibilities = [Visibility::PUBLIC, Visibility::UNLISTED];
        if ($visitingUser !== null) {
            $allowedVisibilities[] = Visibility::ONLY_AUTHENTICATED;
        }

        if ($visitingUser?->id !== $post->user_id && ! in_array($post->visibility, $allowedVisibilities, true)) {
            abort(403);
        }

        return $this->postHydrator->modelToDto($post);
    }

    /**
     * @throws Throwable
     */
    public function updateTransportTimes(TransportPost $transportPost, ?string $manualDepartureTime, ?string $manualArrivalTime): TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::where('id', $transportPost->id)->with('transportPost')->firstOrFail();

            $post->transportPost->manual_departure = $manualDepartureTime ? Carbon::parse($manualDepartureTime)->setSeconds(0)->toIso8601ZuluString() : null;
            $post->transportPost->manual_arrival = $manualArrivalTime ? Carbon::parse($manualArrivalTime)->setSeconds(0)->toIso8601ZuluString() : null;
            $post->transportPost->save();

            DB::commit();

            $post->load('transportPost.destination', 'transportPost.origin', 'transportPost.transportTrip', 'transportPost.originStop.location', 'transportPost.destinationStop.location');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
            throw $e;
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function delete(BasePost $post): void
    {
        DB::beginTransaction();
        Post::with(['locationPost', 'transportPost'])
            ->where('id', $post->id)
            ->delete();
        DB::commit();
    }
}
