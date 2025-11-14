<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\NotificationController as Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private Backend $notificationController;

    public function __construct(Backend $notificationRepository)
    {
        $this->notificationController = $notificationRepository;
    }

    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notificationController->index($request);

        return response()->json($notifications);
    }

    public function unreadCount(Request $request): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationController->unreadCount($request),
        ]);
    }

    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $this->notificationController->markAsRead($request, $id);

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationController->markAllAsRead($request);

        return response()->json(['success' => true]);
    }
}
