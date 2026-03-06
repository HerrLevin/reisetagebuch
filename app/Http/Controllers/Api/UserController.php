<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserDto;
use App\Models\Location;
use App\Models\User;
use Clickbar\Magellan\Data\Geometries\GeometryCollection;
use Clickbar\Magellan\IO\Generator\Geojson\GeojsonGenerator;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    private \App\Http\Controllers\Backend\UserController $userController;

    public function __construct(\App\Http\Controllers\Backend\UserController $userController)
    {
        parent::__construct();
        $this->userController = $userController;
    }

    #[OA\Get(
        path: '/users/{userId}/map-data',
        operationId: 'getProfileMapData',
        description: 'Return GeoJSON map data for a user',
        summary: 'Profile map data',
        tags: ['Profile'],
        parameters: [new OA\Parameter(name: 'userId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'object'))]
    )]
    public function mapData(string $userId)
    {
        $locations = Location::join('location_posts', 'locations.id', '=', 'location_posts.location_id')
            ->join('posts', 'location_posts.post_id', '=', 'posts.id')
            ->where('posts.user_id', $userId)
            ->select('locations.location')
            ->get();

        $locations = GeometryCollection::make($locations->pluck('location')->toArray());

        return new GeojsonGenerator()->generateGeometryCollection($locations); // todo: document response format
    }

    #[OA\Get(
        path: '/profile/{username}',
        operationId: 'getProfile',
        description: 'Return profile data for a user',
        summary: 'Get profile',
        tags: ['Profile'],
        parameters: [new OA\Parameter(name: 'username', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: UserDto::class))]
    )]
    public function show(string $username): UserDto
    {
        return $this->userController->show($username);
    }

    #[OA\Patch(
        path: '/account/profile',
        operationId: 'updateProfile',
        description: 'Update profile for authenticated user',
        summary: 'Update profile',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(ref: UpdateProfileRequest::class)
            )
        ),
        tags: ['Profile'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: UserDto::class))]
    )]
    public function update(UpdateProfileRequest $request): UserDto
    {
        /** @var User $user */
        $user = $this->auth->user();

        return $this->userController->update($request, $user);
    }

    #[OA\Post(
        path: '/account/profile/avatar',
        operationId: 'updateAvatar',
        description: 'Update avatar for authenticated user',
        summary: 'Update avatar',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: ImageUploadRequest::class)
            )
        ),
        tags: ['Profile'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: UserDto::class))],
    )]
    public function updateAvatar(ImageUploadRequest $request): UserDto
    {
        /** @var User $user */
        $user = $this->auth->user();

        return $this->userController->updateAvatar($request, $user);
    }

    #[OA\Delete(
        path: '/account/profile/avatar',
        operationId: 'deleteAvatar',
        description: 'Delete avatar for authenticated user',
        summary: 'Delete avatar',
        tags: ['Profile'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_SUCCESS)],
    )]
    public function deleteAvatar(): void
    {
        /** @var User $user */
        $user = $this->auth->user();

        $this->userController->deleteAvatar($user);
    }

    #[OA\Post(
        path: '/account/profile/header',
        operationId: 'updateHeader',
        description: 'Update header for authenticated user',
        summary: 'Update header',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: ImageUploadRequest::class)
            )
        ),
        tags: ['Profile'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: UserDto::class))],
    )]
    public function updateHeader(ImageUploadRequest $request): UserDto
    {
        /** @var User $user */
        $user = $this->auth->user();

        return $this->userController->updateHeader($request, $user);
    }

    #[OA\Delete(
        path: '/account/profile/header',
        operationId: 'deleteHeader',
        description: 'Delete header for authenticated user',
        summary: 'Delete header',
        tags: ['Profile'],
        responses: [
            new OA\Response(response: 204, description: Controller::OA_DESC_SUCCESS),
        ]
    )]
    public function deleteHeader(): void
    {
        /** @var User $user */
        $user = $this->auth->user();

        $this->userController->deleteHeader($user);
    }
}
