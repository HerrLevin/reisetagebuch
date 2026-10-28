<?php

namespace App\Enums\Scopes;

enum ReadScope: string
{
    use DescribesScope;

    case Accounts = 'read:accounts';
    case Statuses = 'read:statuses';
    case Favourites = 'read:favourites';
    case Follows = 'read:follows';
    case Notifications = 'read:notifications';
    case Search = 'read:search';
    case Invites = 'read:invites';
    case LocationHistory = 'read:location-history';
    case Statistics = 'read:statistics';
}
