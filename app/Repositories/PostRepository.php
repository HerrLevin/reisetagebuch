<?php

namespace App\Repositories;

use App\Dto\PostPaginationDto;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\PostHydrator;
use App\Jobs\TraewellingCrossCheckInJob;
use App\Models\Location;
use App\Models\Post;
use App\Models\TransportTrip;
use App\Models\TransportTripStop;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Throwable;

class PostRepository
{
    private PostHydrator $postHydrator;

    public function __construct(?PostHydrator $postHydrator = null)
    {
        $this->postHydrator = $postHydrator ?? new PostHydrator;
    }

    public function storeLocation(User $user, Location $location, ?string $body = null): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
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

    public function storeText(User $user, string $body): BasePost|LocationPost|TransportPost
    {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
            ]);
            DB::commit();

        } catch (Throwable $e) {
            DB::rollBack();
            report($e);
        }

        return $this->postHydrator->modelToDto($post);
    }

    public function storeTransport(
        User $user,
        TransportTrip $transportTrip,
        TransportTripStop $originStop,
        TransportTripStop $destinationStop,
        ?string $body = null
    ): BasePost|LocationPost|TransportPost {
        try {
            DB::beginTransaction();
            /** @var Post $post */
            $post = Post::create([
                'user_id' => $user->id,
                'body' => $body,
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

    public function getPostsForUser(User|string $user): PostPaginationDto
    {
        if ($user instanceof User) {
            $user = $user->id;
        }

        $posts = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('user_id', $user)
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

    public function getById(string $postId): BasePost|LocationPost|TransportPost
    {
        $post = Post::with(['user', 'locationPost.location', 'locationPost.location.tags', 'transportPost', 'transportPost.origin', 'transportPost.destination'])
            ->where('id', $postId)
            ->firstOrFail();

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
