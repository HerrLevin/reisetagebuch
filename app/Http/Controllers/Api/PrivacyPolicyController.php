<?php

namespace App\Http\Controllers\Api;

use App\Dto\PrivacyPolicyDto;
use App\Http\Controllers\Backend\PrivacyPolicyBackend;
use App\Http\Requests\PrivacyPolicyStoreRequest;
use App\Models\PrivacyPolicy;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class PrivacyPolicyController extends Controller
{
    private PrivacyPolicyBackend $backend;

    public function __construct(PrivacyPolicyBackend $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Get(
        path: '/app/privacy-policy',
        operationId: 'getPrivacyPolicy',
        summary: 'Get the privacy policy currently in effect',
        tags: ['App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: PrivacyPolicyDto::class)
            ),
            new OA\Response(response: 404, description: 'No privacy policy has been published yet'),
        ]
    )]
    public function show(): PrivacyPolicyDto
    {
        return $this->backend->show(Auth::guard('api')->user());
    }

    #[OA\Get(
        path: '/app/privacy-policy/upcoming',
        operationId: 'getUpcomingPrivacyPolicy',
        summary: 'Get the next privacy policy version, if one has been published for the future',
        tags: ['App'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: PrivacyPolicyDto::class)
            ),
            new OA\Response(response: 404, description: 'No upcoming privacy policy has been published'),
        ]
    )]
    public function upcoming(): PrivacyPolicyDto
    {
        return $this->backend->showUpcoming(Auth::guard('api')->user());
    }

    #[OA\Post(
        path: '/app/privacy-policy',
        operationId: 'createPrivacyPolicy',
        description: 'Publish a new privacy policy version. Requires admin privileges.',
        summary: 'Publish a new privacy policy version',
        security: [['passport' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: PrivacyPolicyStoreRequest::class)),
        tags: ['App'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Successful response',
                content: new OA\JsonContent(ref: PrivacyPolicyDto::class)
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function store(PrivacyPolicyStoreRequest $request)
    {
        return response()->json($this->backend->store($request), 201);
    }

    #[OA\Post(
        path: '/app/privacy-policy/{privacyPolicy}/accept',
        operationId: 'acceptPrivacyPolicy',
        description: 'Accept the current or upcoming privacy policy version on behalf of the authenticated user',
        summary: 'Accept a privacy policy version',
        security: [['passport' => []]],
        tags: ['App'],
        parameters: [
            new OA\Parameter(
                name: 'privacyPolicy',
                description: 'The ID of the privacy policy version to accept',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: PrivacyPolicyDto::class)
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'The given privacy policy version is neither current nor upcoming'),
        ]
    )]
    public function accept(PrivacyPolicy $privacyPolicy): PrivacyPolicyDto
    {
        return $this->backend->accept(Auth::guard('api')->user(), $privacyPolicy);
    }
}
