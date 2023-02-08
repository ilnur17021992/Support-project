<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Ticket;
use TelegramBot\Api\Client;
use Orchid\Platform\Models\Role;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Http;

class TelegramBotService
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

            // Обработка нажатия кнопки закрытия тикета
            $getCallbackQuery = $update->getCallbackQuery();
            $queryData = $getCallbackQuery?->getData();
            $queryText = $getCallbackQuery?->getMessage()->getText();

            if ($queryData === 'close_ticket') {
                $ticketId = getTicketId($queryText);
                $result = $ticketService->close($ticketId);
                $message = $result ? '✅ Тикет с ID ' . $ticketId . ' успешно закрыт.' : '🛑 Тикет с ID ' . $ticketId . ' уже закрыт.';
                $this->bot->answerCallbackQuery($update->getCallbackQuery()->getId(), $message, false);
            }

            $message = $update->getMessage();
            if (empty($message)) exit;

            // Получение сообщения
            $fromId = $message->getFrom()->getId();
            $user = User::firstWhere('telegram_id', $fromId);

            if ($message->getChat()->getType() == 'private') {
                if (empty($user)) return $this->sendMessage($fromId, 'Для начала воспользуйтесь командой: /start');

                $ticket = $user->tickets()->where('status', '!=', 'closed')->latest()->first();
                $ticketData = [
                    'title' => 'Telegram',
                    'department' => 'other',
                    'message' => $message->getText(),
                    'user_id' => $user->id,
                    'status' => 'new',
                ];

                empty($ticket)
                    ? $ticketService->create($ticketData)
                    : $ticketService->send($ticket, $ticketData);
            }

            // Обработка цитирования в чате поддержки
            if ($fromId == config('services.telegram_bot_api.ticket_chat_id') && $message->getReplyToMessage()) {
                $quotedText = $message->getReplyToMessage()->getText();
                $ticketId = getTicketId($quotedText);

                if ($ticketId) {
                    $messageId = $message->getReplyToMessage()->getMessageId();
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

        $this->bot->run();
    }

    public function sendMessage($id, $message, $keyboard = null)
    {
        return $this->bot->sendMessage($id, $message, 'HTML', false, null, $keyboard);
    }

    public function pinMessage($id)
    {
        return $this->bot->pinChatMessage(config('services.telegram_bot_api.ticket_chat_id'), $id, true);
    }

    public function unpinMessage($id)
    {
        $token = config('services.telegram_bot_api.token');
        $response = Http::post("https://api.telegram.org/bot$token/unpinChatMessage", [
            'chat_id' => config('services.telegram_bot_api.ticket_chat_id'),
            'message_id' => $id,
        ]);

        if (!$response['ok']) throw new Exception($response);
    }
}
