<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __invoke()
    {
        $token = config('services.telegram_bot_api.token');
        $url = 'https://opengpt.online/api/bot';
        echo file_get_contents("https://api.telegram.org/bot$token/setWebhook?url=$url"); // Установить Webhook
        echo file_get_contents("https://api.telegram.org/bot$token/getWebhookInfo"); // Проверить Webhook
        // echo file_get_contents("https://api.telegram.org/bot$token/deleteWebhook"); // Удалить Webhook
    }
}
