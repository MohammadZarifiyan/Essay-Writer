<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('login', [AuthController::class, 'redirect'])->middleware('guest')->name('login');
Route::get('auth/callback', [AuthController::class, 'callback'])->middleware('guest');
