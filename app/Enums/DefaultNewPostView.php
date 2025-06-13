<?php

namespace App\Enums;

enum DefaultNewPostView: string
{
    case Location = 'location';
    case Departures = 'departures';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::Location => __('Location'),
            self::Departures => __('Departures'),
            self::Text => __('Text'),
        };
    }
}
