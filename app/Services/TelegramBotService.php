<?php

namespace App\Services;

use App\Models\User;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TelegramBotService
{
    public function __invoke()
    {
        $bot = new Client(config('services.telegram_bot_api.token'));

        $bot->command('start', function ($message) {
            createUser($message);
        });

        $bot->on(function (Update $update) {
            $message = $update->getMessage();
            if (empty($message)) exit;

            $id = $message->getChat()->getId();
            $text = $message->getText();
            $user = User::find($id);

            if (empty($user)) return $this->sendMessage($id, 'Для начала воспользуйтесь командой: /start');

            $this->sendMessage($id, 'Your message: ' . $text);
        }, function () {
            return true;
        });

        $bot->run();
    }

    public function sendMessage($id, $message, $keyboard = null)
    {
        $bot = new BotApi(config('services.telegram_bot_api.token'));
        $bot->sendMessage($id, $message, 'HTML', false, null, $keyboard);
    }
}
