<?php

namespace App\Listeners\UserCreated;

use App\Events\UserCreated;
use Illuminate\Support\Facades\Redis;

class IncrementDailyReferralCount
{
    public function handle(UserCreated $event): void
    {
        $referrer = $event->user
            ->referrer()
            ->withTrashed()
            ->first();

        if (is_null($referrer)) {
            return;
        }

        $key = 'daily-referral-count:' . $referrer->getKey() . ':' . $event->user->created_at->toDateString();
        $expirationDate = $event->user->created_at->copy()->addDays(2);

        Redis::incr($key);
        Redis::expireAt($key, $expirationDate->timestamp);
    }
}
