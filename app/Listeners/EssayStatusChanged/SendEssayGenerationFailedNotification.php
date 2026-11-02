<?php

namespace App\Listeners\EssayStatusChanged;

use App\Events\EssayStatusChanged;
use App\Notifications\EssayGenerationFailedNotification;
use App\State\EssayStatus\Failed;

class SendEssayGenerationFailedNotification
{
    public function handle(EssayStatusChanged $event): void
    {
        if (!$event->finalState->equals(Failed::class)) {
            return;
        }

        $notification = new EssayGenerationFailedNotification($event->model);
        $notification->afterCommit();

        $event->model->user->notify($notification);
    }
}
