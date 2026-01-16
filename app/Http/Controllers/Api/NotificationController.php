<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\NotificationController as Backend;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    private Backend $notificationController;

    public function __construct(Backend $notificationRepository)
    {
        parent::__construct();
        $this->notificationController = $notificationRepository;
    }

    #[OA\Get(
        path: '/notifications/list',
        operationId: 'listNotifications',
        description: 'List notifications for authenticated user',
        summary: 'List notifications',
        tags: ['Notifications'],
        responses: [new OA\Response(response: 200, description: Controller::OA_DESC_SUCCESS)]
    )]
    public function index(Request $request)
    {
        return $this->notificationController->index($request); // todo: implement DTOs
    }

    #[OA\Get(
        path: '/notifications/unread-count',
        operationId: 'unreadNotificationCount',
        description: 'Get unread notifications count',
        summary: 'Unread count',
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: Controller::OA_DESC_SUCCESS,
                content: new OA\MediaType(
                    mediaType: 'application/json',
                    schema: new OA\Schema(
                        required: ['count'],
                        properties: [
                            new OA\Property(
                                property: 'count',
                                description: 'Number of unread notifications',
                                type: 'integer'
                            ),
                        ],
                        type: 'object'
                    )
                )
            ),
        ]
    )]
    public function unreadCount(Request $request)
    {
        return [
            'count' => $this->notificationController->unreadCount($request),
        ];
    }

    #[OA\Post(
        path: '/notifications/{id}/read',
        operationId: 'markNotificationAsRead',
        description: 'Mark a notification as read',
        summary: 'Mark as read',
        tags: ['Notifications'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function markAsRead(Request $request, string $id)
    {
        $this->notificationController->markAsRead($request, $id);

        return response()->noContent();
    }

    #[OA\Post(
        path: '/notifications/read-all',
        operationId: 'markAllNotificationsAsRead',
        description: 'Mark all notifications as read',
        summary: 'Mark all as read',
        tags: ['Notifications'],
        responses: [new OA\Response(response: 204, description: Controller::OA_DESC_NO_CONTENT)]
    )]
    public function markAllAsRead(Request $request)
    {
        $this->notificationController->markAllAsRead($request);

        return response()->noContent();
    }
}
