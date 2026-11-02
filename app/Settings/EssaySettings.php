<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class EssaySettings extends Settings
{
    public int $defaultDailyLimit;

    public static function group(): string
    {
        return 'essay';
    }
}
