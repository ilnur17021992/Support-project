<?php

namespace App\Orchid\Screens\Ticket;

use App\Models\Ticket;
use App\Notifications\TelegramNotification;
use App\Orchid\Layouts\Ticket\TicketMessagesLayout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

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
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::rows([
                    TextArea::make('message')
                        ->placeholder('Введите текст ответа')
                        ->rows(9),
                    Button::make('Отправить')
                        ->method('sendMessage')
                        ->type(Color::PRIMARY()),
                ]),

                Layout::rows([
                    Link::make($this->ticket->user->name)
                        ->title('Клиент:')
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
                        ->method('updateTicket')
                        ->type(Color::PRIMARY()),
                ]),
            ]),

            TicketMessagesLayout::class
        ];
    }

    public function sendMessage(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1024'],
        ]);

        $validated['user_id'] = Auth::id();

        $ticket->messages()->create($validated);
        $ticket->update(['status' => 'Processing']);

        // $telegramMessage =
        //     '<b>Time: </b>' . '<code>' . date('d.m.Y H:i:s') . '</code>' . "\n" .
        //     '<b>Type: </b>' . '<code>' . 'Ответ тех. поддержки 👔' . '</code>' . "\n" .
        //     '<b>ID: </b>' . '<code>' . $ticket->id . '</code>' . "\n" .
        //     '<b>Department: </b>' . '<code>' . Ticket::DEPARTMENT[$ticket->department] . '</code>' . "\n" .
        //     '<b>Admin: </b>' . '<code>' . auth('sanctum')->user()->name . '</code>' . "\n" .
        //     '<b>Title: </b>' . '<code>' . $ticket->title . '</code>' . "\n" .
        //     '<b>Message: </b>' . '<code>' . $validated['message'] . '</code>' . "\n";

        try {
            // $notification = new TelegramNotification($telegramMessage, $ticket->id);
            // Notification::send('telegram', $notification);

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

    public function removeMessage(Request $request, $id)
    {
        dd($id);
    }
}
