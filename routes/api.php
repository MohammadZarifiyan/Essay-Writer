<?php

use App\Http\Controllers\Api\TelegramController;
use Illuminate\Support\Facades\Route;

Route::post('telegram/update', [TelegramController::class, 'update'])->name('api.telegram.update');
