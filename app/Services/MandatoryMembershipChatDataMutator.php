<?php

namespace App\Services;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class MandatoryMembershipChatDataMutator
{
    public function mutate(array $data, Action $action): array
    {
        $response = Telegram::perform('getChat', [
            'chat_id' => $data['chat_id'],
        ]);

        if (!$response->json('ok')) {
            Notification::make()
                ->danger()
                ->title(__('filament/resources/mandatory-membership-chats/index.notifications.chat-not-found-or-bot-lacks-admin-privileges'))
                ->send();

            $action->halt();
        }

        if (!in_array($response->json('result.type'), ['channel', 'group', 'supergroup'])) {
            Notification::make()
                ->danger()
                ->title(__('filament/resources/mandatory-membership-chats/index.notifications.selected-chat-must-be-channel-or-group'))
                ->send();

            $action->halt();
        }

        $data['validity'] = true;

        if (blank($data['title'] ?? null)) {
            $data['title'] = $response->json('result.title');
        }

        if (blank($data['url'] ?? null)) {
            $data['url'] = match (true) {
                filled($response->json('result.username') ?? null) => 'https://t.me/' . $response->json('result.username'),
                filled($chat['invite_link'] ?? null) => $response->json('result.invite_link'),
                default => $this->createChatInviteLink($data['chat_id'], $action),
            };
        }

        return $data;
    }

    protected function createChatInviteLink(int|string $chatId, Action $action): string
    {
        $response = Telegram::perform('createChatInviteLink', ['chat_id' => $chatId]);

        if (!$response->json('ok')) {
            Notification::make()
                ->danger()
                ->title(__('telegram/texts.chat-not-found-or-bot-lacks-admin-privileges'))
                ->send();

            $action->halt();
        }

        return $response->json('result.invite_link');
    }
}
