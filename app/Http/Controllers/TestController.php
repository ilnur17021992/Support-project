<?php

namespace App\Http\Controllers;

use App\Models\User;
use TelegramBot\Api\BotApi;
use Illuminate\Http\Request;
use Orchid\Platform\Models\Role;
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
        // $keyboard = new InlineKeyboardMarkup(
        //     [
        //         [
        //             ['text' => 'button1', 'url' => 'https://core.telegram.org'],['text' => 'button2', 'url' => 'https://core.telegram.org']
        //         ],
        //         [
        //             ['text' => 'button3', 'url' => 'https://core.telegram.org']
        //         ]
        //     ]
        // );

        // $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'Hello!', null, false, null, $keyboard);


        // $token = config('services.telegram_bot_api.token');
        // $url = 'https://opengpt.online/api/bot';
        // echo file_get_contents("https://api.telegram.org/bot$token/setWebhook?url=$url"); // Установить Webhook
        // echo file_get_contents("https://api.telegram.org/bot$token/getWebhookInfo"); // Проверить Webhook
        // echo file_get_contents("https://api.telegram.org/bot$token/deleteWebhook"); // Удалить Webhook

        $role = Role::find(1);

        User::find(779740786)->addRole($role);

        echo test();
    }
}
