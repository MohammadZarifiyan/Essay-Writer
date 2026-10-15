<?php

namespace App\Http\Controllers;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class AuthController
{
    public function redirect()
    {
        return Socialite::driver('telegram')->redirect();
    }

    public function callback()
    {
        $socialiteUser = Socialite::driver('telegram')->user();
        $apiKey = config('services.telegram.client_secret');
        $user = User::restoreOrCreate(
            [
                'bot_id' => Telegram::fresh($apiKey)->getBotId(),
                'telegram_id' => $socialiteUser->getId(),
            ],
            ['conversation_initiated' => false]
        );

        filament()->auth()->login($user, true);

        return redirect(filament()->getHomeUrl());
    }
}
