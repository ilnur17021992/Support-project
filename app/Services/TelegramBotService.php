<?php

namespace App\Services;

use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TelegramBotService
{
    public function __invoke()
    {
        $bot = new Client(config('services.telegram_bot_api.token'));

        $bot->command('start', function ($message) use ($bot) {
            createUser($bot, $message);
        });

        $bot->on(function (Update $update) use ($bot) {
            $message = $update->getMessage();
            if (empty($message)) exit;

            $id = $message->getChat()->getId();
            $bot->sendMessage($id, 'Your message: ' . $message->getText());
        }, function () {
            return true;
        });

        $bot->run();
    }
}
