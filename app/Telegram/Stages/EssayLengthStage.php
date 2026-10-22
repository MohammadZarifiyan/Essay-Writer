<?php

namespace App\Telegram\Stages;

use App\Enums\EssayLength;
use App\Jobs\ProcessEssay;
use App\Models\Essay;
use App\Telegram\ReplyMarkups\PrimaryKeyboard;
use App\Telegram\Updates\EssayLengthUpdate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class EssayLengthStage
{
    public function handleMessage(EssayLengthUpdate $update): void
    {
        $essayLength = Arr::first(
            EssayLength::cases(),
            fn (EssayLength $essayLength) => __('enums/essay-length.' . $essayLength->value) === $update->input('message.text')
        );
        $gainer = $update->gainer();
        $essay = DB::transaction(function () use ($essayLength, $gainer) {
            $essay = new Essay([
                'topic' => $gainer->temporarilyDataManager()->findByName('essay_topic'),
                'educational_level' => $gainer->temporarilyDataManager()->findByName('educational_level'),
                'type' => $gainer->temporarilyDataManager()->findByName('essay_type'),
                'tone' => $gainer->temporarilyDataManager()->findByName('essay_tone'),
                'requested_word_count' => $essayLength->getWordCount(),
            ]);
            $essay->user()->associate($gainer);
            $essay->save();

            $gainer->update(['stage' => PrimaryStage::class]);
            $gainer->temporarilyData()->delete();

            return $essay;
        });

        ProcessEssay::dispatch($essay);

        Telegram::perform(
            'sendMessage',
            [
                'text' => trans('telegram/texts.your-essay-details-saved-notify-when-essay-ready'),
                'chat_id' => $update->input('message.chat.id'),
                'reply_to_message_id' => $update->input('message.message_id'),
                'allow_sending_without_reply' => true,
                'parse_mode' => 'HTML',
            ],
            PrimaryKeyboard::class
        );
    }
}
