<?php

namespace App\Http\Controllers\Api;

use App\Dto\TripCreationResponseDto;
use App\Http\Controllers\Backend\TripController as BackendTripController;
use App\Http\Requests\StoreTripRequest;
use OpenApi\Attributes as OA;

class TripController extends Controller
{
    private BackendTripController $tripController;

    public function __construct(BackendTripController $tripController)
    {
        parent::__construct();
        $this->tripController = $tripController;
    }

    #[OA\Post(
        path: '/trips',
        operationId: 'storeTrip',
        description: 'Create a new trip',
        summary: 'Store trip',
        security: [
            [
                'oauth2_security_example' => ['write:projects', 'read:projects'],
            ],
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: StoreTripRequest::class)
        ),
        tags: ['Trips'],
        responses: [
            new OA\Response(response: 201, description: Controller::OA_DESC_SUCCESS, content: new OA\JsonContent(ref: TripCreationResponseDto::class)),
            new OA\Response(response: 422, description: 'Validation or processing error'),
        ]
    )]
    public function store(StoreTripRequest $request): TripCreationResponseDto
    {
        try {
            $trip = $this->tripController->store($request);
            $origin = $trip->stops->first()->location;

            return new TripCreationResponseDto(
                tripId: $trip->foreign_trip_id,
                startId: $origin->id,
                startTime: $request->departureTime,
            );
        } catch (\Throwable $e) {
            abort(422, [
                'success' => false,
                'message' => 'Could not create trip: '.$e->getMessage(),
            ]);
        }
    }
}
