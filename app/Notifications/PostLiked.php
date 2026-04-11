<?php

namespace App\Notifications;

use App\Enums\DatabaseNotificationType;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;
use App\Hydrators\UserHydrator;
use App\Models\Like;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostLiked extends Notification
{
    use Queueable;

    public function __construct(
        public readonly User $liker,
        public readonly BasePost|LocationPost|TransportPost $post,
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
            'summary' => $this->getSummary(),
        ];
    }

    private function getSummary(): ?string
    {
        if ($this->post instanceof LocationPost) {
            return $this->getLocationPostSummary();
        } elseif ($this->post instanceof TransportPost) {
            return $this->getTransportPostSummary();
        }

        return empty($this->post->body) ? null : $this->formattedBody();
    }

    private function formattedBody(): string
    {
        return $this->post->body ? sprintf('"%s" ', $this->post->body) : '';
    }

    private function getLocationPostSummary(): string
    {
        return sprintf(
            '%s@ %s',
            $this->formattedBody(),
            $this->post->location->name
        );
    }

    private function getTransportPostSummary(): string
    {
        return sprintf(
            '%s@ %s, %s ➜ %s',
            $this->formattedBody(),
            $this->post->trip->displayName ?? $this->post->trip->tripShortName ?? $this->post->trip->lineName ?? $this->post->trip->routeLongName,
            $this->post->originStop->name,
            $this->post->destinationStop->name
        );
    }
}
