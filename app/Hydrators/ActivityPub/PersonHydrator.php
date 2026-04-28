<?php

namespace App\Hydrators\ActivityPub;

use App\Dto\ActivityPub\Image;
use App\Dto\ActivityPub\Objects\Person;
use App\Dto\ActivityPub\PublicKey;
use App\Http\Resources\UserDto;

class PersonHydrator
{
    public function hydrate(UserDto $user): Person
    {
        $person = new Person;
        $person->id = route('ap.actor', ['username' => $user->username]);
        $person->following = route('ap.following', ['username' => $user->username]);
        $person->followers = route('ap.followers', ['username' => $user->username]);
        $person->inbox = route('ap.inbox', ['username' => $user->username]);
        $person->outbox = route('ap.outbox', ['username' => $user->username]);
        $person->preferredUsername = $user->username;
        $person->name = $user->name;
        $person->summary = $user->bio ?? '';
        $person->url = url('/@'.$user->username);
        $person->manuallyApprovesFollowers = $user->requiresFollowRequest;
        $person->discoverable = true;
        $person->published = $user->createdAt;
        $person->endpoints = [
            'sharedInbox' => route('ap.shared-inbox'),
        ];

        $key = new PublicKey;
        $key->id = route('ap.actor', ['username' => $user->username]).'#main-key';
        $key->owner = route('ap.actor', ['username' => $user->username]);
        $key->publicKeyPem = $user->publicKeyPem;

        $person->publicKey = $key;

        if ($user->avatar !== null) {
            $image = new Image;
            $image->mediaType = $user->avatarMimeType ?? '';
            $image->url = $user->avatar;
            $person->icon = $image;
        }

        if ($user->header !== null) {
            $image = new Image;
            $image->mediaType = $user->headerMimeType ?? '';
            $image->url = $user->header;
            $person->image = $image;
        }

        $person->setContext([
            'https://www.w3.org/ns/activitystreams',
            'https://w3id.org/security/v1',
            [
                'manuallyApprovesFollowers' => 'as:manuallyApprovesFollowers',
                'toot' => 'http://joinmastodon.org/ns#',
                'schema' => 'http://schema.org#',
                'PropertyValue' => 'schema:PropertyValue',
                'value' => 'schema:value',
                'discoverable' => 'toot:discoverable',
                'indexable' => 'toot:indexable',
                'attributionDomains' => [
                    '@id' => 'toot:attributionDomains',
                    '@type' => '@id',
                ],
                'focalPoint' => [
                    '@container' => '@list',
                    '@id' => 'toot:focalPoint',
                ],
                'alsoKnownAs' => [
                    '@id' => 'toot:alsoKnownAs',
                    '@type' => '@id',
                ],
            ],
        ]);

        return $person;
    }
}
