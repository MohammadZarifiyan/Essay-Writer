<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use MohammadZarifiyan\Telegram\Facades\Telegram;
use MohammadZarifiyan\Telegram\Interfaces\SecretTokenRepository;

class ConfigureTheMainTelegramBotWebhook extends Command
{
    protected $signature = 'app:configure-telegram-bot-webhook';

    protected $description = 'Set up Telegram bot webhook.';

    public function handle(): int
    {
        /**
         * @var SecretTokenRepository $secretTokenRepository
         */
        $secretTokenRepository = App::make(SecretTokenRepository::class);
        $secretToken = $secretTokenRepository->get();

        if (Str::of($secretToken)->isEmpty()) {
            $this->warn('No secret key has been set!');
        }

        $response = Telegram::perform('setWebhook', [
            'url' => URL::route('api.telegram.update'),
            'secret_token' => $secretToken,
        ]);

        if ($response->json('result')) {
            $this->info($response->json('description'));
        }
        else {
            $this->error($response->json('description'));
        }

        return Command::SUCCESS;
    }
}
