<?php

namespace App\Telegram\Breakers;

use App\Models\MandatoryMembershipChat;
use App\Services\MandatoryMembershipChecker;
use App\Telegram\ReplyMarkups\PrimaryKeyboard;
use App\Telegram\Stages\PrimaryStage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MohammadZarifiyan\Telegram\Enums\Signal;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;

class EnsureUserIsJointInMandatoryChats
{
    public function handleCallbackQuery(Update $update): Signal
    {
        if ($update->input('callback_query.message.chat.type') !== 'private') {
            return Signal::Continue;
        }

        $mandatoryMembershipChats = MandatoryMembershipChat::where('validity', true)
            ->orderBy('order_number')
            ->get()
            ->filter(function (MandatoryMembershipChat $mandatoryMembershipChat) use ($update) {
                $isJoint = Cache::driver('redis')->remember(
                    'is-' . $update->input('callback_query.from.id') . '-joint-' . $mandatoryMembershipChat->chat_id,
                    5,
                    fn () => new MandatoryMembershipChecker()->checkMembership($mandatoryMembershipChat->chat_id, $update->input('callback_query.from.id'))
                );

                return $isJoint === false;
            });

        if ($mandatoryMembershipChats->isEmpty()) {
            if ($update->input('callback_query.data') !== 'check-mandatory-chats-membership') {
                return Signal::Continue;
            }

            Telegram::perform('deleteMessage', [
                'chat_id' => $update->input('callback_query.message.chat.id'),
                'message_id' => $update->input('callback_query.message.message_id')
            ]);

            $gainer = $update->gainer();

            DB::transaction(function () use ($gainer) {
                $gainer->update(['stage' => PrimaryStage::class]);
                $gainer->temporarilyData()->delete();
            });

            Telegram::perform(
                'sendMessage',
                [
                    'text' => trans('telegram/texts.to-get-started-click-request-essay-button'),
                    'chat_id' => $update->input('callback_query.message.chat.id'),
                    'parse_mode' => 'HTML',
                ],
                PrimaryKeyboard::class
            );

            return Signal::Continue;
        }

        $buttons = $mandatoryMembershipChats->map(fn (MandatoryMembershipChat $mandatoryMembershipChat) => [[
            'text' => $mandatoryMembershipChat->title,
            'url' => $mandatoryMembershipChat->url,
        ]])
            ->add([[
                'text' => trans('telegram/buttons.ive-joined'),
                'callback_data' => 'check-mandatory-chats-membership'
            ]])
            ->all();

        Telegram::perform('answerCallbackQuery', [
            'callback_query_id' => $update->input('callback_query.id'),
            'text' => trans('telegram/texts.please-join-sponsor-channels-first')
        ]);

        if ($update->input('callback_query.data') === 'check-mandatory-chats-membership') {
            Telegram::perform('editMessageReplyMarkup', [
                'chat_id' => $update->input('callback_query.message.chat.id'),
                'message_id' => $update->input('callback_query.message.message_id'),
                'reply_markup' => [
                    'inline_keyboard' => $buttons
                ]
            ]);
        }

        return Signal::Exit;
    }

    public function handleMessage(Update $update): Signal
    {
        if ($update->input('message.chat.type') !== 'private') {
            return Signal::Continue;
        }

        $mandatoryMembershipChats = MandatoryMembershipChat::where('validity', true)
            ->orderBy('order_number')
            ->get()
            ->filter(function (MandatoryMembershipChat $mandatoryMembershipChat) use ($update) {
                $isJoint = Cache::driver('redis')->remember(
                    'is-' . $update->input('message.from.id') . '-joint-' . $mandatoryMembershipChat->chat_id,
                    5,
                    fn () => new MandatoryMembershipChecker()->checkMembership($mandatoryMembershipChat->chat_id, $update->input('message.from.id'))
                );

                return $isJoint === false;
            });

        if ($mandatoryMembershipChats->isEmpty()) {
            return Signal::Continue;
        }

        $buttons = $mandatoryMembershipChats->map(fn (MandatoryMembershipChat $mandatoryMembershipChat) => [[
            'text' => $mandatoryMembershipChat->title,
            'url' => $mandatoryMembershipChat->url,
        ]])
            ->add([[
                'text' => trans('telegram/buttons.ive-joined'),
                'callback_data' => 'check-mandatory-chats-membership'
            ]])
            ->all();

        Telegram::perform('sendMessage', [
            'text' => trans('telegram/texts.to-use-the-bot-you-must-join-sponsor-channels'),
            'chat_id' => $update->input('message.chat.id'),
            'reply_to_message_id' => $update->input('message.message_id'),
            'allow_sending_without_reply' => true,
            'disable_web_page_preview' => true,
            'parse_mode' => 'HTML',
            'reply_markup' => [
                'inline_keyboard' => $buttons,
            ]
        ]);

        return Signal::Exit;
    }
}
