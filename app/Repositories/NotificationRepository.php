<?php

namespace App\Repositories;

use App\Enums\DatabaseNotificationType;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository
{
    public function index(User $user): Collection
    {
        return $user
            ->notifications()
            ->latest()
            ->limit(50)
            ->get();
    }

    public function count(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $id): DatabaseNotification
    {
        $notification = $user
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return $notification;
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function deleteReferencedNotification(
        User|string $user,
        DatabaseNotificationType $notificationType,
        string $reference
    ): void {
        $userId = $user instanceof User ? $user->id : $user;

        $query = DatabaseNotification::where('notifiable_id', $userId)
            ->where('notifiable_type', User::class)
            ->where('type', $notificationType)
            ->whereJsonContains('data', ['reference_id' => $reference]);
        $query
            ->delete();
    }
}
