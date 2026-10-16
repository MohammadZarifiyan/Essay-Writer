<?php

namespace App\Telegram\CommandHandlers;

use App\Models\User;
use App\Telegram\ReplyMarkups\PrimaryKeyboard;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\CommandHandler;
use MohammadZarifiyan\Telegram\Update;

class StartCommandHandler implements CommandHandler
{
    public function getSignature(Update $update): string|array
    {
        return 'start';
    }

    public function handle(Update $update): Signal
    {
        $gainer = $update->gainer();

        if ($gainer instanceof User === false || $update->input('message.chat.type') !== 'private') {
            return Signal::Continue;
        }

        DB::transaction(function () use ($gainer) {
            $gainer->update(['stage' => null]);
            $gainer->temporarilyData()->delete();
        });

        Telegram::perform(
            'sendMessage',
            [
                'text' => trans('telegram/texts.greeting'),
                'chat_id' => $update->input('message.chat.id'),
                'reply_to_message_id' => $update->input('message.message_id'),
                'allow_sending_without_reply' => true,
                'parse_mode' => 'HTML',
            ],
            PrimaryKeyboard::class
        );

        return Signal::Exit;
    }
}
