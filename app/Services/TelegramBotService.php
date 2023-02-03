<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

class TelegramBotService
{
    public function __construct(public TicketService $ticket)
    {
    }

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


            $ticket = $user->tickets()->where('status', '!=', 'Closed')->latest()->first();

            if (empty($ticket)) {
                $ticket = Ticket::create([
                    'title' => 'Telegram',
                    'department' => 'Other',
                    'user_id' => $id,
                    'status' => 'New',
                ]);

                $this->sendMessage($id, 'Благодарим Вас за обращение! Наши специалисты уже приступают к рассмотрению Вашего вопроса. Ожидайте ответа!');
            }

            $ticket->messages()->create([
                'user_id' => $id,
                'message' => $text,
            ]);
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
