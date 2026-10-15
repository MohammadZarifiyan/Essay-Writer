<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Exceptions\TelegramValidationException;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->preventRequestsDuringMaintenance(['/api/telegram/update/*']);
        $middleware->trustProxies('*',
            SymfonyRequest::HEADER_FORWARDED |
            SymfonyRequest::HEADER_X_FORWARDED_FOR |
            SymfonyRequest::HEADER_X_FORWARDED_HOST |
            SymfonyRequest::HEADER_X_FORWARDED_PROTO |
            SymfonyRequest::HEADER_X_FORWARDED_PORT |
            SymfonyRequest::HEADER_X_FORWARDED_PREFIX
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
        $exceptions->dontReport(\MohammadZarifiyan\Telegram\Exceptions\TelegramAuthorizationException::class);
        $exceptions->dontReport(\MohammadZarifiyan\Telegram\Exceptions\TelegramCommandHandlerNotFoundException::class);
        $exceptions->dontReport(\MohammadZarifiyan\Telegram\Exceptions\TelegramMiddlewareFailedException::class);
        $exceptions->reportable(function (TelegramValidationException $exception) {
            $error = $exception->validator->errors()->first();
            $update = Telegram::getUpdate();

            if ($update->type() === 'callback_query') {
                Telegram::perform('answerCallbackQuery', [
                    'text' => trans('telegram/texts.error', compact('error')),
                    'callback_query_id' => $update->input('callback_query.id'),
                ]);
            }
            else {
                $gainer = $update->gainer();

                Telegram::perform('sendMessage', [
                    'text' => trans('telegram/texts.error', compact('error')),
                    'chat_id' => $gainer->telegram_id,
                    'disable_web_page_preview' => true
                ]);
            }
        })->stop();
    })->create();
