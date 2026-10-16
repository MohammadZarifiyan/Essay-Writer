<?php

namespace App\Services\MessageCollectionHandlers;

use App\Interfaces\MessageCollectionHandlerInterface;
use App\Telegram\Stages\ShouldQuoteMessagesOfBotNotification;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class BotNotification implements MessageCollectionHandlerInterface
{
    public function __construct(protected Update $update)
    {
        //
    }

    public function getRequestText(): string
    {
        return trans('telegram/texts.send-the-messages-you-wish-to-be-sent-as-notifications');
    }

    public function saveAndFinish(array $messages): void
    {
        $gainer = $this->update->gainer();

        DB::transaction(function () use ($messages, $gainer) {
            foreach ($messages as $message) {
                $messageId = data_get($message, 'message_id');

                $gainer->temporarilyDataManager()->set('bot-notification:messages:' . $messageId, $message);
            }

            $gainer->update(['stage' => ShouldQuoteMessagesOfBotNotification::class]);
        });

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.do-you-want-the-message-to-be-quoted'),
            'chat_id' => $this->update->input('message.chat.id'),
            'reply_to_message_id' => $this->update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => [
                    [['text' => trans('telegram/buttons.no')], ['text' => trans('telegram/buttons.yes')]],
                    [['text' => trans('telegram/buttons.cancel')]]
                ]
            ]
        ]);
    }
}
