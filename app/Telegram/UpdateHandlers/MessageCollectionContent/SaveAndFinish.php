<?php

namespace App\Telegram\UpdateHandlers\MessageCollectionContent;

use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Models\TemporarilyData;
use App\Models\User;
use App\Services\MessageRequester;
use Illuminate\Database\Eloquent\Builder;
use MohammadZarifiyan\Telegram\Update;

class SaveAndFinish implements HandlingUpdateStrategyInterface
{
    protected User $gainer;

    public function __construct(protected Update $update)
    {
        $this->gainer = $this->update->gainer();
    }

    public function handle(): void
    {
        $messages = $this->getMessages();
        $messageCollection = $this->gainer->temporarilyDataManager()->findByName('message-collection');
        $messageCollectionHandler = new MessageRequester($this->update)->getMessageCollectionHandler($messageCollection);

        $messageCollectionHandler->saveAndFinish($messages);

        $this->gainer
            ->temporarilyData()
            ->where(function (Builder $builder) {
                $builder->whereIn('key', ['message-collection', 'message-collection:updated-keyboard']);
                $builder->orWhereLike('key', 'message-collection:messages:%');
            })
            ->delete();
    }

    protected function getMessages(): array
    {
        return $this->gainer
            ->temporarilyData()
            ->whereLike('key', 'message-collection:messages:%')
            ->orderBy('key')
            ->get()
            ->map(fn (TemporarilyData $temporarilyData) => $temporarilyData->value)
            ->all();
    }
}
