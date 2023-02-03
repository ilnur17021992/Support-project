<?php

namespace App\Orchid\Screens\Ticket;

use App\Models\Ticket;
use App\Orchid\Layouts\Ticket\TicketMessagesLayout;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class TicketMessagesScreen extends Screen
{
    public $ticket;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Ticket $ticket): iterable
    {
        return [
            'ticket' => $ticket,
            'messages' => $ticket->messages()->defaultSort('id', 'desc')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Просмотр тикета ID: ' . $this->ticket->id;
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return checkRole('user')
            ? [
                Button::make('Проблема решена')
                    ->icon('check')
                    ->method('closeTicket')
                    ->confirm('Моя проблема решена')
                    ->type(Color::SUCCESS())
                    ->canSee($this->ticket->status != 'Closed'),
            ]
            : [
                Button::make('Закрыть тикет')
                    ->icon('close')
                    ->method('closeTicket')
                    ->confirm('Проблема пользователя решена')
                    ->type(Color::DANGER())
                    ->canSee($this->ticket->status != 'Closed'),
            ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $rows = [];

        if ($this->ticket->status != 'Closed') $rows[] = [
            Layout::rows([
                TextArea::make('message')
                    ->placeholder('Введите текст ответа')
                    ->rows(9),
                Button::make('Отправить')
                    ->icon('cursor')
                    ->method('sendMessage')
                    ->type(Color::PRIMARY()),
            ]),
        ];

        $rows[] = [
            Layout::rows([
                Link::make($this->ticket->user->name)
                    ->title('Пользователь:')
                    ->route('platform.systems.users.edit', $this->ticket->user_id)
                    ->horizontal(),

                Select::make('department')
                    ->title('Отдел:')
                    ->options(Ticket::DEPARTMENT)
                    ->value($this->ticket->department)
                    ->horizontal(),

                Input::make('title')
                    ->title('Заголовок:')
                    ->value($this->ticket->title)
                    ->horizontal(),

                Select::make('status')
                    ->title('Статус:')
                    ->options(Ticket::STATUS)
                    ->value($this->ticket->status)
                    ->horizontal(),

                Button::make('Обновить')
                    ->icon('refresh')
                    ->method('updateTicket')
                    ->type(Color::INFO())
                    ->canSee(checkRole('support')),
            ]),
        ];

        return [
            Layout::columns($rows),

            TicketMessagesLayout::class
        ];
    }

    public function sendMessage(Request $request, Ticket $ticket, TelegramBotService $bot)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1024'],
        ]);

        try {
            $validated['user_id'] = Auth::id();
            $ticket->messages()->create($validated);

            $from = checkRole('user') ? 'Ответ пользователя ✉️' : 'Ответ тех. поддержки 👔';
            $group = checkRole('user') ? 'User:' : 'Support:';
            $status = checkRole('user') ? 'New' : 'Processing';

            $ticket->update(['status' => $status]);

            $message =
                '<b>Time: </b><code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
                '<b>Type: </b><code>' . $from . '</code>' . "\n" .
                '<b>ID: </b><code>' . $ticket->id . '</code>' . "\n" .
                '<b>Department: </b><code>' . Ticket::DEPARTMENT[$ticket->department] . '</code>' . "\n" .
                '<b>' . $group . '</b><code> ' . auth('sanctum')->user()->name . '</code>' . "\n" .
                '<b>Title: </b><code>' . $ticket->title . '</code>' . "\n" .
                '<b>Message: </b><code>' . $validated['message'] . '</code>' . "\n";

            $keyboard = new InlineKeyboardMarkup([[['text' => 'View ticket', 'url' => route('platform.ticket.messages', ['ticket' => $ticket->id])]]]);

            $bot->sendMessage(config('services.telegram_bot_api.ticket_chat_id'), $message, $keyboard);

            Toast::success('Сообщение успешно отправлено.');
        } catch (\Throwable $e) {
            info($e);
            Toast::error($e->getMessage());
        }
    }

    public function updateTicket(Request $request, Ticket $ticket)
    {
        try {
            $ticket->update($request->all());
            Toast::success('Тикет успешно обновлен.');
        } catch (\Throwable $e) {
            info($e);
            Toast::error($e->getMessage());
        }
    }

    public function closeTicket(Ticket $ticket)
    {
        try {
            $ticket->update(['status' => 'Closed']);
            Toast::success('Тикет успешно закрыт.');
        } catch (\Throwable $e) {
            info($e);
            Toast::error($e->getMessage());
        }
    }

    public function removeMessage(Request $request, $id)
    {
        dd($id);
    }
}
