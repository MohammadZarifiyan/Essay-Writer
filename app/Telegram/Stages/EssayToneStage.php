<?php

namespace App\Telegram\Stages;

use App\Enums\EssayLength;
use App\Enums\EssayTone;
use App\Telegram\Updates\EssayToneUpdate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class EssayToneStage
{
    public function handleMessage(EssayToneUpdate $update): void
    {
        $essayTone = Arr::first(
            EssayTone::cases(),
            fn (EssayTone $essayTone) => __('enums/essay-tone.' . $essayTone->value) === $update->input('message.text')
        );
        $gainer = $update->gainer();

        DB::transaction(function () use ($essayTone, $gainer) {
            $gainer->temporarilyDataManager()->set('essay_tone', $essayTone);
            $gainer->update(['stage' => EssayLengthStage::class]);
        });

        $essayLengths = EssayLength::cases();

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-essay-length'),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => collect($essayLengths)
                    ->map(fn (EssayLength $essayLength) => [[
                        'text' => __('enums/essay-length.' . $essayLength->value),
                    ]])
                    ->add([['text' => trans('telegram/buttons.cancel')]])
                    ->toArray(),
            ]
        ]);
    }
}
