<?php

namespace App\Listeners\BotNotificationCreated;

use App\Events\BotNotificationCreated;
use App\Jobs\DeliverBotNotification;
use App\Models\User;
use App\State\BotNotificationStatus\Failed;
use App\State\BotNotificationStatus\Finished;
use App\State\BotNotificationStatus\Processing;
use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Throwable;

class DispatchNotifications implements ShouldQueueAfterCommit
{
    public string $queue = 'dispatch-notifications';

    public function handle(BotNotificationCreated $event): void
    {
        try {
            $batch = $this->createBatch($event);

            $event->botNotification->status->transitionTo(Processing::class);
            $event->botNotification->loadMissing([
                'sender' => fn (BelongsTo $belongsTo) => $belongsTo->withTrashed(),
            ]);

            User::where('conversation_initiated', true)
                ->where('bot_id', $event->botNotification->sender->bot_id)
                ->chunkById(1000, function (Collection $users) use ($event, &$batch) {
                    $jobs = [];

                    foreach ($users as $user) {
                        $notification = new DeliverBotNotification($event->botNotification, $user);
                        $notification->onQueue('deliver-bot-notifications');

                        $jobs[] = $notification;
                    }

                    $batch = $batch->add($jobs);

                    if ($batch instanceof PendingBatch) {
                        $batch = $batch->dispatch();

                        $event->botNotification->update(['batch_id' => $batch->id]);
                    }

                    $event->botNotification->increment('total_notifications', count($jobs));
                });

            if ($batch instanceof Batch === false) {
                $event->botNotification->status->transitionTo(Finished::class);
            }
        }
        catch (Throwable $throwable) {
            $event->botNotification->status->transitionTo(Failed::class);

            throw $throwable;
        }
    }

    protected function createBatch(BotNotificationCreated $event): PendingBatch
    {
        $batch = Bus::batch([]);
        $batch->finally(function () use ($event) {
            $event->botNotification->status->transitionTo(Finished::class);
        });

        return $batch;
    }
}
