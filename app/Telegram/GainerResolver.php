<?php

namespace App\Telegram;

use App\Models\User;
use App\Services\TelegramAccessUrl;
use App\Telegram\Stages\PrimaryStage;
use Illuminate\Database\Eloquent\Model;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\GainerResolver as GainerResolverInterface;
use MohammadZarifiyan\Telegram\Update;

class GainerResolver implements GainerResolverInterface
{
    public function __invoke(Update $update): ?Model
    {
        $type = $update->type();
        $isBot = $update->input($type .'.from.is_bot');
        $telegramId = $update->input($type . '.from.id');

        if (in_array($isBot, [true, null], true) || is_null($telegramId)) {
            return null;
        }

        $conversationInitiated = $type === 'message' && $update->input('message.chat.type') === 'private';
        $botId = Telegram::getBotId();
        $user = User::withTrashed()->firstWhere([
            'telegram_id' => $telegramId,
            'bot_id' => $botId,
        ]);

        if (is_null($user)) {
            $referrer = $this->getReferrer($update);

            $user = new User([
                'bot_id' => $botId,
                'telegram_id' => $telegramId,
                'conversation_initiated' => $conversationInitiated,
                'stage' => PrimaryStage::class,
            ]);
            $user->referrer()->associate($referrer);
            $user->save();

            return $user;
        }

        $user->fill([
            'conversation_initiated' => $conversationInitiated || $user->conversation_initiated,
        ]);

        if ($user->trashed()) {
            $user->restore();
        }
        else {
            $user->save();
        }

        return $user;
    }

    protected function getReferrer(Update $update): ?User
    {
        $command = $update->toCommand();

        if ($command?->value !== 'start') {
            return null;
        }

        $id = new TelegramAccessUrl()->extract($command->value, 'referrer-');

        return is_null($id) ? null : User::find($id);
    }
}
