<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Str;
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
            $user = User::find($id);
            $ticketService = new TicketService();

            if ($message->getChat()->getType() == 'private') {
                if (empty($user)) return $this->sendMessage($id, 'Для начала воспользуйтесь командой: /start');

                $ticket = $user->tickets()->where('status', '!=', 'Closed')->latest()->first();
                $ticketData = [
                    'title' => 'Telegram',
                    'department' => 'Other',
                    'message' => $message->getText(),
                    'user_id' => $user->id,
                    'status' => 'New',
                ];

                empty($ticket)
                    ? $ticketService->create($ticketData)
                    : $ticketService->send($ticket, $ticketData);
            }

            // FIX Нужно отключить Privacy Mode у бота в BotFather: https://core.telegram.org/bots/features#privacy-mode
            if ($id == config('services.telegram_bot_api.ticket_chat_id') && $message->getReplyToMessage()) {
                $ticketId = Str::of($message->getReplyToMessage()->getText())->match('/ID: ([0-9]+)/');

                if ($ticketId) {
                    $ticket = Ticket::find($ticketId);
                    $message = [
                        'user_id' => $message->getFrom()->getId(),
                        'message' => $message->getText(),
                    ];
                    $ticketService->send($ticket, $message);
                }
            }
        }, function () {
            return true;
        });

        $bot->run();
    }

    public function sendMessage($id, $message, $keyboard = null)
    {
        $bot = new BotApi(config('services.telegram_bot_api.token'));
        return $bot->sendMessage($id, $message, 'HTML', false, null, $keyboard);
    }
}
