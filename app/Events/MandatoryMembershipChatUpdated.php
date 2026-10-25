<?php

namespace App\Events;

use App\Models\MandatoryMembershipChat;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MandatoryMembershipChatUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public MandatoryMembershipChat $mandatoryMembershipChat)
    {
        //
    }
}
