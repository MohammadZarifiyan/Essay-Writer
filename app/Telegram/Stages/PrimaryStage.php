<?php

namespace App\Telegram\Stages;

use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Telegram\UpdateHandlers\PrimaryStage\RequestEssay;
use App\Telegram\UpdateHandlers\PrimaryStage\SendNotification;
use App\Telegram\Updates\PrimaryUpdate;

class PrimaryStage
{
    public function handleMessage(PrimaryUpdate $update): void
    {
        if ($update->input('message.chat.type') !== 'private') {
            return;
        }

        /**
         * @var HandlingUpdateStrategyInterface $handler
         */
        $handler = match (true) {
            $update->input('message.text') === trans('telegram/buttons.request-essay') => new RequestEssay($update),
            $update->input('message.text') === trans('telegram/buttons.send-notification') => new SendNotification($update),
        };
        $handler->handle();
    }
}
