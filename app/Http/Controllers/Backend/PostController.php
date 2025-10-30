<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\TripDto;
use App\Dto\PostPaginationDto;
use App\Enums\Visibility;
use App\Exceptions\OriginAfterDestinationException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LocationPostRequest;
use App\Http\Requests\PostRequest;
use App\Http\Requests\TransportPostCreateRequest;
use App\Http\Requests\TransportPostUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\DbTripHydrator;
use App\Jobs\DeletePostInTraewellingJob;
use App\Jobs\EditTraewellingPostJob;
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

    /**
     * @throws AuthorizationException
     */
    public function show(string $postId): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());

        $this->authorize('view', $post);

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function edit(string $postId): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function updatePost(string $postId, PostRequest $request): BasePost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        $post = $this->postRepository->updateBasePost(
            $post,
            Visibility::from($request->input('visibility')),
            $request->input('body'),
        );

        if ($post instanceof TransportPost) {
            EditTraewellingPostJob::dispatch($post);
        }

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $postId): void
    {
        $post = $this->postRepository->getById($postId, Auth::user());

        $this->authorize('delete', $post);

        if ($post instanceof TransportPost) {
            DeletePostInTraewellingJob::dispatch($post);
        }

        $this->postRepository->delete($post);
    }

    /**
     * @throws AuthorizationException
     */
    public function editTransport(string $postId): TripDto
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        if (! $post instanceof TransportPost) {
            abort(422, 'Not a transport post');
        }

        $trip = $this->transportTripRepository->getTripById(
            $post->trip->id,
            ['stops', 'stops.location.identifiers']
        );

        // remove everything before $post->originStop
        $filteredStops = collect();
        $foundOrigin = false;
        foreach ($trip->stops as $stop) {
            if ($stop->id === $post->originStop->id) {
                $foundOrigin = true;
            }
            if ($foundOrigin) {
                $filteredStops->push($stop);
            }
        }

        $hydrator = new DbTripHydrator;

        return $hydrator->hydrateTrip($trip, $filteredStops);
    }

    /**
     * @throws AuthorizationException
     */
    public function editTimesTransport(string $postId): TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        if (! $post instanceof TransportPost) {
            abort(422, 'Not a transport post');
        }

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTimesTransport(string $postId, TransportTimesUpdateRequest $request): TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        if (! $post instanceof TransportPost) {
            abort(422, 'Not a transport post');
        }

        $post = $this->postRepository->updateTransportTimes(
            $post,
            $request->manualDepartureTime,
            $request->manualArrivalTime
        );

        EditTraewellingPostJob::dispatch($post);

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTransport(string $postId, TransportPostUpdateRequest $request): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        if (! $post instanceof TransportPost) {
            abort(422, 'Not a transport post');
        }

        try {
            return $this->postRepository->updateTransportPost(
                $post,
                $request->stopId
            );
        } catch (OriginAfterDestinationException) {
            abort(422, 'Origin stop must be before destination stop');
        } catch (StationNotOnTripException) {
            abort(422, 'Stop not on trip');
        }
    }
}
