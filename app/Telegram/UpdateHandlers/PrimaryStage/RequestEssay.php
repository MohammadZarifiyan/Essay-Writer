<?php

namespace App\Telegram\UpdateHandlers\PrimaryStage;

use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Models\User;
use App\Services\TelegramAccessUrl;
use App\Settings\EssaySettings;
use App\Telegram\Stages\EssayTopicStage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redis;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class RequestEssay implements HandlingUpdateStrategyInterface
{
    protected EssaySettings $essaySettings;

    public function __construct(protected Update $update)
    {
        $this->essaySettings = app(EssaySettings::class);
    }

    public function handle(): void
    {
        $gainer = $this->update->gainer();
        $maxAttempts = $this->getMaxAttempts($gainer);
        $rateLimiterKey = 'request-essay:' . $gainer->getKey();
        $username = $this->getUsername();

        if (RateLimiter::tooManyAttempts($rateLimiterKey, $maxAttempts)) {
            $invitationUrl = new TelegramAccessUrl()->make($username, 'referrer-', $gainer->getKey());

            Telegram::perform('sendMessage', [
                'text' => trans('telegram/texts.daily-limit-reached-referral-invitation-required', [
                    'invitation_url' => $invitationUrl
                ]),
                'chat_id' => $this->update->input('message.chat.id'),
                'reply_to_message_id' => $this->update->input('message.message_id'),
                'allow_sending_without_reply' => true,
                'parse_mode' => 'HTML',
                'reply_markup' => [
                    'inline_keyboard' => [
                        [[
                            'text' => trans('telegram/buttons.invite-friends'),
                            'url' => sprintf('https://t.me/share/url?%s', http_build_query(['url' => $invitationUrl]))
                        ]]
                    ]
                ]
            ]);
            return;
        }

        $gainer->update(['stage' => EssayTopicStage::class]);

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-essay-topic'),
            'chat_id' => $this->update->input('message.chat.id'),
            'reply_to_message_id' => $this->update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => [
                    [['text' => trans('telegram/buttons.cancel')]]
                ]
            ]
        ]);

        RateLimiter::hit($rateLimiterKey, now()->addDay()->startOfDay());
    }

    protected function getMaxAttempts(User $user): int
    {
        $numberOfNewReferrals = (int) Redis::get('daily-referral-count:' . $user->getKey() . ':' . today()->toDateString());

        return $numberOfNewReferrals + $this->essaySettings->defaultDailyLimit;
    }

    public function getUsername(): string
    {
        return Cache::driver('redis')->remember('bot-username', 60, function () {
            $response = Telegram::perform('getMe');

            return $response->json('result.username');
        });
    }
}
