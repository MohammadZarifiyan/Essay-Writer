<?php

namespace App\Listeners\MandatoryMembershipChatUpdated;

use App\Events\MandatoryMembershipChatUpdated;
use App\Models\User;
use App\Notifications\MandatoryMembershipChatIsNotValidNotification;

class SendMandatoryMembershipChatIsNotValidNotification
{
    public function handle(MandatoryMembershipChatUpdated $event): void
    {
        if (!$this->shouldHandle($event)) {
            return;
        }

        foreach (User::role('administrator')->lazy() as $user) {
            $notification = new MandatoryMembershipChatIsNotValidNotification($event->mandatoryMembershipChat);
            $notification->afterCommit();

            $user->notify($notification);
        }
    }

    protected function shouldHandle(MandatoryMembershipChatUpdated $event): bool
    {
        return !$event->mandatoryMembershipChat->validity && $event->mandatoryMembershipChat->wasChanged('validity');
    }
}
