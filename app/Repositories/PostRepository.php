<?php

namespace App\Repositories;

use App\Dto\PostPaginationDto;
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

    public function __construct(?PostHydrator $postHydrator = null)
    {
        $this->postHydrator = $postHydrator ?? new PostHydrator;
    }

    public function storeLocation(User $user, Location $location, Visibility $visibility, ?string $body = null): BasePost|LocationPost|TransportPost
    {
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
            DB::commit();

            $post->load('locationPost');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function storeText(User $user, Visibility $visibility, string $body): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);
            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function updateBasePost(BasePost $basePost, ?Visibility $visibility, ?string $body): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::where('id', $basePost->id)->firstOrFail();

            $post->body = $body;
            if ($visibility !== null) {
                $post->visibility = $visibility;
            }
            $post->save();
            DB::commit();

            $post->load('locationPost', 'transportPost');
        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function updateTransportPost(TransportPost $transportPost, string $stopId): TransportPost
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
        ?string $body = null
    ): BasePost|LocationPost|TransportPost {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
                'visibility' => $visibility,
            ]);

            // create transport post
            $post->transportPost()->create([
                'transport_trip_id' => $transportTrip->id,
                'origin_stop_id' => $originStop->id,
                'destination_stop_id' => $destinationStop->id,
                'departure' => now(),
                'arrival' => now(),
                'mode' => 'lol',
            ]);
            DB::commit();

            $post->load('transportPost');
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
            'transportPost.originStop.location', 'transportPost.destinationStop.location', 'transportPost.transportTrip',
        ])
            ->where('user_id', '=', $user->id)
            ->orWhereIn('visibility', [Visibility::PUBLIC->value, Visibility::ONLY_AUTHENTICATED->value])
            ->latest()
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

        $posts = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('user_id', $user);

        if ($visitingUser && $visitingUser->id !== $user) {
            // not the owner, show only public posts
            $posts->whereIn('visibility', [Visibility::ONLY_AUTHENTICATED, Visibility::PUBLIC]);
        } elseif (! $visitingUser) {
            // not logged in, show only public posts
            $posts->where('visibility', Visibility::PUBLIC);
        }

        $posts = $posts
            ->latest()
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
        $post = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
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
