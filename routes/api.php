<?php

use App\Http\Controllers\Api\TelegramController;
use Illuminate\Support\Facades\Route;

Route::post('telegram/update', [TelegramController::class, 'handleRequest'])->name('api.telegram.update');
