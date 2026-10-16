<?php

namespace App\Telegram\UpdateHandlers\MessageCollectionContent;

use App\Enums\MessageCollection;
use App\Interfaces\HandlingUpdateStrategyInterface;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MohammadZarifiyan\LaravelLocaleKit\LocaleKit;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class ContinueAddingContent implements HandlingUpdateStrategyInterface
{
    protected User $gainer;

    public function __construct(
        protected Update $update,
        protected bool $updatedKeyboard,
        protected MessageCollection $messageCollection
    ) {
        $this->gainer = $this->update->gainer();
    }

    public function handle(): void
    {
        if (!$this->hasRequiredMessageFields()) {
            Telegram::perform('sendMessage', [
                'text' => trans('telegram/texts.the-message-type-is-not-supported', [
                    'required_one_of_fields' => new Collection($this->messageCollection->messageDataRequiresOneOfFields())
                        ->map(fn (string $field) => Str::replace(['-', '_'], ' ', $field))
                        ->implode(LocaleKit::get('punctuation.comma') . ' '),
                ]),
                'chat_id' => $this->update->json('message.chat.id'),
                'reply_to_message_id' => $this->update->json('message.message_id'),
                'allow_sending_without_reply' => true,
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'is_persistent' => true,
                    'keyboard' => $this->updatedKeyboard
                        ? [
                            [['text' => trans('telegram/buttons.save-and-finish')]],
                            [['text' => trans('telegram/buttons.cancel')]],
                        ]
                        : [
                            [['text' => trans('telegram/buttons.cancel')]],
                        ]
                ]
            ]);
            return;
        }

        if ($this->update->boolean('message.has_protected_content')) {
            Telegram::perform('sendMessage', [
                'text' => trans('telegram/texts.it-is-not-possible-to-save-protected-content'),
                'chat_id' => $this->update->json('message.chat.id'),
                'reply_to_message_id' => $this->update->json('message.message_id'),
                'allow_sending_without_reply' => true,
                'reply_markup' => [
                    'resize_keyboard' => true,
                    'is_persistent' => true,
                    'keyboard' => $this->updatedKeyboard
                        ? [
                            [['text' => trans('telegram/buttons.save-and-finish')]],
                            [['text' => trans('telegram/buttons.cancel')]],
                        ]
                        : [
                            [['text' => trans('telegram/buttons.cancel')]],
                        ]
                ]
            ]);

            return;
        }

        $this->gainer->temporarilyDataManager()->set(
            'message-collection:messages:' . $this->update->input('message.message_id'),
            $this->update->input('message')
        );

        if ($this->updatedKeyboard) {
            return;
        }

        $response = Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.message-saved-stop-process-by-save-and-finish-or-continue-saving'),
            'chat_id' => $this->update->json('message.chat.id'),
            'reply_to_message_id' => $this->update->json('message.message_id'),
            'allow_sending_without_reply' => true,
            'reply_markup' => [
                'resize_keyboard' => true,
                'is_persistent' => true,
                'keyboard' => [
                    [['text' => trans('telegram/buttons.save-and-finish')]],
                    [['text' => trans('telegram/buttons.cancel')]],
                ]
            ]
        ]);

        $this->gainer->temporarilyDataManager()->set('message-collection:updated-keyboard', $response->json('ok'));
    }

    protected function hasRequiredMessageFields(): bool
    {
        $requiredOneOfFields = array_map(
            fn (string $field) => sprintf('message.%s', $field),
            $this->messageCollection->messageDataRequiresOneOfFields()
        );

        return $this->update->anyFilled($requiredOneOfFields);
    }
}
