<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use App\Hydrators\UserHydrator;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLiked extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $liker,
        public readonly Post $post,
        public readonly Like $like,
        private readonly UserHydrator $userHydrator = new UserHydrator
    ) {}

    public function databaseType(object $notifiable): DatabaseNotificationType
    {

        return DatabaseNotificationType::PostLiked;

    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'liker' => $this->userHydrator->modelToDto($this->liker),
            'reference_id' => $this->like->id,
            'post_id' => $this->post->id,
            'post_body' => $this->post->body ? substr($this->post->body, 0, 50) : null,
        ];
    }
}
