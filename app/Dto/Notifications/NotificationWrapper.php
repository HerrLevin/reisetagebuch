<?php

namespace App\Dto\Notifications;

use App\Enums\DatabaseNotificationType as Notification;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NotificationWrapper',
    description: 'A wrapper for notification data',
    required: ['id', 'type', 'createdAt', 'updatedAt', 'readAt', 'data'],
    properties: [
        new OA\Property(
            property: 'id',
            description: 'Unique identifier for the notification',
            type: 'string',
            format: 'uuid'
        ),
        new OA\Property(
            property: 'type',
            ref: '#/components/schemas/NotificationType',
        ),
        new OA\Property(
            property: 'createdAt',
            description: 'Timestamp when the notification was created',
            type: 'string',
            format: 'date-time'
        ),
        new OA\Property(
            property: 'updatedAt',
            description: 'Timestamp when the notification was last updated',
            type: 'string',
            format: 'date-time'
        ),
        new OA\Property(
            property: 'readAt',
            description: 'Timestamp when the notification was read',
            type: 'string',
            format: 'date-time',
            nullable: true
        ),
        new OA\Property(
            property: 'data',
            description: 'Additional data associated with the notification',
            type: 'object',
            nullable: true,
            oneOf: [
                new OA\Schema(ref: PostLikedData::class),
            ]
        ),
    ]
)]
readonly class NotificationWrapper
{
    public function __construct(
        public string $id,
        public Notification $type,
        public string $createdAt,
        public string $updatedAt,
        public ?string $readAt = null,
        public mixed $data = null,
    ) {}
}
