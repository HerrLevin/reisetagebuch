<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Note;
use App\Dto\ActivityPub\Extensions\RtbExtension;
use App\Dto\ActivityPub\Extensions\RtbLocationExtension;
use App\Dto\ActivityPub\Extensions\RtbTransportExtension;
use App\Http\Resources\PostTypes\BasePost;
use App\Http\Resources\PostTypes\LocationPost;
use App\Http\Resources\PostTypes\TransportPost;

class NoteHydrator
{
    public function hydrate(BasePost $post, string $actorUrl, string $followersUrl, bool $context = false): Note
    {
        $note = new Note;
        $note->id = route('ap.post-object', ['id' => $post->id]);
        $note->published = $post->createdAt;
        $note->attributedTo = $actorUrl;
        $note->content = $post->getHtmlBody() ?? '';
        $note->to = ['https://www.w3.org/ns/activitystreams#Public'];
        $note->cc = [$followersUrl];

        if ($extension = $this->buildExtension($post)) {
            $note->rtbExtension = $extension->toArray();
        }

        if ($context) {
            $note->setContext();
        }

        return $note;
    }

    /**
     * Builds the reisetagebuch-to-reisetagebuch structured extension for Location/
     * Transport posts, so two RTB instances can exchange full-fidelity data instead of
     * just the flattened HTML in `content`. Returns null for any other post type,
     * leaving the Note's `rtbExtension` property unset (see Note::$rtbExtension).
     */
    private function buildExtension(BasePost $post): ?RtbExtension
    {
        return match (true) {
            $post instanceof LocationPost => RtbLocationExtension::fromPost($post),
            $post instanceof TransportPost => RtbTransportExtension::fromPost($post),
            default => null,
        };
    }
}
