<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Dto\MotisApi\StopDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\TransportPostCreateRequest;
use App\Models\Post;
use App\Models\User;
use App\Repositories\LocationRepository;
use App\Repositories\PostRepository;
use App\Services\TransitousRequestService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;

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

    public function store(PostCreateRequest $request): Post
    {
        $location = $this->locationRepository->getLocationById($request->input('location'));

        return $this->postRepository->store(
            $request->user(),
            $location,
            $request->input('body')
        );
    }

    public function storeMotisTransport(TransportPostCreateRequest $request): Post
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
        return $this->postRepository->dashboard($user);
    }

    public function show(string $postId): Post
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
