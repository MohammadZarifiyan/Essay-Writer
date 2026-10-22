<?php

namespace App\Telegram\Stages;

use App\Enums\EssayTone;
use App\Enums\EssayType;
use App\Telegram\Updates\EssayTypeUpdate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class EssayTypeStage
{
    public function handleMessage(EssayTypeUpdate $update): void
    {
        $essayType = Arr::first(
            EssayType::cases(),
            fn (EssayType $essayType) => __('enums/essay-type.' . $essayType->value) === $update->input('message.text')
        );
        $gainer = $update->gainer();

        DB::transaction(function () use ($essayType, $gainer) {
            $gainer->temporarilyDataManager()->set('essay_type', $essayType);
            $gainer->update(['stage' => EssayToneStage::class]);
        });

        $essayTones = EssayTone::cases();

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-essay-tone'),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => collect($essayTones)
                    ->map(fn (EssayTone $essayTone) => [[
                        'text' => __('enums/essay-tone.' . $essayTone->value),
                    ]])
                    ->add([['text' => trans('telegram/buttons.cancel')]])
                    ->toArray(),
            ]
        ]);
    }
}
