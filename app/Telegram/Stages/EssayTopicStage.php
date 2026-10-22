<?php

namespace App\Telegram\Stages;

use App\Enums\EducationalLevel;
use App\Telegram\Updates\EssayTopicUpdate;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class EssayTopicStage
{
    public function handleMessage(EssayTopicUpdate $update): void
    {
        $gainer = $update->gainer();

        DB::transaction(function () use ($update, $gainer) {
            $gainer->temporarilyDataManager()->set('topic', $update->input('message.text'));
            $gainer->update(['stage' => EssayEducationalLevelStage::class]);
        });

        $educationLevels = EducationalLevel::cases();

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.send-me-your-educational-level'),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => collect($educationLevels)
                    ->map(fn (EducationalLevel $educationalLevel) => [[
                        'text' => __('enums/educational-level.' . $educationalLevel->value),
                    ]])
                    ->add([['text' => trans('telegram/buttons.cancel')]])
                    ->toArray(),
            ]
        ]);
    }
}
