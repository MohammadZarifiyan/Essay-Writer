<?php

namespace App\Notifications;

use App\Models\Essay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\TelegramRequestContent;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class EssayGenerationFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Essay $essay)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramRequestContent
    {
        $route = $notifiable->routeNotificationFor('telegram', $this);
        $recipient = $route instanceof TelegramRequestOptions ? $route->recipient : $route;

        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => trans('notifications/essay-generation-failed.telegram', [
                    'topic' => $this->essay->topic
                ]),
                'chat_id' => $recipient
            ]);
    }
}
