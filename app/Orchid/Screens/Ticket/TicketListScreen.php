<?php

namespace App\Orchid\Screens\Ticket;

use App\Http\Requests\Support\MessageRequest;
use App\Models\Ticket;
use App\Orchid\Layouts\Ticket\TicketListLayout;
use App\Services\Support\Message;
use App\Services\Support\Ticket as SupportTicket;
use App\Services\Support\TicketService;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

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

                    TextArea::make('text')
                        ->title('Сообщение')
                        ->rows(5)
                        ->required(),

                    Input::make('file')
                        ->type('file'),
                ]),
            ])->applyButton('Создать'),
        ];
    }

    public function createTicket(MessageRequest $request, TicketService $ticketService): void
    {
        try {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
                'text' => ['required', 'string', 'max:1024'],
                'file' => ['nullable', 'mimes:png,jpg,gif', 'max:5120']
            ]);

            if (checkExistsTicket(auth()->user())) throw new Exception('У вас уже есть активный тикет');

            $ticket = $ticketService->create(new SupportTicket(
                auth()->id(),
                $validated['title'],
                $validated['department'],
                'new',
            ));

            $ticketService->send($ticket, new Message(
                auth()->id(),
                $validated['text'],
                isset($validated['file']) ? Storage::putFile('files', $validated['file'], 'public') : null
            ));

            Toast::success('Тикет успешно создан.');
        } catch (\Throwable $e) {
            info($e);
            Alert::error($e->getMessage());
        }
    }

    public function closeTicket(Ticket $ticket, TicketService $ticketService)
    {
        try {
            $ticketService->close($ticket->id);
            Toast::success('Тикет успешно закрыт.');
        } catch (\Throwable $e) {
            info($e);
            Alert::error($e->getMessage());
        }
    }

    public function removeTicket($id)
    {
        try {
            Ticket::find($id)->delete();
            Toast::success('Тикет успешно удален.');
        } catch (\Throwable $e) {
            info($e);
            Alert::error($e->getMessage());
        }
    }
}
