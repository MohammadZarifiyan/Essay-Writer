<?php

namespace App\Telegram\Stages;

use App\Telegram\UpdateHandlers\MessageCollectionContent\ContinueAddingContent;
use App\Telegram\UpdateHandlers\MessageCollectionContent\SaveAndFinish;
use MohammadZarifiyan\Telegram\Update;

class MessageCollectionContent
{
    public function handleMessage(Update $update): void
    {
        if ($update->input('message.chat.type') !== 'private') {
            return;
        }

        $gainer = $update->gainer();
        $updatedKeyboard = $gainer->temporarilyDataManager()->findByName('message-collection:updated-keyboard');
        $messageCollection = $gainer->temporarilyDataManager()->findByName('message-collection');
        $handler = $update->input('message.text') === trans('telegram/buttons.save-and-finish') && $updatedKeyboard
            ? new SaveAndFinish($update)
            : new ContinueAddingContent($update, $updatedKeyboard, $messageCollection);

        $handler->handle();
    }
}
