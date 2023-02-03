<?php

namespace App\Services;

use App\Models\User;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;
use Illuminate\Support\Facades\Auth;

class TicketService
{
    public function __invoke($message)
    {
        // $bot = new Client(config('services.telegram_bot_api.token'));
        // $bot->sendMessage($id, 'Для начала воспользуйтесь командой: /start');


        // $validated['user_id'] = Auth::id();

        // $ticket->messages()->create($validated);
        // $ticket->update(['status' => 'Processing']);

        $user = Auth::user();


        return $user;

        dd($message);
    }
}
