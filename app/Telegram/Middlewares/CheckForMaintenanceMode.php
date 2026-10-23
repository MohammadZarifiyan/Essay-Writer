<?php

namespace App\Telegram\Middlewares;

use Illuminate\Support\Facades\App;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Update;
use Symfony\Component\HttpFoundation\Response;

class CheckForMaintenanceMode
{
    public function handleMessage(Update $update)
    {
        if (!App::isDownForMaintenance()) {
            return $update;
        }

        if ($update->input('message.chat.type') === 'private') {
            Telegram::perform('sendMessage', [
                'chat_id' => $update->input('message.chat.id'),
                'text' => trans('telegram/texts.down-for-maintenance'),
            ]);
        }
    }

    public function handleCallbackQuery(Update $update)
    {
        if (!App::isDownForMaintenance()) {
            return $update;
        }

        if ($update->input('message.chat.type') === 'private') {
            Telegram::perform('answerCallbackQuery', [
                'callback_query_id' => $update->input('callback_query.id'),
                'text' => trans('telegram/texts.down-for-maintenance')
            ]);
        }
    }

    public function handle(Update $update): Update
    {
        abort_if(
            App::isDownForMaintenance(),
            Response::HTTP_SERVICE_UNAVAILABLE
        );

        return $update;
    }
}
