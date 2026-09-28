<?php

namespace App\Dto\ActivityPub\Activities;

use App\Dto\ActivityPub\Objects\BaseObject;

class Note extends BaseObject
{
    public readonly string $type;

    public string $attributedTo;

    public string $content;

    public array $to;

    public array $cc;

    public string $published;

    public string $updated;

    public array $interactionPolicy = [
        'canReply' => [
            'automaticApproval' => [],
            'manualApproval' => [],
        ],
    ];

    /**
     * Optional reisetagebuch-to-reisetagebuch extension envelope. Intentionally left
     * uninitialized (no default) for plain posts: get_object_vars() silently omits an
     * uninitialized typed property, so JsonResponseObject::toArray() simply never emits
     * this key unless NoteHydrator assigns it — keeping the Note byte-for-byte identical
     * to a plain Mastodon Note for any post that isn't a Location/Transport post.
     */
    public array $rtbExtension;

    public function __construct()
    {
        $this->type = 'Note';
    }

    public function setContext(array|string|null $context = []): void
    {
        $context = [
            'https://gotosocial.org/ns',
            'https://www.w3.org/ns/activitystreams',
        ];

        if (isset($this->rtbExtension)) {
            $context[] = [
                'rtb' => 'https://reisetagebu.ch/ns#',
                'rtbExtension' => [
                    '@id' => 'rtb:extension',
                    '@type' => '@json',
                ],
            ];
        }

        parent::setContext($context);
    }
}
