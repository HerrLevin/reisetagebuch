<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Hydrators\Notifications\NotificationHydrator;
use App\Repositories\NotificationRepository;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private NotificationRepository $notificationRepository;

    private NotificationHydrator $hydrator;

    public function __construct(NotificationRepository $notificationRepository, NotificationHydrator $hydrator)
    {
        $this->notificationRepository = $notificationRepository;
        $this->hydrator = $hydrator;
    }

    public function index(Request $request)
    {
        return $this->hydrator->hydrateCollection($this->notificationRepository->index($request->user()));
    }

    public function unreadCount(Request $request): int
    {
        return $this->notificationRepository->count($request->user());
    }

    public function markAsRead(Request $request, string $id): bool
    {
        return $this->notificationRepository->markAsRead($request->user(), $id)->exists;
    }

    public function markAllAsRead(Request $request): bool
    {
        $this->notificationRepository->markAllAsRead($request->user());

        return true;
    }
}
