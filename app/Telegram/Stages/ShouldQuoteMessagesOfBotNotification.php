<?php

namespace App\Telegram\Stages;

use App\Enums\MessageCollection;
use App\Models\BotNotification;
use App\Models\Message;
use App\Telegram\Updates\YesOrNoAnswerUpdate;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class ShouldQuoteMessagesOfBotNotification
{
    public function handleMessage(YesOrNoAnswerUpdate $update): void
    {
        if ($update->input('message.chat.type') !== 'private') {
            return;
        }

        $gainer = $update->gainer();
        $quote = match ($update->input('message.text')) {
            trans('telegram/buttons.yes') => true,
            trans('telegram/buttons.no') => false,
        };

        $botNotification = DB::transaction(function () use ($update, $gainer, $quote) {
            $botNotification = new BotNotification([
                'quote' => $quote,
                'total_notifications' => 0,
                'successful_notifications' => 0,
            ]);
            $botNotification->sender()->associate($gainer);
            $botNotification->save();

            $messages = $gainer->temporarilyData()
                ->whereLike('key', 'bot-notification:messages:%')
                ->orderBy('key')
                ->get();

            foreach ($messages as $temporarilyData) {
                $message = new Message([
                    'collection_name' => MessageCollection::BotNotification,
                    'data' => $temporarilyData->value,
                ]);
                $message->parent()->associate($botNotification);
                $message->save();
            }

            $gainer->temporarilyData()->delete();
            $gainer->update(['stage' => null]);

            return $botNotification;
        });

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.notification-queued-for-delivery-do-not-delete-messages-until-delivery-completes', [
                'id' => $botNotification->getKey()
            ]),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'reply_markup' => [
                'remove_keyboard' => true
            ]
        ]);
    }
}
