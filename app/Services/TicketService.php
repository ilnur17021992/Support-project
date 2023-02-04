<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Auth;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TicketService
{
    public function createOrUpdate($user, $text, $ticket = null)
    {
        $bot = new TelegramBotService();

        if (empty($ticket)) $ticket = $user->tickets()->where('status', '!=', 'Closed')->latest()->first();

        if (empty($ticket)) {
            $ticket = Ticket::create([
                'title' => 'Telegram',
                'department' => 'Other',
                'user_id' => $user->id,
                'status' => 'New',
            ]);

            $bot->sendMessage($user->id, 'Благодарим Вас за обращение! Наши специалисты уже приступают к рассмотрению Вашего вопроса. Ожидайте ответа!');
        }

        if ($ticket->messages()->count() == 0) $type = 'Новый тикет 🛟';

        if (empty($type)) $type = checkPermission('platform.systems.support') ? 'Ответ тех. поддержки 👔' : 'Ответ пользователя ✉️';
        $group = checkPermission('platform.systems.support') ? 'Support:' : 'User:';
        $status = checkPermission('platform.systems.support') ? 'Processing' : 'New';

        $ticket->update(['status' => $status]);

        $message =
            '<b>Time: </b><code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
            '<b>Type: </b><code>' . $type . '</code>' . "\n" .
            '<b>ID: </b><code>' . $ticket->id . '</code>' . "\n" .
            '<b>Department: </b><code>' . Ticket::DEPARTMENT[$ticket->department] . '</code>' . "\n" .
            '<b>' . $group . '</b><code> ' . $user->name . '</code>' . "\n" .
            '<b>Title: </b><code>' . $ticket->title . '</code>' . "\n" .
            '<b>Message: </b><code>' . $text . '</code>' . "\n";

        $keyboard = new InlineKeyboardMarkup([[['text' => 'View ticket', 'url' => route('platform.ticket.messages', ['ticket' => $ticket->id])]]]);

        $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), $message, $keyboard);

        $ticket->messages()->create([
            'user_id' => $user->id,
            'message' => $text,
        ]);
    }
}
