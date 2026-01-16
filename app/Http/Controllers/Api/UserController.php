<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserDto;
use App\Models\Location;
use Clickbar\Magellan\Data\Geometries\GeometryCollection;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;

class UserController extends Controller
{
    private \App\Http\Controllers\Backend\UserController $userController;

    public function __construct(\App\Http\Controllers\Backend\UserController $userController)
    {
        parent::__construct();
        $this->userController = $userController;
    }

    public function mapData(string $username)
    {
        $user = $this->userController->show($username);

        $locations = Location::join('location_posts', 'locations.id', '=', 'location_posts.location_id')
            ->join('posts', 'location_posts.post_id', '=', 'posts.id')
            ->where('posts.user_id', $user->id)
            ->select('locations.location')
            ->get();

        $locations = GeometryCollection::make($locations->pluck('location')->toArray());

        return new GeojsonGenerator()->generateGeometryCollection($locations);
    }

    public function show(string $username)
    {
        return $this->userController->show($username);
    }

    public function update(UpdateProfileRequest $request): UserDto
    {
        return $this->userController->update($request, $this->auth->user());
    }
}
