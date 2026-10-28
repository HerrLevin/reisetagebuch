<?php

namespace App\Enums\Scopes;

enum AdminWriteScope: string
{
    use DescribesScope;

    case Accounts = 'admin:write:accounts';
    case Invites = 'admin:write:invites';
    case Settings = 'admin:write:settings';
}
