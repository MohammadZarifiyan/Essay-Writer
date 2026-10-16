<?php

namespace App\Telegram\ReplyMarkups;

use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PrimaryKeyboard implements ReplyMarkup
{
    public function __invoke(): array
    {
        return [
            'resize_keyboard' => true,
            'is_persistent' => true,
            'keyboard' => [
                [['text' => trans('telegram/buttons.send-notification')]],
            ]
        ];
    }
}
