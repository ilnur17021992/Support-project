<?php

namespace App\Http\Controllers;

use CURLFile;
use Exception;
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Str;
use TelegramBot\Api\BotApi;
use Illuminate\Http\Request;
use App\Services\TicketService;
use Orchid\Platform\Models\Role;
use App\Services\TelegramBotService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        // $res->getMessageId()
        // $bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), 4, true); // 4 6 7
        // $bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), 6, true); // 4 6 7
        // $bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), 7, true); // 4 6 7
        // $bot->unpinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), 4);


        // $token = config('services.telegram_bot_api.token');
        // $ticket_chat_id = config('services.telegram_bot_api.ticket_chat_id');
        // echo file_get_contents("https://api.telegram.org/bot$token/unpinChatMessage?chat_id=$ticket_chat_id&message_id=4"); // Установить Webhook

        // $telegram = new BotApi($token);

        // $chat_id = $ticket_chat_id;
        // $message_id = 4;

        // try {
        //     $telegram->unpinChatMessage(['chat_id' => $chat_id, 'message_id' => $message_id]);
        //     echo "Message unpinned successfully";
        // } catch (Exception $e) {
        //     echo "Error unpinning message: " . $e->getMessage();
        // }


        // $response = Http::post('https://api.telegram.org/bot<token>/unpinChatMessage', [
        //     'chat_id' => config('services.telegram_bot_api.ticket_chat_id'),
        //     'message_id' => 4,
        // ]);

        // if ($response->successful()) {
        //     echo "Message unpinned successfully";
        // } else {
        //     echo "Error unpinning message: " . $e->getMessage();
        // }

        // try {
        //     $token = config('services.telegram_bot_api.token');

        //     $response = Http::post("https://api.telegram.org/bot$token/unpinChatMessage", [
        //         'chat_id' => config('services.telegram_bot_api.ticket_chat_id'),
        //         'message_id' => 7,
        //     ]);
        //     if ($response->successful()) {
        //         echo "Message unpinned successfully";
        //     } else {
        //         echo "Error unpinning message";
        //     }
        // } catch (Exception $e) {
        //     echo "Error unpinning message: " . $e->getMessage();
        // }



        // $bot = new TelegramBotService();

        // $bot->pinMessage(4);
        // $bot->pinMessage(6);
        // $bot->pinMessage(7);

        // $bot->unpinMessage(7);


        // echo test();


        // $tgUserId = 779740786;
        // $user = User::firstWhere('telegram_id', $tgUserId);

        // return $user->id;



        // public/files/yeFhZv43vVlbusOaWrEWrZt9IOhiF7mrdYDGFMYC.png

        //     return Storage::url('public/files/yeFhZv43vVlbusOaWrEWrZt9IOhiF7mrdYDGFMYC.png');



        //     $file = __DIR__ . '/storage/app/public/5GTFdAUAttBl6Ba4sjq6jgs8gAX0kjwPSQwUud4B.png';

        //     $contents = Storage::get('public/files/yeFhZv43vVlbusOaWrEWrZt9IOhiF7mrdYDGFMYC.png');

        //     $document = new CURLFile('public/files/yeFhZv43vVlbusOaWrEWrZt9IOhiF7mrdYDGFMYC.png');

        //     $bot->sendDocument(config('services.telegram_bot_api.ticket_chat_id'), $document);

        // ->where('created_at', '>=', Carbon::now()->subHour())->orderBy('created_at', 'asc')->get();
        $ticket = Ticket::find(48);
        return $ticket->messages()->orderBy('created_at', 'desc')->first()->telegram_message_id;
        return $ticket->messages()->latest()->get();
    }
}
