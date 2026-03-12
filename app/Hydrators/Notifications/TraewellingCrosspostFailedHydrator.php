<?php

namespace App\Hydrators\Notifications;

use App\Dto\Notifications\TraewellingCrosspostFailedData;
use Illuminate\Notifications\DatabaseNotification;

class TraewellingCrosspostFailedHydrator
{
    public function hydrate(DatabaseNotification $notification): TraewellingCrosspostFailedData
    {
        $data = $notification->data;

        return new TraewellingCrosspostFailedData(
            postId: $data['post_id'],
            errorMessage: $data['error_message'],
        );
    }
}
