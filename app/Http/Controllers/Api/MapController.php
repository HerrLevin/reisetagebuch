<?php

namespace App\Http\Controllers\Api;

use App\Dto\ErrorDto;
use App\Http\Controllers\Backend\MapController as BackendMapController;
use App\Http\Requests\FromToRequest;
use Clickbar\Magellan\Data\Geometries\MultiPoint;
use OpenApi\Attributes as OA;

class MapController extends Controller
{
    private BackendMapController $locationController;

    public function __construct(BackendMapController $locationController)
    {
        parent::__construct();
        $this->locationController = $locationController;
    }

    #[OA\Get(
        path: '/map/linestring',
        operationId: 'getLineStringBetween',
        description: 'Get a LineString geometry between two locations',
        summary: 'Get linestring',
        tags: ['Map'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'object')), // todo: define return type
            new OA\Response(response: 400, description: 'Invalid stops provided', content: new OA\JsonContent(ref: ErrorDto::class)),
        ]
    )]
    public function getLineStringBetween(FromToRequest $request)
    {
        $linestring = $this->locationController->fromTo($request->from, $request->to);

        if (! $linestring) {
            abort(400, new ErrorDto('Invalid stops provided'));
        }

        return $linestring; // todo: define return type
    }

    #[OA\Get(
        path: '/map/stopovers',
        operationId: 'getStopsBetween',
        description: 'Get stop points between two locations as MultiPoint geometry',
        summary: 'Get stopovers',
        tags: ['Map'],
        parameters: [
            new OA\Parameter(name: 'from', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'to', in: 'query', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(type: 'object'))] // todo: define return type)]
    )]
    public function getStopsBetween(FromToRequest $request): ?MultiPoint
    {
        return $this->locationController->stopPointGeometryFromTo($request->from, $request->to); // todo: define return type
    }
}
