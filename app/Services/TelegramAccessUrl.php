<?php

namespace App\Services;

use Illuminate\Support\Str;

class TelegramAccessUrl
{
    public function make(string $username, string $prefix, string|int $value): string
    {
        return sprintf('https://t.me/%s?start=%s', $username, $prefix . $value);
    }

    public function extract(string $commandValue, string $prefix): ?string
    {
        if (!str_starts_with($commandValue, $prefix)) {
            return null;
        }

        return Str::of($commandValue)
            ->replaceStart($prefix, '')
            ->trim();
    }
}
