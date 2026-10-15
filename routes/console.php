<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('queue:prune-failed')
    ->daily()
    ->withoutOverlapping()
    ->onOneServer();
