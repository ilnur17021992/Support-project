<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Str;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Http;

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

            $telegramId = $message->getChat()->getId();
            $user = User::firstWhere('telegram_id', $telegramId);
            $ticketService = new TicketService();

            if ($message->getChat()->getType() == 'private') {
                if (empty($user)) return $this->sendMessage($telegramId, 'Для начала воспользуйтесь командой: /start');

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

            if ($telegramId == config('services.telegram_bot_api.ticket_chat_id') && $message->getReplyToMessage()) {
                $ticketId = Str::of($message->getReplyToMessage()->getText())->match('/ID: ([0-9]+)/');
                
                if ($ticketId) {
                    $messageId = $message->getReplyToMessage()->getMessageId();
                    info($messageId);
                    $ticket = Ticket::find($ticketId);
                    $user = User::firstWhere('telegram_id', $message->getFrom()->getId());
                    $message = [
                        'user_id' => $user->id,
                        'message' => $message->getText(),
                    ];

                    $this->unpinMessage($messageId);
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

    public function pinMessage($id)
    {
        $bot = new BotApi(config('services.telegram_bot_api.token'));
        $bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), $id, true);
    }

    public function unpinMessage($id)
    {
        $token = config('services.telegram_bot_api.token');
        Http::post("https://api.telegram.org/bot$token/unpinChatMessage", [
            'chat_id' => config('services.telegram_bot_api.ticket_chat_id'),
            'message_id' => $id,
        ]);
    }
}
