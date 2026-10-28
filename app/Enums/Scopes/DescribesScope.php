<?php

namespace App\Enums\Scopes;

trait DescribesScope
{
    /** Shown on the OAuth consent screen. */
    public function description(): string
    {
        return __('auth.scopes.'.$this->value);
    }

    /** Route middleware requiring this scope (or its parent), e.g. ->middleware(ReadScope::Statuses->middleware()). */
    public function middleware(): string
    {
        return 'scopes:'.$this->value;
    }
}
