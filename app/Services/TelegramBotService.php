<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Tariff;
use App\Models\Message;
use Illuminate\Support\Str;
use TelegramBot\Api\Client;
use App\Services\OpenAiService;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class TelegramBotService
{
    public function __invoke()
    {
        $bot = new \TelegramBot\Api\Client(config('services.telegram_bot_api.token'));

        $bot->command('start', function ($message) use ($bot) {
            createUser($bot, $message);
        });

        $bot->command('ping', function ($message) use ($bot) {
            $bot->sendMessage($message->getChat()->getId(), 'pong!');
        });

        //Handle text messages
        $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot) {
            $message = $update->getMessage();
            $id = $message->getChat()->getId();
            $bot->sendMessage($id, 'Your message: ' . $message->getText());
        }, function () {
            return true;
        });

        $bot->run();



        // $telegramBot = new Client(config('services.telegram.token'));

        // $telegramBot->command('start', function ($request) {
        //     $user = findOrCreateUser($request);
        //     $userLanguage = $user->language;

        //     $this->sendMessage($request, getWelcomeMessage($user?->first_name, $userLanguage), 'HTML');
        // });

        // $telegramBot->command('reset', function ($request) {
        //     $user = findOrCreateUser($request);
        //     $user->messages()->delete();
        //     $userLanguage = $user->language;

        //     $this->sendMessage($request, getResetMessage($userLanguage), 'HTML');
        // });

        // $telegramBot->command('help', function ($request) {
        //     $userLanguage = findOrCreateUser($request)->language;

        //     $this->sendMessage($request, getHelpMessage($userLanguage));
        // });

        // $telegramBot->command('tariff', function ($request) {
        //     $user = findOrCreateUser($request);
        //     $tariff =  $user->tariff->name;
        //     $userLanguage = $user->language;

        //     $this->sendMessage($request, getTariffsMessage($tariff, $userLanguage), 'HTML');
        // });

        // $telegramBot->command('feedback', function ($request) {
        //     $userLanguage = findOrCreateUser($request)->language;

        //     $this->sendMessage($request, getFeedbackMessage($userLanguage), 'HTML');
        // });

        // $telegramBot->on(function (Update $request) {
        //     $request = $request->getMessage();

        //     if (empty($request)) exit;

        //     $messageText = $request->getText();
        //     $chatId = $request->getChat()->getId();
        //     $userId = $request->getFrom()->getId();
        //     $user = findOrCreateUser($request);
        //     $userRequest = $messageText;

        //     $language_code = $request->getFrom()->getLanguageCode(); // FIX удалить потом
        //     $user->update(['language' => $language_code]); // FIX удалить потом
        //     $user->refresh(); // FIX удалить потом

        //     $userLanguage = $user->language;

        //     $timeDiff = time() - strtotime($user->created_at);
        //     $userTariff = $timeDiff <= 600 ? Tariff::find(3) : $user->tariff;
        //     $userMessages = $user->messages()->where('created_at', '>=', Carbon::now()->subHour())->orderBy('created_at', 'asc')->get();


        //     if ($user->per_minute >= $userTariff->per_minute) return $this->sendMessage($request, getLimitMessage('minute', $user, $userLanguage), 'HTML');
        //     if ($user->per_day >= $userTariff->per_day) return $this->sendMessage($request, getLimitMessage('day', $user, $userLanguage), 'HTML');

        //     if (Str::startsWith($messageText, '/bot@Chat_GPT_Free_Bot')) {
        //         $userRequest = Str::after($messageText, '/bot@Chat_GPT_Free_Bot');
        //     } else if (Str::startsWith($messageText, '/bot')) {
        //         $userRequest = Str::after($messageText, '/bot');
        //     }

        //     if ($chatId != $userId && !Str::startsWith($messageText, ['/bot@Chat_GPT_Free_Bot', '/bot'])) exit;
        //     if (Str::length($userRequest) > $userTariff->max_request_tokens) return $this->sendMessage($request, getLimitMessage('tokens', $user, $userLanguage), 'HTML');

        //     $context = implode("\n", collect($userMessages)->take(-$user->tariff->max_context_count)->pluck('text')->all());
        //     $context .= "\n" . $userRequest;

        //     if (!empty($userRequest)) {
        //         $openAiService = new OpenAiService();
        //         $openResponse = $openAiService(Str::substr($context, -$user->tariff->max_context_tokens));
        //         $user->increment('per_minute');
        //         $user->increment('per_day');
        //         $this->sendMessage($request, $openResponse . "\n\n" . getSignatureMessage($userLanguage), 'HTML'); // HTML, Markdown, MarkdownV2

        //         Message::create([
        //             'user_id' => $userId,
        //             'text' => trim($userRequest),
        //         ]);

        //         Message::create([
        //             'user_id' => $userId,
        //             'text' => trim($openResponse),
        //         ]);
        //     } else {
        //         $this->sendMessage($request, getEmptyMessage($userLanguage), 'HTML');
        //     }
        // }, function () {
        //     return true;
        // });

        // $telegramBot->run();
    }

    public function sendMessage($request, $message, $parseMode = null)
    {
        $telegramBot = new Client(config('services.telegram.token'));

        if (empty($request)) exit;
        $chatId = $request->getChat()->getId();
        $messageText = $request->getText();
        $fromUser = $request->getFrom();
        $userId = $fromUser->getId();
        $userName = $fromUser->getUsername();
        $firstName = $fromUser->getFirstName();
        $lastName = $fromUser->getLastName();
        $response = $telegramBot->sendMessage($chatId, $message, $parseMode);

        $telegramMessage = // History
            '<b>Chat ID: </b>' . '<code>' . $chatId . '</code>' . "\n" .
            '<b>User ID: </b>' . '<code>' . $userId . '</code>' . "\n" .
            '<b>User name: </b>' . '<code>' . $userName . '</code>' . "\n" .
            '<b>First name: </b>' . '<code>' . $firstName . '</code>' . "\n" .
            '<b>Last name: </b>' . '<code>' . $lastName . '</code>' . "\n" .
            '<b>Request: </b>' . '<code>' . $messageText . '</code>' . "\n" .
            '<b>Response:</b> <code>' . $message . '</code>';

        $telegramBot->sendMessage('-1001829766169', $telegramMessage, 'HTML'); // -800152046

        Log::channel('history')->info('Request:', [
            'chatId' => $chatId,
            'messageText' => $messageText,
            'userId' => $userId,
            'userName' => $userName,
            'firstName' => $firstName,
            'lastName' => $lastName,
        ]);
    }
}
