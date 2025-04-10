<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCreateRequest;
use App\Models\Post;
use App\Models\User;
use App\Repositories\LocationRepository;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class PostController extends Controller
{
    private PostRepository $postRepository;
    private LocationRepository $locationRepository;

    public function __construct(PostRepository $postRepository, LocationRepository $locationRepository)
    {
        $this->locationRepository = $locationRepository;
        $this->postRepository = $postRepository;
    }

    public function store(PostCreateRequest $request): Post {
        $location = $this->locationRepository->getLocationById($request->input('location'));

        return $this->postRepository->store(
            $request->user(),
            $location,
            $request->input('body')
        );
    }

    public function dashboard(User $user): Collection
    {
        return $this->postRepository->dashboard($user);
    }
}
