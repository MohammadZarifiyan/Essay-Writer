<?php

namespace App\Providers;

use App\Mixins\ArrMixin;
use App\Mixins\CarbonMixin;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Telegram\Provider as TelegramProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Arr::mixin(new ArrMixin);
        Carbon::mixin(new CarbonMixin);

        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('telegram', TelegramProvider::class);
        });
    }
}
