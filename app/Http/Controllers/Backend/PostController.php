<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\TripDto;
use App\Dto\PostPaginationDto;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\Visibility;
use App\Exceptions\OriginAfterDestinationException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BasePostRequest;
use App\Http\Requests\FilterPostsRequest;
use App\Http\Requests\LocationBasePostRequest;
use App\Http\Requests\MassEditPostRequest;
use App\Http\Requests\TransportBasePostCreateRequest;
use App\Http\Requests\TransportPostUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\DbTripHydrator;
use App\Jobs\PrefetchJob;
use App\Jobs\TraewellingChangeExitJob;
use App\Jobs\TraewellingCrossCheckInJob;
use App\Jobs\TraewellingDeletePostJob;
use App\Jobs\TraewellingEditPostJob;
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

    public function storeLocation(LocationBasePostRequest $request): BasePost|LocationPost|TransportPost
    {
        $location = $this->locationRepository->getLocationById($request->input('location'));

        return $this->postRepository->storeLocation(
            $request->user(),
            $location,
            Visibility::from($request->input('visibility')),
            $request->input('body'),
            $request->input('tags', []),
            TravelReason::from($request->input('travelReason'))
        );
    }

    public function storeText(BasePostRequest $request): BasePost
    {
        return $this->postRepository->storeText(
            $request->user(),
            Visibility::from($request->input('visibility')),
            $request->input('body'),
            $request->input('tags', [])
        );
    }

    public function storeMotisTransport(TransportBasePostCreateRequest $request): BasePost|LocationPost|TransportPost
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
            $request->body,
            $request->input('tags', []),
            TravelReason::from($request->input('travelReason'))
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
    public function updatePost(string $postId, BasePostRequest $request): BasePost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        $this->authorize('update', $post);

        $reason = null;
        if ($post instanceof LocationPost || $post instanceof TransportPost) {
            $reason = TravelReason::from($request->input('travelReason'));
        }

        $post = $this->postRepository->updateBasePost(
            $post,
            Visibility::from($request->input('visibility')),
            $request->input('body'),
            $request->input('tags', []),
            $reason
        );

        if ($post instanceof TransportPost) {
            TraewellingEditPostJob::dispatch($post);
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
            TraewellingDeletePostJob::dispatch($post);
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

        TraewellingEditPostJob::dispatch($post);

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

        $reason = $request->input('reason') !== null ? TravelReason::tryFrom($request->input('reason')) : null;

        try {
            $post = $this->postRepository->updateTransportPost(
                $post,
                $request->stopId,
                $reason ?? $post->travelReason ?? TravelReason::LEISURE
            );
        } catch (OriginAfterDestinationException) {
            abort(422, 'Origin stop must be before destination stop');
        } catch (StationNotOnTripException) {
            abort(422, 'Stop not on trip');
        }

        TraewellingChangeExitJob::dispatch($post);

        return $post;
    }

    public function filter(FilterPostsRequest $request): PostPaginationDto
    {
        $user = Auth::user();

        return $this->postRepository->getFilteredPosts(
            $user,
            $request->input('dateFrom'),
            $request->input('dateTo'),
            $request->input('visibility'),
            $request->input('travelReason'),
            $request->input('tags')
        );
    }

    public function massEdit(MassEditPostRequest $request): array
    {
        $visibility = $request->input('visibility') !== null ? Visibility::tryFrom($request->input('visibility')) : null;
        $travelReason = $request->input('travelReason') !== null ? TravelReason::tryFrom($request->input('travelReason')) : null;
        $tags = $request->input('tags');
        $addTags = $request->boolean('addTags', false);

        $updated = $this->postRepository->massEdit(
            $request->user(),
            $request->input('postIds'),
            $visibility,
            $travelReason,
            $tags,
            $addTags
        );

        foreach ($updated as $post) {
            if ($post instanceof TransportPost) {
                TraewellingEditPostJob::dispatch($post);
            }
        }

        return [
            'success' => true,
            'updatedCount' => count($updated),
        ];
    }
}
