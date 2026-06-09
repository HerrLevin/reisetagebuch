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

    public function __construct()
    {
        $this->type = 'Note';
    }

    public function setContext(array|string|null $context = []): void
    {
        parent::setContext(
            [
                'https://gotosocial.org/ns',
                'https://www.w3.org/ns/activitystreams',
            ]
        );
    }
}
