<?php

namespace App\Http\Controllers\Api;

use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use Illuminate\Http\Request;
use TelegramBot\Api\Exception;
use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;

class TelegramController extends Controller
{
    public function __invoke(Request $request, TelegramBotService $telegramBot)
    {
        try {
            $telegramBot($request);
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
