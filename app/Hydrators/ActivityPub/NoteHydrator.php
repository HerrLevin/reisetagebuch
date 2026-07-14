<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Activities\Note;
use App\Http\Resources\PostTypes\BasePost;

class NoteHydrator
{
    public function hydrate(BasePost $post, string $actorUrl, string $followersUrl, bool $context = false): Note
    {
        $note = new Note;
        $note->id = route('ap.post-object', ['id' => $post->id]);
        $note->published = $post->publishedAt;
        $note->attributedTo = $actorUrl;
        $note->content = $post->getHtmlBody() ?? '';
        $note->to = ['https://www.w3.org/ns/activitystreams#Public'];
        $note->cc = [$followersUrl];

        if ($context) {
            $note->setContext();
        }

        return $note;
    }
}
