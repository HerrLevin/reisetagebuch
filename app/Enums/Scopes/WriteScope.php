<?php

namespace App\Enums\Scopes;

enum WriteScope: string
{
    use DescribesScope;

    case Accounts = 'write:accounts';
    case Statuses = 'write:statuses';
    case Favourites = 'write:favourites';
    case Follows = 'write:follows';
    case Notifications = 'write:notifications';
    case Invites = 'write:invites';
    case LocationHistory = 'write:location-history';
    case Security = 'write:security';
}
