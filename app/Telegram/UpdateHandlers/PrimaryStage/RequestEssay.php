<?php

namespace App\Telegram\UpdateHandlers\PrimaryStage;

use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Telegram\Stages\EssayTopicStage;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class RequestEssay implements HandlingUpdateStrategyInterface
{
    public function __construct(protected Update $update)
    {
        //
    }

    public function handle(): void
    {
        $gainer = $this->update->gainer();
        $gainer->update(['stage' => EssayTopicStage::class]);

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-essay-topic'),
            'chat_id' => $this->update->input('message.chat.id'),
            'reply_to_message_id' => $this->update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => [
                    [['text' => trans('telegram/buttons.cancel')]]
                ]
            ]
        ]);
    }
}
