<?php

namespace App\Orchid\Screens\Ticket;

use Exception;
use App\Models\Ticket;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Illuminate\Http\Request;
use App\Services\TicketService;
use Illuminate\Validation\Rule;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Actions\ModalToggle;
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
        if (checkPermission('platform.systems.support')) return  [
            'tickets' => Ticket::filters()->defaultSort('id', 'desc')->paginate(),
        ];

        return [
            'tickets' => auth()->user()->tickets()->filters()->defaultSort('id', 'desc')->paginate(),
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return checkPermission('platform.systems.support') ? 'Список тикетов' : 'Мои тикеты';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        if (!checkPermission('platform.systems.support') && !checkExistsTicket(auth()->user()))
            return [
                ModalToggle::make('Создать тикет')
                    ->icon('plus')
                    ->type(Color::PRIMARY())
                    ->modal('modalCreateTicket')
                    ->modalTitle('Создание тикета')
                    ->method('createTicket'),
            ];

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

                    Input::make('file')
                        ->type('file'),
                ]),
            ])->applyButton('Создать'),
        ];
    }

    public function createTicket(Request $request, TicketService $ticketService): void
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
                'message' => ['required', 'string', 'max:1024'],
                'file' => ['nullable', 'mimes:pdf,png,jpg,gif', 'max:5120']
            ]);

            $validated['status'] = 'New';
            $validated['user_id'] = auth()->id();

            if (checkExistsTicket(auth()->user())) throw new Exception('У вас уже есть активный тикет');

            $ticketService->create($validated);
            Toast::success('Тикет успешно создан.');
        } catch (\Throwable $e) {
            info($e);
            Toast::error($e->getMessage());
        }
    }

    public function closeTicket(Ticket $ticket, TicketService $ticketService)
    {
        try {
            $ticketService->close($ticket->id);
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
