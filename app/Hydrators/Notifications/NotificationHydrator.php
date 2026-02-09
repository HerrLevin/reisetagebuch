<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\NotificationWrapper;
use App\Enums\DatabaseNotificationType;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;

class NotificationHydrator
{
    /**
     * @param  Collection<DatabaseNotification>  $notifications
     * @return Collection
     */
    public function hydrateCollection(Collection $notifications)
    {
        return $notifications->map(function (DatabaseNotification $notification) {
            return $this->hydrate($notification);
        });
    }

    public function hydrate(DatabaseNotification $notification): ?NotificationWrapper
    {
        $type = DatabaseNotificationType::tryFrom($notification->type);
        if (! $type) {
            return null;
        }

        return new NotificationWrapper(
            id: $notification->id,
            type: $type,
            createdAt: $notification->created_at->toIso8601String(),
            updatedAt: $notification->updated_at->toIso8601String(),
            readAt: $notification->read_at?->toIso8601String(),
            data: $this->hydrateData($notification),
        );
    }

    private function hydrateData(DatabaseNotification $notification)
    {
        $type = DatabaseNotificationType::tryFrom($notification->type);
        $dataHydratorClass = $type?->getHydratorClassName() ?? null;

        if ($dataHydratorClass && class_exists($dataHydratorClass)) {
            $dataHydrator = new $dataHydratorClass;

            return $dataHydrator->hydrate($notification);
        }

        return null;
    }
}
