<?php

namespace App\Telegram\Middlewares;

use App\Models\MandatoryMembershipChat;
use MohammadZarifiyan\Telegram\Update;

class UpdateMandatoryMembershipChats
{
    public function handleMyChatMember(Update $update): Update
    {
        $chatId = $update->input('my_chat_member.chat.id');
        $isNewStatusPrivileged = match ($update->input('my_chat_member.new_chat_member.status')) {
            'creator' => true,
            'administrator' => (bool) $update->input('my_chat_member.new_chat_member.can_invite_users'),
            default => false
        };

        MandatoryMembershipChat::where('chat_id', $chatId)
            ->where('validity', !$isNewStatusPrivileged)
            ->each(fn (MandatoryMembershipChat $mandatoryMembershipChat) => $mandatoryMembershipChat->update(['validity' => $isNewStatusPrivileged]));

        return $update;
    }
}
