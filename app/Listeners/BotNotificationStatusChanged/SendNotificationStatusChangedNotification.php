<?php

namespace App\Listeners\BotNotificationStatusChanged;

use App\Events\BotNotificationStatusChanged;
use App\Notifications\BotNotificationFailedNotification;
use App\Notifications\BotNotificationFinishedNotification;
use App\State\BotNotificationStatus\Finished;
use App\State\BotNotificationStatus\Failed;
use Illuminate\Notifications\Notification;

class SendNotificationStatusChangedNotification
{
    public function handle(BotNotificationStatusChanged $event): void
    {
        if (!$this->shouldHandle($event)) {
            return;
        }

        $notification = $this->getNotification($event);

        if ($notification) {
            $event->model->sender->notify($notification);
        }
    }

    protected function shouldHandle(BotNotificationStatusChanged $event): bool
    {
        return is_null($event->model->sender) === false && $event->model->sender->trashed() === false;
    }

    protected function getNotification(BotNotificationStatusChanged $event): ?Notification
    {
        $notification = match (true) {
            $event->finalState->equals(Finished::class) => new BotNotificationFinishedNotification($event->model),
            $event->finalState->equals(Failed::class) => new BotNotificationFailedNotification($event->model),
            default => null
        };

        if (is_null($notification)) {
            return null;
        }

        $notification->afterCommit();

        return $notification;
    }
}
