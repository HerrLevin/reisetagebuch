<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\PostPaginationDto;
use App\Enums\PostMetaInfo\TravelReason;
use App\Enums\PostMetaInfo\TravelRole;
use App\Enums\Visibility;
use App\Exceptions\NegativePeriodException;
use App\Exceptions\OriginAfterDestinationException;
use App\Exceptions\StationNotOnTripException;
use App\Http\Controllers\Controller;
use App\Http\Requests\BasePostRequest;
use App\Http\Requests\FilterPostsRequest;
use App\Http\Requests\LocationBasePostRequest;
use App\Http\Requests\MassEditPostRequest;
use App\Http\Requests\TransportBasePostCreateRequest;
use App\Http\Requests\TransportPostExitUpdateRequest;
use App\Http\Requests\TransportTimesUpdateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
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
use Throwable;

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
            TravelReason::from($request->input('travelReason')),
            $request->input('vehicleIds', []),
            $request->input('metaTripId'),
            ! empty($request->input('travelRole')) ? TravelRole::tryFrom($request->input('travelRole')) : null
        );

        TraewellingCrossCheckInJob::dispatch($post->id);
        PrefetchJob::dispatch($stopStopover->location->location);

        return $post;
    }

    public function dashboard(User $user): PostPaginationDto
    {
        return $this->postRepository->getDashboardForUser($user);
    }

    public function postsForUser(string $userId, ?User $visitingUser = null): PostPaginationDto
    {
        return $this->postRepository->getPostsForUserId($userId, $visitingUser);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(string $postId, ?User $visitingUser = null): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, $visitingUser);

        if ($visitingUser?->can('view', $post)) {
            return $post;
        }

        throw new AuthorizationException('This post is not visible to you.');
    }

    /**
     * @throws AuthorizationException
     */
    public function updatePost(string $postId, BasePostRequest $request, User $visitingUser): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        if ($visitingUser->cannot('update', $post)) {
            throw new AuthorizationException('You do not have permission to update this post.');
        }

        $reason = null;
        if ($post instanceof LocationPost || $post instanceof TransportPost) {
            $reason = TravelReason::from($request->input('travelReason'));
        }

        $post = $this->postRepository->updateBasePost(
            $post,
            Visibility::from($request->input('visibility')),
            $request->input('body'),
            $request->input('tags', []),
            $reason,
            $request->input('vehicleIds'),
            $request->input('metaTripId'),
            ! empty($request->input('travelRole')) ? TravelRole::tryFrom($request->input('travelRole')) : null
        );

        if ($post instanceof TransportPost) {
            TraewellingEditPostJob::dispatch($post);
        }

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $postId, User $user): void
    {
        $post = $this->postRepository->getById($postId, $user);
        if ($user->cannot('delete', $post)) {
            throw new AuthorizationException('You do not have permission to delete this post.');
        }

        if ($post instanceof TransportPost) {
            TraewellingDeletePostJob::dispatch($post);
        }

        $this->postRepository->delete($post);
    }

    /**
     * @throws AuthorizationException
     * @throws NegativePeriodException
     * @throws Throwable
     */
    public function updateTimesTransport(string $postId, TransportTimesUpdateRequest $request, User $user): TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        if ($user->cannot('update', $post)) {
            throw new AuthorizationException('You do not have permission to update this post.');
        }

        if (! $post instanceof TransportPost) {
            abort(422, 'Not a transport post');
        }

        $post = $this->postRepository->updateTransportTimes(
            $post,
            $request->manualDepartureTime,
            $request->has('manualDepartureTime'),
            $request->manualArrivalTime,
            $request->has('manualArrivalTime')
        );

        TraewellingEditPostJob::dispatch($post);

        return $post;
    }

    /**
     * @throws AuthorizationException
     */
    public function updateTransportPostExit(string $postId, TransportPostExitUpdateRequest $request, User $user): BasePost|LocationPost|TransportPost
    {
        $post = $this->postRepository->getById($postId, Auth::user());
        if ($user->cannot('update', $post)) {
            throw new AuthorizationException('You do not have permission to update this post.');
        }

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

    public function filter(FilterPostsRequest $request, User $user): PostPaginationDto
    {
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
