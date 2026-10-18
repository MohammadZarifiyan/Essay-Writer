<?php

namespace App\Telegram\UpdateHandlers\PrimaryStage;

use App\Enums\MessageCollection;
use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Services\MessageRequester;
use MohammadZarifiyan\Telegram\Update;

class SendNotification implements HandlingUpdateStrategyInterface
{
    public function __construct(protected Update $update)
    {
        //
    }

    public function handle(): void
    {
        new MessageRequester($this->update)->requestFor(MessageCollection::BotNotification);
    }
}
