<?php

namespace App\Telegram\Breakers;

use App\Models\User;
use App\Telegram\ReplyMarkups\PrimaryKeyboard;
use App\Telegram\Stages\PrimaryStage;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class Cancel
{
    public function handleMessage(Update $update): Signal
    {
        $gainer = $update->gainer();

        if (
            $gainer instanceof User === false
            || $update->input('message.chat.type') !== 'private'
            || $update->input('message.text') !== trans('telegram/buttons.cancel')
        ) {
            return Signal::Continue;
        }

        DB::transaction(function () use ($gainer) {
            $gainer->update(['stage' => PrimaryStage::class]);
            $gainer->temporarilyData()->delete();
        });

        Telegram::perform(
            'sendMessage',
            [
                'text' => trans('telegram/texts.operation-canceled'),
                'chat_id' => $update->input('message.chat.id'),
                'reply_to_message_id' => $update->input('message.message_id'),
                'allow_sending_without_reply' => true
            ],
            PrimaryKeyboard::class
        );

        return Signal::Exit;
    }
}
