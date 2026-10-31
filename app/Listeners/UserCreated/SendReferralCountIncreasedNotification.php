<?php

namespace App\Listeners\UserCreated;

use App\Events\UserCreated;
use App\Notifications\ReferralCountIncreasedNotification;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Support\Facades\Redis;

class SendReferralCountIncreasedNotification implements ShouldQueueAfterCommit
{
    public function shouldQueue(UserCreated $event): bool
    {
        $foreignKeyName = $event->user->referrer()->getForeignKeyName();

        return !is_null($event->user->{$foreignKeyName});
    }

    public function withDelay(UserCreated $event): CarbonInterface
    {
        $referrer = $event->user
            ->referrer()
            ->withTrashed()
            ->first();

        $numberOfNewReferrals = (int) Redis::get('daily-referral-count:' . $referrer->getKey() . ':' . $event->user->created_at->toDateString());

        $now = now();
        $delay = match (true) {
            $numberOfNewReferrals <= 5 => $now,
            $numberOfNewReferrals <= 25 => $now->addMinutes(5),
            $numberOfNewReferrals <= 50 => $now->addMinutes(15),
            $numberOfNewReferrals <= 100 => $now->addMinutes(25),
            $numberOfNewReferrals <= 500 => $now->addHour(),
            $numberOfNewReferrals <= 1000 => $now->addHours(3),
            default => $now->addHours(6),
        };

        return $now->copy()->endOfDay()->subMinute()->min($delay);
    }

    public function handle(UserCreated $event): void
    {
        $referrer = $event->user
            ->referrer()
            ->withTrashed()
            ->first();

        $numberOfNewReferrals = (int) Redis::get('daily-referral-count:' . $referrer->getKey() . ':' . $event->user->created_at->toDateString());

        $notification = new ReferralCountIncreasedNotification($numberOfNewReferrals, $event->user->created_at->copy()->startOfDay());
        $notification->afterCommit();

        $referrer->notify($notification);
    }
}
