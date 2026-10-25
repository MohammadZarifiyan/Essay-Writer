<?php

namespace App\Notifications;

use App\Filament\Resources\MandatoryMembershipChats\MandatoryMembershipChatResource;
use App\Models\MandatoryMembershipChat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\TelegramRequestContent;
use MohammadZarifiyan\Telegram\TelegramRequestOptions;

class MandatoryMembershipChatIsNowValidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public MandatoryMembershipChat $mandatoryMembershipChat)
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
        $me = Telegram::perform('getMe');

        return TelegramRequestContent::fresh()
            ->setMethod('sendMessage')
            ->setData([
                'text' => trans('notifications/mandatory-membership-chat-is-now-valid.telegram', [
                    'title' => $this->mandatoryMembershipChat->title,
                    'url' => $this->mandatoryMembershipChat->url,
                ]),
                'chat_id' => $recipient,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [[
                            'text' => trans('telegram/buttons.manage-mandatory-membership-chats'),
                            'url' => MandatoryMembershipChatResource::getUrl()
                        ]],
                    ]
                ]
            ]);
    }
}
