<?php

namespace App\Enums;

enum MessageCollection: string
{
    case BotNotification = 'bot-notification';

    public function messageDataRequiresOneOfFields(): array
    {
        return match ($this) {
            self::BotNotification => [
                'text',
                'animation',
                'audio',
                'voice',
                'video',
                'document',
                'photo',
                'contact',
                'location',
            ]
        };
    }
}
