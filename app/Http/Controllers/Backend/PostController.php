<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\PostPaginationDto;
use App\Enums\Visibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationPostRequest;
use App\Http\Requests\PostRequest;
use App\Http\Requests\TransportPostCreateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Jobs\PrefetchJob;
use App\Jobs\TraewellingCrossCheckInJob;
use App\Models\TransportTripStop;
use App\Models\User;
use App\Repositories\LocationRepository;
use App\Repositories\PostRepository;
use App\Repositories\TransportTripRepository;
use Auth;
use Illuminate\Auth\Access\AuthorizationException;

class PostController extends Controller
{
    private PostRepository $postRepository;

    private LocationRepository $locationRepository;

    private TransportTripRepository $transportTripRepository;

    public function __construct(
        PostRepository $postRepository,
        LocationRepository $locationRepository,
        TransportTripRepository $transportTripRepository
    ) {
        $this->locationRepository = $locationRepository;
        $this->postRepository = $postRepository;
        $this->transportTripRepository = $transportTripRepository;
    }

    public function storeLocation(LocationPostRequest $request): BasePost|LocationPost|TransportPost
    {
        $location = $this->locationRepository->getLocationById($request->input('location'));

        return $this->postRepository->storeLocation(
            $request->user(),
            $location,
            Visibility::from($request->input('visibility')),
            $request->input('body')
        );
    }

    public function storeText(PostRequest $request): BasePost
    {
        return $this->postRepository->storeText(
            $request->user(),
            Visibility::from($request->input('visibility')),
            $request->input('body'),
        );
    }

    public function storeMotisTransport(TransportPostCreateRequest $request): BasePost|LocationPost|TransportPost
    {
        $trip = $this->transportTripRepository->getTripByIdentifier(
            $request->tripId,
            null,
            ['stops', 'stops.location.identifiers']
        );

        $startLocation = $this->locationRepository->getLocationByIdentifier($request->startId, 'stop', 'motis') ??
            $this->locationRepository->getLocationById($request->startId);
        $stopLocation = $this->locationRepository->getLocationById($request->stopId) ??
            $this->locationRepository->getLocationByIdentifier($request->stopId, 'stop', 'motis');

        $startStopover = null;
        $stopStopover = null;
        /** @var TransportTripStop $stopover */
        foreach ($trip->stops as $stopover) {
            // todo: fix ring lines
            if ($stopover->location_id === $startLocation->id) {
                $startStopover = $stopover;
            }
            if ($stopover->location_id === $stopLocation->id) {
                $stopStopover = $stopover;
            }
        }

        if ($startStopover === null || $stopStopover === null) {
            abort(422, 'Invalid stopover');
        }

        $post = $this->postRepository->storeTransport(
            $request->user(),
            $trip,
            $startStopover,
            $stopStopover,
            Visibility::from($request->input('visibility')),
            $request->body
        );

        TraewellingCrossCheckInJob::dispatch($post->id);
        PrefetchJob::dispatch($stopStopover->location->location);

        return $post;
    }

    public function dashboard(User $user): PostPaginationDto
    {
        return $this->postRepository->getDashboardForUser($user);
    }

    public function postsForUser(User|string $user, ?User $visitingUser = null): PostPaginationDto
    {
        return $this->postRepository->getPostsForUser($user, $visitingUser);
    }

    public function show(string $postId): BasePost|LocationPost|TransportPost
    {
        return $this->postRepository->getById($postId, Auth::user());
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $postId): void
    {
        $post = $this->postRepository->getById($postId, Auth::user());

        $this->authorize('delete', $post);

        $this->postRepository->delete($post);
    }
}
