<?php

namespace App\Notifications;

use App\Models\BotNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Number;
use MohammadZarifiyan\Telegram\TelegramRequestContent;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class BotNotificationFinishedNotification extends Notification implements ShouldQueue
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
                'text' => trans('notifications/bot-notification-finished.telegram', [
                    'id' => $this->botNotification->getKey(),
                    'total_notifications' => Number::format($this->botNotification->total_notifications),
                    'successful_notifications' => Number::format($this->botNotification->successful_notifications),
                ]),
                'chat_id' => $recipient
            ]);
    }
}
