<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\AppConfigurationBackend;
use OpenApi\Attributes as OA;

class AppConfigurationController extends Controller
{
    private AppConfigurationBackend $backend;

    public function __construct(AppConfigurationBackend $backend)
    {
        parent::__construct();
        $this->backend = $backend;
    }

    #[OA\Get(
        path: '/app/configuration',
        operationId: 'getAppConfiguration',
        summary: 'Get application configuration',
        tags: ['App Configuration'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: '#/components/schemas/AppConfigurationDto')
            ),
        ]
    )]
    public function index()
    {
        return $this->backend->index();
    }
}
