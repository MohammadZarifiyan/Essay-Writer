<?php

namespace App\Telegram\ReplyMarkups;

use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\ReplyMarkup;

class PrimaryKeyboard implements ReplyMarkup
{
    public function __invoke(): array
    {
        $gainer = Telegram::getUpdate()->gainer();
        $keyboard = [['text' => trans('telegram/buttons.request-essay')]];

        if ($gainer->hasRole('administrator')) {
            $keyboard[] = [['text' => trans('telegram/buttons.send-notification')]];
        }

        return [
            'resize_keyboard' => true,
            'is_persistent' => true,
            'keyboard' => $keyboard
        ];
    }
}
