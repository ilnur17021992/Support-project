<?php

namespace App\Orchid\Screens\Ticket;

use App\Models\Ticket;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Actions\ModalToggle;
use Illuminate\Support\Facades\Storage;
use App\Orchid\Layouts\Ticket\TicketListLayout;

class TicketListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tickets' => Ticket::filters()->defaultSort('status')->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Список тикетов';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Создать тикет')
                ->icon('plus')
                ->type(Color::PRIMARY())
                ->modal('modalCreateTicket')
                ->modalTitle('Создание тикета')
                ->method('createTicket'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            TicketListLayout::class,

            Layout::modal('modalCreateTicket', [
                Layout::rows([
                    Select::make('department')
                        ->options(Ticket::DEPARTMENT)
                        ->title('Отдел')
                        ->required(),

                    Input::make('title')
                        ->title('Заголовок')
                        ->type('text')
                        ->required(),

                    TextArea::make('message')
                        ->title('Сообщение')
                        ->rows(5)
                        ->required(),

                    // Input::make('file')
                    //     ->type('file'),
                ]),
            ])->applyButton('Создать'),
        ];
    }

    public function createTicket(Request $request): void
    {       
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
                'message' => ['required', 'string', 'max:1024'],
                'file' => ['nullable', 'mimes:pdf,png,jpg,gif', 'max:5120']
            ]);

            $validated['user_id'] = Auth::id();
            $validated['status'] = 'New';

            $message = $validated['message'];
            $path = isset($validated['file']) ? Storage::putFile('files', $validated['file'], 'public') : null;

            $ticket = Ticket::create($validated);
            $ticket->messages()->create([
                'user_id' => Auth::id(),
                'message' => $message,
                'file' => $path,
            ]);

            Toast::success('Тикет успешно создан.');
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

    public function removeTicket($id)
    {
        try {
            Ticket::find($id)->delete();
            Toast::success('Тикет успешно удален.');
        } catch (\Throwable $e) {
            info($e);
            Toast::error($e->getMessage());
        }
    }
}
