<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\StopDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\TransportPostCreateRequest;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Models\User;
use App\Repositories\LocationRepository;
use App\Repositories\PostRepository;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;

class PostController extends Controller
{
    private PostRepository $postRepository;
    private LocationRepository $locationRepository;
    private TransitousRequestService $transitousRequestService;

    public function __construct(PostRepository $postRepository, LocationRepository $locationRepository, TransitousRequestService $transitousRequestService)
    {
        $this->locationRepository = $locationRepository;
        $this->postRepository = $postRepository;
        $this->transitousRequestService = $transitousRequestService;
    }

    public function store(PostCreateRequest $request): BasePost|LocationPost|TransportPost
    {
        $location = $this->locationRepository->getLocationById($request->input('location'));

        return $this->postRepository->store(
            $request->user(),
            $location,
            $request->input('body')
        );
    }

    public function storeMotisTransport(TransportPostCreateRequest $request): BasePost|LocationPost|TransportPost
    {
        $trip = $this->transitousRequestService->getStopTimes($request->tripId);

        $stopovers = [$trip->legs[0]->from, ...$trip->legs[0]->intermediateStops, $trip->legs[0]->to];
        $start = null;
        $stop = null;
        /** @var StopDto $stopover */
        foreach ($stopovers as $stopover) {
            // todo: fix ring lines
            if ($stopover->stopId === $request->startId) {
                $start = $stopover;
            }
            if ($stopover->stopId === $request->stopId) {
                $stop = $stopover;
            }
        }

        if ($start === null || $stop === null) {
            abort(422, 'Invalid stopover');
        }

        $startLocation = $this->locationRepository->getOrCreateLocationByIdentifier(
            $start->name,
            $start->latitude,
            $start->longitude,
            $start->stopId,
            'stop',
            'motis'
        );

        $stopLocation = $this->locationRepository->getOrCreateLocationByIdentifier(
            $stop->name,
            $stop->latitude,
            $stop->longitude,
            $stop->stopId,
            'stop',
            'motis'
        );


        return $this->postRepository->storeTransport(
            $request->user(),
            $startLocation,
            Carbon::parse($request->startTime),
            $stopLocation,
            Carbon::parse($request->stopTime),
            $trip->legs[0]->mode,
            $trip->legs[0]->routeShortName,
            $request->body
        );
    }

    public function dashboard(User $user): Collection
    {
        return $this->postRepository->getDashboardForUser($user);
    }

    public function postsForUser(User|string $user): Collection
    {
        return $this->postRepository->getPostsForUser($user);
    }

    public function show(string $postId): BasePost|LocationPost|TransportPost
    {
        return $this->postRepository->getById($postId);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(string $postId): void
    {
        $post = $this->postRepository->getById($postId);

        $this->authorize('delete', $post);

        $this->postRepository->delete($post);
    }
}
