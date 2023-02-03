<?php

namespace App\Http\Controllers;

use App\Models\User;
use TelegramBot\Api\BotApi;
use Illuminate\Http\Request;
use App\Services\TicketService;
use Orchid\Platform\Models\Role;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TestController extends Controller
{
    public function __invoke(TicketService $test)
    {
        $bot = new BotApi(config('services.telegram_bot_api.token'));

        // Просто отправка сообщений
        // $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'hello!');

        // Меню из кнопок
        // $keyboard = new ReplyKeyboardMarkup(array(array("one", "two", "three")), true); // true for one-time keyboard
        // $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), 'hello!', null, false, null, $keyboard);

        // Удалить меню из кнопок
        // $keyboard = new ReplyKeyboardRemove();
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



        return $test('rjnrj3');


        // $user = User::find(779740786);


        // $ticket = $user->tickets()->where('status', '!=', 'Closed')->latest()->first();

        // $ticket->messages()->create([
        //     'user_id' => 779740786,
        //     'message' => 'ruwieryuiewyriewy'
        // ]);


        // return $ticket;

        // $ticket->messages()->create($validated);


        echo test();
    }
}
