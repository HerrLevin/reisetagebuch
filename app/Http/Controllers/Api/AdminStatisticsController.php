<?php

namespace App\Http\Controllers\Api;

use App\Dto\AdminStatisticsDto;
use App\Enums\Scopes\AdminReadScope;
use App\Http\Controllers\Backend\AdminStatisticsBackend;
use OpenApi\Attributes as OA;

class AdminStatisticsController extends Controller
{
    public function __construct(private readonly AdminStatisticsBackend $backend)
    {
        parent::__construct();
    }

    #[OA\Get(
        path: '/admin/statistics',
        operationId: 'getAdminStatistics',
        description: 'Global statistics of this instance. Requires admin privileges.',
        summary: 'Get instance statistics',
        security: [['passport' => [AdminReadScope::Statistics->value]]],
        tags: ['Admin'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful response',
                content: new OA\JsonContent(ref: AdminStatisticsDto::class)
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function show(): AdminStatisticsDto
    {
        return $this->backend->show();
    }
}
