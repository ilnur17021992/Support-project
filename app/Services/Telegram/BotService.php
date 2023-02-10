<?php

namespace App\Services\Telegram;

use Exception;
use App\Models\User;
use App\Models\Ticket;
use App\Services\Support\Message;
use App\Services\Support\Ticket as SupportTicket;
use TelegramBot\Api\Client;
use Orchid\Platform\Models\Role;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Http;
use App\Services\Support\TicketService;

class BotService
{
    private $bot;

    public function __construct()
    {
        $this->bot = new Client(config('services.telegram_bot_api.token'));
    }

    public function __invoke()
    {
        $this->bot->command('start', function ($message) {
            createUser($message);
        });

        $this->bot->command('admin', function ($message) {
            $chatId = $message->getChat()->getId();
            $fromId = $message->getFrom()->getId();

            if ($chatId == config('services.telegram_bot_api.ticket_chat_id')) {
                $user = User::firstWhere('telegram_id', $fromId);
                $admin = Role::firstWhere('slug', 'admin');

                if (empty($user)) return $this->sendMessage($chatId, 'Для начала воспользуйтесь командой: /start');
                if ($user->inRole($admin)) return $this->sendMessage($chatId, 'У вас уже есть роль Admin.');

                $user->addRole($admin);
                $this->sendMessage($chatId, 'Роль Admin успешно выдана.');
            }
        });

        $this->bot->command('support', function ($message) {
            $chatId = $message->getChat()->getId();
            $fromId = $message->getFrom()->getId();

            if ($chatId == config('services.telegram_bot_api.ticket_chat_id')) {
                $user = User::firstWhere('telegram_id', $fromId);
                $support = Role::firstWhere('slug', 'support');

                if (empty($user)) return $this->sendMessage($chatId, 'Для начала воспользуйтесь командой: /start');
                if ($user->inRole($support)) return $this->sendMessage($chatId, 'У вас уже есть роль Support.');

                $user->addRole($support);
                $this->sendMessage($chatId, 'Роль Support успешно выдана.');
            }
        });

        $this->bot->on(function (Update $update) {
            $ticketService = new TicketService;
            $message = $update->getMessage();
            $chatId = $message?->getChat()->getId();
            $fromId = $message?->getFrom()->getId();
            $user = User::firstWhere('telegram_id', $fromId);

            // Обработка нажатия кнопки закрытия тикета
            $getCallbackQuery = $update->getCallbackQuery();
            $queryData = $getCallbackQuery?->getData();
            $queryText = $getCallbackQuery?->getMessage()->getText();

            if ($queryData === 'close_ticket') {
                $ticketId = getTicketId($queryText);
                $result = $ticketService->close($ticketId);
                $message = $result ? '✅ Тикет с ID ' . $ticketId . ' успешно закрыт.' : '🛑 Тикет с ID ' . $ticketId . ' уже закрыт.';
                $this->bot->answerCallbackQuery($update->getCallbackQuery()->getId(), $message, false);
                exit;
            }

            // Обработка сообщений
            if ($message?->getChat()->getType() == 'private') {
                if (empty($user)) return $this->sendMessage($fromId, 'Для начала воспользуйтесь командой: /start');

                $ticket = $user->tickets()->where('status', '!=', 'closed')->latest()->first();

                if (empty($ticket)) $ticket = $ticketService->create(new SupportTicket(
                    $user->id,
                    'Telegram',
                    'other',
                    'new',
                ));

                $ticketService->send($ticket, new Message(
                    $user->id,
                    $message->getText(),
                ));
            }

            // Обработка цитирования в чате поддержки
            if ($chatId == config('services.telegram_bot_api.ticket_chat_id') && $message->getReplyToMessage()) {
                $quotedText = $message->getReplyToMessage()->getText();
                $ticketId = getTicketId($quotedText);

                if ($ticketId) {
                    $messageId = $message->getReplyToMessage()->getMessageId();
                    $ticket = Ticket::find($ticketId);
                    $user = User::firstWhere('telegram_id', $message->getFrom()->getId());

                    $this->bot->sendChatAction($chatId, 'typing');
                    $this->unpinMessage($messageId);
                    $ticketService->send($ticket, new Message(
                        $user->id,
                        $message->getText(),
                    ));
                }
            }
        }, function () {
            return true;
        });

        $this->bot->run();
    }

    public function sendMessage($chatId, $message, $keyboard = null)
    {
        $this->bot->sendChatAction($chatId, 'typing');
        return $this->bot->sendMessage($chatId, $message, 'HTML', false, null, $keyboard);
    }

    public function pinMessage($messageId)
    {
        return $this->bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), $messageId, true);
    }

    public function unpinMessage($messageId)
    {
        $token = config('services.telegram_bot_api.token');
        $response = Http::post("https://api.telegram.org/bot$token/unpinChatMessage", [
            'chat_id' => config('services.telegram_bot_api.ticket_chat_id'),
            'message_id' => $messageId,
        ]);

        if (!$response['ok']) throw new Exception($response);
    }
}
