<?php

namespace App\Events;

use App\Models\BotNotification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BotNotificationCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public BotNotification $botNotification)
    {
        //
    }
}
