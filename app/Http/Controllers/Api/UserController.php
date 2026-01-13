<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Clickbar\Magellan\Data\Geometries\GeometryCollection;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;

class UserController extends Controller
{
    private \App\Http\Controllers\Backend\UserController $userController;

    public function __construct(\App\Http\Controllers\Backend\UserController $userController)
    {
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
}
