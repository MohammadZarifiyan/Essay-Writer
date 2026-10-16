<?php

namespace App\State\BotNotificationStatus;

use App\Events\BotNotificationStatusChanged;
use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class BotNotificationStatus extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)
            ->stateChangedEvent(BotNotificationStatusChanged::class)

            ->allowTransition(Pending::class, Processing::class)
            ->allowTransition(Pending::class, Failed::class)

            ->allowTransition(Processing::class, Finished::class)
            ->allowTransition(Processing::class, Canceled::class)
            ->allowTransition(Processing::class, Failed::class);
    }
}
