<?php

namespace App\Services;

use MohammadZarifiyan\Telegram\Facades\Telegram;

class MandatoryMembershipChecker
{
    public function checkMembership(int $chatId, int $fromId): ?bool
    {
        $response = Telegram::perform('getChatMember', [
            'chat_id' => $chatId,
            'user_id' => $fromId
        ]);

        if ($response->failed()) {
            report($response->toException());
            return null;
        }

        if ($response->json('result.is_anonymous')) {
            return true;
        }

        return in_array(
            $response->json('result.status'),
            ['creator', 'administrator', 'restricted', 'member']
        );
    }
}
