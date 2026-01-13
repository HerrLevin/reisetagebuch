<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Backend\NotificationController as Backend;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private Backend $notificationController;

    public function __construct(Backend $notificationRepository)
    {
        $this->notificationController = $notificationRepository;
    }

    public function index(Request $request)
    {
        return $this->notificationController->index($request);
    }

    public function unreadCount(Request $request)
    {
        return [
            'count' => $this->notificationController->unreadCount($request),
        ];
    }

    public function markAsRead(Request $request, string $id)
    {
        $this->notificationController->markAsRead($request, $id);

        return ['success' => true];
    }

    public function markAllAsRead(Request $request)
    {
        $this->notificationController->markAllAsRead($request);

        return ['success' => true];
    }
}
