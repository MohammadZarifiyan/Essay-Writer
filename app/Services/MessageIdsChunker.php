<?php

namespace App\Services;

use App\Models\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MessageIdsChunker
{
    public function __construct(protected Collection $messages)
    {
        //
    }

    public function getResult(): array
    {
        return $this->messages->pipeThrough([
            $this->keepMediaGroupMessagesTogether(...),
            $this->chunkByTelegramLimit(...),
            $this->collapseChunks(...)
        ]);
    }

    protected function keepMediaGroupMessagesTogether(Collection $messages): Collection
    {
        $iteratedMediaGroupIds = [];
        $result = new Collection;

        foreach ($messages as $message) {
            $mediaGroupId = data_get($message, 'data.media_group_id');

            if (is_null($mediaGroupId)) {
                $result[] = [data_get($message, 'data.message_id')];
            }
            else if (!in_array($mediaGroupId, $iteratedMediaGroupIds)) {
                $iteratedMediaGroupIds[] = $mediaGroupId;
                $result[] = $messages->where('data.media_group_id', $mediaGroupId)
                    ->map(fn (Message $message) => data_get($message, 'data.message_id'))
                    ->sort()
                    ->all();
            }
        }

        return $result;
    }

    protected function chunkByTelegramLimit(Collection $messages): array
    {
        return Arr::chunkByFlatCount($messages->all(), 100);
    }

    protected function collapseChunks(array $chunks): array
    {
        return array_map(Arr::collapse(...), $chunks);
    }
}
