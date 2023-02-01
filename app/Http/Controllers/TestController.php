<?php

namespace App\Http\Controllers;

use TelegramBot\Api\BotApi;
use Illuminate\Http\Request;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TestController extends Controller
{
    public function __invoke()
    {
        $bot = new BotApi(config('services.telegram_bot_api.token'));

        // Просто отправка сообщений
        // $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'hello!');

        // Меню из кнопок
        // $keyboard = new ReplyKeyboardMarkup(array(array("one", "two", "three")), true, true); // true for one-time keyboard
        // $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'hello!', null, false, null, $keyboard);

        // Кнопки прикрепленные к сообщению
        $keyboard = new InlineKeyboardMarkup(
            [
                [
                    ['text' => 'button1', 'url' => 'https://core.telegram.org'],['text' => 'button2', 'url' => 'https://core.telegram.org']
                ],
                [
                    ['text' => 'button3', 'url' => 'https://core.telegram.org']
                ]
            ]
        );

        $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'Hello!', null, false, null, $keyboard);
    }
}
