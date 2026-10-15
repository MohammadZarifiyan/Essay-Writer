<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MohammadZarifiyan\Telegram\Facades\Telegram;

class TelegramController extends Controller
{
    public function handleRequest(Request $request)
    {
        Telegram::handleRequest($request);
    }
}
