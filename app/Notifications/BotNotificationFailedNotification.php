<?php

namespace App\Notifications;

use App\Models\BotNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\TelegramRequestContent;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class BotNotificationFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public BotNotification $botNotification)
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
                'text' => trans('notifications/bot-notification-failed.telegram', [
                    'id' => $this->botNotification->getKey()
                ]),
                'chat_id' => $recipient
            ]);
    }
}
