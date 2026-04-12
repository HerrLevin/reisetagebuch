<?php

namespace App\Http\Controllers\Api;

use App\Dto\ImprintDto;
use App\Http\Controllers\Backend\ImprintBackend;
use App\Http\Requests\ImprintUpdateRequest;
use OpenApi\Attributes as OA;

class ImprintController extends Controller
{
    private ImprintBackend $backend;

    public function __construct(ImprintBackend $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Get(
        path: '/app/imprint',
        operationId: 'getImprint',
        summary: 'Get the instance imprint',
        tags: ['App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: ImprintDto::class)
            ),
        ]
    )]
    public function show()
    {
        return $this->backend->show();
    }

    #[OA\Patch(
        path: '/app/imprint',
        operationId: 'updateImprint',
        description: 'Update the instance imprint. Requires admin privileges.',
        summary: 'Update the instance imprint',
        security: [['passport' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: ImprintUpdateRequest::class)),
        tags: ['App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: ImprintDto::class)
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function update(ImprintUpdateRequest $request)
    {
        return $this->backend->update($request);
    }
}
