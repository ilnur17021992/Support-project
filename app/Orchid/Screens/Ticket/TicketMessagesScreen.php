<?php

namespace App\Orchid\Screens\Ticket;

use App\Models\Ticket;
use App\Orchid\Layouts\Ticket\TicketMessagesLayout;
use App\Services\TelegramBotService;
use App\Services\TicketService;
use Exception;
use Illuminate\Http\Request;
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
        return [
            Button::make(checkPermission('platform.systems.support') ? 'Закрыть тикет' : 'Проблема решена')
                ->icon(checkPermission('platform.systems.support') ? 'close' : 'check')
                ->confirm(checkPermission('platform.systems.support') ? 'Проблема пользователя решена' : 'Моя проблема решена')
                ->type(checkPermission('platform.systems.support') ? Color::DANGER() : Color::SUCCESS())
                ->canSee($this->ticket->status != 'Closed')
                ->method('closeTicket')
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
                    ->icon('paper-plane')
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
                    ->canSee(checkPermission('platform.systems.support')),
            ]),
        ];

        return [
            Layout::columns($rows),

            TicketMessagesLayout::class
        ];
    }

    public function sendMessage(Request $request, Ticket $ticket, TicketService $ticketService)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1024'],
        ]);

        try {
            if ($ticket->status == 'Closed') throw new Exception('Ошибка: тикет уже закрыт');
            $validated['user_id'] = auth()->id();
            $ticketService->send($ticket, $validated);

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
