<?php

namespace App\Providers;

use App\Mixins\ArrMixin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Telegram\Provider as TelegramProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Arr::mixin(new ArrMixin);

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('telegram', TelegramProvider::class);
        });
    }
}
