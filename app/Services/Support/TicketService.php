<?php

namespace App\Services\Support;

use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\DashboardAlert;
use App\Services\Support\Message as SupportMessage;
use App\Services\Support\Ticket as SupportTicket;
use App\Services\Telegram\BotService;
use Illuminate\Support\Facades\Storage;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TicketService
{
    public function __construct(private $bot = new BotService)
    {
    }

    public function create(SupportTicket $ticketData): Ticket
    {
        $ticket = Ticket::create((array) $ticketData);

        $this->bot->sendMessage($ticket->user->telegram_id, 'Благодарим вас за обращение! Наши специалисты уже приступают к рассмотрению вашего вопроса. Ожидайте ответа!');

        return $ticket;
    }

    public function send(Ticket $ticket, SupportMessage $message): Message
    {
        $user = User::find($message->user_id);
        $text = $message->text;
        $url = route('platform.ticket.messages', ['ticket' => $ticket->id]);
        $group = $user->hasAccess('platform.systems.support') ? 'Support:' : 'User:';
        $status = $user->hasAccess('platform.systems.support') ? 'processing' : 'new';
        $type = $user->hasAccess('platform.systems.support') ? 'Ответ тех. поддержки 👔' : 'Ответ пользователя ✉️';
        if ($ticket->messages()->count() == 0) $type = 'Новый тикет 🛟';

        $user->hasAccess('platform.systems.support')
            ? $ticket->user->notify(new DashboardAlert($user->name, $text, $url))
            : User::where('permissions->platform.systems.support', 1)->get()->each(fn ($support) => $support->notify(new DashboardAlert($user->name, $text, $url)));

        $ticketMessage =
            '<b>Time: </b><code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
            '<b>Type: </b><code>' . $type . '</code>' . "\n" .
            '<b>ID: </b><code>' . $ticket->id . '</code>' . "\n" .
            '<b>Department: </b><code>' . Ticket::DEPARTMENT[$ticket->department] . '</code>' . "\n" .
            '<b>' . $group . '</b> <code>' . $user->name . '</code>' . "\n" .
            '<b>Title: </b><code>' . $ticket->title . '</code>' . "\n" .
            '<b>Message: </b><code>' . $text . '</code>' . "\n";

        $buttons = [['text' => '🛟 View', 'url' => $url]];
        if (isset($message->file)) $buttons[] = ['text' => '💾 Open', 'url' => Storage::url($message->file)];
        $buttons[] = ['text' => '❌ Close', 'callback_data' => 'close_ticket'];
        $keyboard = new InlineKeyboardMarkup([$buttons]);

        if ($user->id != $ticket->user->id) $this->bot->sendMessage($ticket->user->telegram_id, $text);
        $messageId = $this->bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), $ticketMessage, $keyboard)->getMessageId();
        $message->telegram_message_id = $messageId;
        $lastMessage = $ticket->messages()->latest()->first();

        if ($lastMessage) $this->bot->unpinMessage($lastMessage->telegram_message_id);
        $user->hasAccess('platform.systems.support') ?: $this->bot->pinMessage($messageId);
        $ticket->update(['status' => $status]);
        $message = $ticket->messages()->create((array) $message); // FIX ???

        return $message;
    }

    public function close($id): bool // FIX тип int
    {
        $ticket = Ticket::find($id);
        $lastMessage = $ticket->messages()->latest()->first();;

        if ($ticket->status == 'closed') return false;

        $ticket->update(['status' => 'closed']);
        $this->bot->unpinMessage($lastMessage->telegram_message_id);
        $this->bot->sendMessage($ticket->user->telegram_id, 'Спасибо, что обратились в нашу службу поддержки. Если у вас возникнут дополнительные вопросы, мы будем рады на них ответить!');

        return true;
    }
}
