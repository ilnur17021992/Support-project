<?php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TicketService
{
    public function create($ticketData)
    {
        $bot = new TelegramBotService();
        $ticket = Ticket::create($ticketData);

        $bot->sendMessage($ticket->user->telegram_id, 'Благодарим Вас за обращение! Наши специалисты уже приступают к рассмотрению Вашего вопроса. Ожидайте ответа!');
        $this->send($ticket, $ticketData);

        return $ticket;
    }

    public function send($ticket, $message)
    {
        $bot = new TelegramBotService();
        $user = User::find($message['user_id']);
        $group = $user->hasAccess('platform.systems.support') ? 'Support:' : 'User:';
        $status = $user->hasAccess('platform.systems.support') ? 'Processing' : 'New';
        $type = $user->hasAccess('platform.systems.support') ? 'Ответ тех. поддержки 👔' : 'Ответ пользователя ✉️';
        if ($ticket->messages()->count() == 0) $type = 'Новый тикет 🛟';

        $ticketMessage =
            '<b>Time: </b><code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
            '<b>Type: </b><code>' . $type . '</code>' . "\n" .
            '<b>ID: </b><code>' . $ticket->id . '</code>' . "\n" .
            '<b>Department: </b><code>' . Ticket::DEPARTMENT[$ticket->department] . '</code>' . "\n" .
            '<b>' . $group . '</b><code> ' . $user->name . '</code>' . "\n" .
            '<b>Title: </b><code>' . $ticket->title . '</code>' . "\n" .
            '<b>Message: </b><code>' . $message['message'] . '</code>' . "\n";

        $keyboard = new InlineKeyboardMarkup([[['text' => 'View ticket', 'url' => route('platform.ticket.messages', ['ticket' => $ticket->id])]]]);

        if ($user->id != $ticket->user->id) $bot->sendMessage($ticket->user->telegram_id, $message['message']);
        $response = $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), $ticketMessage, $keyboard);
        $pinnedMessageId = $ticket->messages()->latest()->first()?->telegram_message_id;
        $messageId = $response->getMessageId();
        $message['telegram_message_id'] = $messageId;

        $user->hasAccess('platform.systems.support') ?: $bot->pinMessage($messageId);
        $bot->unpinMessage($pinnedMessageId);
        $ticket->update(['status' => $status]);
        $ticket->messages()->create($message);
    }
}
