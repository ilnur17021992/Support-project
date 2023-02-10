<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Telegram\BotService;
use Illuminate\Http\Request;
use TelegramBot\Api\Exception;

class TelegramController extends Controller
{
    public function __invoke(Request $request, BotService $bot)
    {
        try {
            $bot($request); // FIX
        } catch (Exception $e) {
            $e->getMessage();
        }
    }
}
