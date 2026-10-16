<?php

namespace App\Jobs;

use App\Enums\MessageCollection;
use App\Models\BotNotification;
use App\Models\Message;
use App\Models\User;
use App\Services\MessageIdsChunker;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class DeliverBotNotification implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public BotNotification $botNotification, public User $user)
    {
        //
    }

    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        if (is_null($this->user) || $this->user->trashed()) {
            $this->fail('Receiver not found');
            return;
        }

        if (is_null($this->botNotification->sender) || $this->botNotification->sender->trashed()) {
            $this->fail('Sender not found');
            return;
        }

        $method = $this->getMethod();
        $messages = $this->getMessages();
        $messageIdsChunks = new MessageIdsChunker($messages)->getResult();

        foreach ($messageIdsChunks as $messageIds) {
            $response = Telegram::perform($method, [
                'from_chat_id' => $this->botNotification->sender->telegram_id,
                'chat_id' => $this->user->telegram_id,
                'message_ids' => $messageIds,
            ]);

            if ($response->successful()) {
                continue;
            }

            $description = $response->json('description', '');

            if (str_contains($description, 'USER_IS_BLOCKED')) {
                $this->user->update(['conversation_initiated' => false]);;
            }
            else if (str_contains($description, 'User is deactivated')) {
                $this->user->delete();;
            }

            $this->fail($response->toException());
            return;
        }

        $this->botNotification->increment('successful_notifications');
    }

    protected function getMethod(): string
    {
        return $this->botNotification->quote ? 'forwardMessages' : 'copyMessages';
    }

    protected function getMessages(): Collection
    {
        return Message::whereMorphedTo('parent', $this->botNotification)
            ->where('collection_name', MessageCollection::BotNotification)
            ->get()
            ->toBase();
    }
}
