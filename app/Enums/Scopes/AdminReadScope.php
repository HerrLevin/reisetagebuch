<?php

namespace App\Enums\Scopes;

enum AdminReadScope: string
{
    use DescribesScope;

    case Accounts = 'admin:read:accounts';
    case Invites = 'admin:read:invites';
    case Settings = 'admin:read:settings';
}
