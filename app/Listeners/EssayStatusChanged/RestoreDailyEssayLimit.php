<?php

namespace App\Listeners\EssayStatusChanged;

use App\Events\EssayStatusChanged;
use App\State\EssayStatus\Failed;
use Illuminate\Support\Facades\RateLimiter;

class RestoreDailyEssayLimit
{
    public function handle(EssayStatusChanged $event): void
    {
        if (!$event->finalState->equals(Failed::class)) {
            return;
        }

        $key = 'request-essay:' . $event->model->user->getKey();
        $decaySeconds = $event->model->created_at->addDay()->startOfDay();

        RateLimiter::decrement($key, $decaySeconds);
    }
}
