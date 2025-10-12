<?php

namespace App\Http\Controllers\Inertia;

use App\Http\Controllers\Backend\PostController;
use App\Http\Controllers\Backend\UserController as BackendUserController;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Location;
use Auth;
use Clickbar\Magellan\Data\Geometries\GeometryCollection;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;
use Request;

class UserController extends Controller
{
    private BackendUserController $userController;

    private PostController $postController;

    public function __construct(BackendUserController $userController, PostController $postController)
    {
        $this->postController = $postController;
        $this->userController = $userController;
    }

    public function show(string $username, Request $request): Response|ResponseFactory
    {
        $user = $this->userController->show($username);
        $posts = $this->postController->postsForUser($user->id, Auth::user());

        return inertia('Profile/Show', [
            'posts' => Inertia::merge($posts->items),
            'nextCursor' => $posts->nextCursor,
            'previousCursor' => $posts->previousCursor,
            'user' => $user,
        ]);
    }

    public function showMap(string $username): Response|ResponseFactory
    {
        $user = $this->userController->show($username);

        return inertia('Profile/ShowMap', [
            'user' => $user,
        ]);
    }

    public function mapData(string $username, Request $request)
    {
        $user = $this->userController->show($username);

        $locations = Location::join('location_posts', 'locations.id', '=', 'location_posts.location_id')
            ->join('posts', 'location_posts.post_id', '=', 'posts.id')
            ->where('posts.user_id', $user->id)
            ->select('locations.location')
            ->get();

        $locations = GeometryCollection::make($locations->pluck('location')->toArray());
        $geojson = new GeojsonGenerator()->generateGeometryCollection($locations);

        return response()->json($geojson);
    }

    public function update(UpdateProfileRequest $username): RedirectResponse|Response|ResponseFactory
    {
        $user = $this->userController->update($username);

        if (route('profile.show', $user->username) !== url()->previous()) {
            return redirect()->route('profile.show', $user->username);
        }

        return inertia('Profile/Show', [
            'posts' => $this->postController->postsForUser($user->id),
            'user' => $user,
        ]);
    }
}
