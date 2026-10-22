<?php

namespace App\Telegram\Stages;

use App\Enums\EducationalLevel;
use App\Enums\EssayType;
use App\Telegram\Updates\EssayEducationalLevelUpdate;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class EssayEducationalLevelStage
{
    public function handleMessage(EssayEducationalLevelUpdate $update): void
    {
        $educationalLevel = Arr::first(
            EducationalLevel::cases(),
            fn (EducationalLevel $educationalLevel) => __('enums/educational-level.' . $educationalLevel->value) === $update->input('message.text')
        );
        $gainer = $update->gainer();

        DB::transaction(function () use ($update, $gainer, $educationalLevel) {
            $gainer->temporarilyDataManager()->set('educational_level', $educationalLevel);
            $gainer->update(['stage' => EssayTypeStage::class]);
        });

        $essayTypes = EssayType::cases();

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-essay-type'),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => collect($essayTypes)
                    ->map(fn (EssayType $essayType) => [[
                        'text' => __('enums/essay-type.' . $essayType->value),
                    ]])
                    ->add([['text' => trans('telegram/buttons.cancel')]])
                    ->toArray(),
            ]
        ]);
    }
}
