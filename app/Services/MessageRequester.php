<?php

namespace App\Services;

use App\Enums\MessageCollection;
use App\Interfaces\MessageCollectionHandlerInterface;
use App\Services\MessageCollectionHandlers\BotNotification;
use App\Telegram\Stages\MessageCollectionContent;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class MessageRequester
{
    public function __construct(protected Update $update)
    {
        //
    }

    public function requestFor(MessageCollection $messageCollection): void
    {
        $gainer = $this->update->gainer();

        DB::transaction(function () use ($messageCollection, $gainer) {
            $gainer->temporarilyData()->whereLike('key', 'message-collection:messages:%')->delete();
            $gainer->temporarilyDataManager()->set('message-collection:updated-keyboard', false);
            $gainer->temporarilyDataManager()->set('message-collection', $messageCollection);
            $gainer->update(['stage' => MessageCollectionContent::class]);
        });

        Telegram::perform('sendMessage', [
            'text' => $this->getMessageCollectionHandler($messageCollection)->getRequestText(),
            'chat_id' => $gainer->telegram_id,
            'allow_sending_without_reply' => true,
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => [
                    [['text' => trans('telegram/buttons.cancel')]],
                ]
            ]
        ]);
        Telegram::perform('answerCallbackQuery', [
            'callback_query_id' => $this->update->input('callback_query.id'),
        ]);
    }

    public function getMessageCollectionHandler(MessageCollection $messageCollection): MessageCollectionHandlerInterface
    {
        return match ($messageCollection) {
            MessageCollection::BotNotification => new BotNotification($this->update),
        };
    }
}
