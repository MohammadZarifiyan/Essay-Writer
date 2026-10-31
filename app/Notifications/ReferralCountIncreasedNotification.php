<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use MohammadZarifiyan\Telegram\TelegramRequestContent;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class ReferralCountIncreasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $numberOfNewReferrals, public Carbon $date)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toSms(object $notifiable): TelegramRequestContent
    {
        $route = $notifiable->routeNotificationFor('telegram', $this);
        $recipient = $route instanceof TelegramRequestOptions ? $route->recipient : $route;

        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => trans_choice('notifications/referral-count-increased-notification.telegram', $this->numberOfNewReferrals, [// todo
                    'number_of_new_referrals' => Number::format($this->numberOfNewReferrals),
                    'date' => $this->date->timezoneAwareTraditionalIsoFormat('Y/M/d', 'Y/M/d VV')
                ]),
                'chat_id' => $recipient,
                'disable_notification' => $this->numberOfNewReferrals > 5,
            ]);
    }
}
