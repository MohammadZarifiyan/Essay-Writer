<?php

namespace App\State\EssayStatus;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class EssayStatus extends State
{
    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Pending::class)

            ->allowTransition(Pending::class, Processing::class)

            ->allowTransition(Processing::class, Generated::class)
            ->allowTransition(Processing::class, Failed::class);
    }
}
