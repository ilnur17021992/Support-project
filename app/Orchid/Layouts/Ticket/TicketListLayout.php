<?php

namespace App\Orchid\Layouts\Ticket;

use App\Models\User;
use Orchid\Screen\TD;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Actions\DropDown;
use Illuminate\Support\Facades\Auth;

class TicketListLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'tickets';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->filter()
                ->render(fn (Ticket $ticket) => Link::make($ticket->id)
                    ->route('platform.ticket.messages', $ticket)),

            TD::make('title', 'Заголовок')
                ->render(fn (Ticket $ticket) => Link::make(Str::limit($ticket->title, 20))
                    ->route('platform.ticket.messages', $ticket)),

            TD::make('message', 'Сообщение')
                ->render(fn (Ticket $ticket) => Link::make(Str::limit($ticket->messages()->latest()->first()?->message, 20))
                    ->route('platform.ticket.messages', $ticket)),

            TD::make('user_id', 'Пользователь')
                ->canSee(checkPermission('platform.systems.support'))
                ->sort()
                ->filter(Relation::make()->fromModel(User::class, 'id')->searchColumns('name', 'email')->chunk(10)->displayAppend('full'))
                ->render(fn (Ticket $ticket) => Link::make(Str::limit($ticket->user->name, 25))
                    ->route('platform.systems.users.edit', $ticket->user->id)),

            TD::make('department', 'Отдел')
                ->sort()
                ->filter(Select::make()->options(Ticket::DEPARTMENT)->empty('Не выбрано'))
                ->render(fn (Ticket $ticket) => Link::make(Ticket::DEPARTMENT[$ticket->department])
                    ->route('platform.ticket.messages', $ticket)),

            TD::make('status', 'Статус')
                ->sort()
                ->filter(Select::make()->options(Ticket::STATUS)->empty('Не выбрано'))
                ->render(function (Ticket $ticket) {
                    return match ($ticket->status) {
                        'new' => Link::make(Ticket::STATUS['new'])
                            ->style('color: green !important')
                            ->route('platform.ticket.messages', $ticket),
                        'processing' => Link::make(Ticket::STATUS['processing'])
                            ->style('color: blue !important')
                            ->route('platform.ticket.messages', $ticket),
                        'closed' => Link::make(Ticket::STATUS['closed'])
                            ->style('color: red !important')
                            ->route('platform.ticket.messages', $ticket),
                    };
                }),

            TD::make('messagesCount', 'Кол-во сообщений')
                ->sort()
                ->align(TD::ALIGN_CENTER)
                ->render(fn (Ticket $ticket) => Link::make($ticket->messages()->count())
                    ->route('platform.ticket.messages', $ticket)),

            TD::make('updated_at', 'Дата обновления')
                ->sort()
                ->render(fn (Ticket $ticket) => $ticket->updated_at),


            TD::make('created_at', 'Дата создания')
                ->sort()
                ->render(fn (Ticket $ticket) => $ticket->created_at)
                ->defaultHidden(),

            TD::make('Действия')
                ->align(TD::ALIGN_CENTER)
                ->render(function (Ticket $ticket) {
                    return DropDown::make()
                        ->icon('options')
                        ->list([
                            Link::make('Просмотр')
                                ->icon('eye')
                                ->route('platform.ticket.messages', $ticket),

                            Button::make('Закрыть')
                                ->icon('close')
                                ->confirm('Закрытие тикета ID: ' . $ticket->id)
                                ->canSee($ticket->status != 'closed')
                                ->method('closeTicket', [
                                    'id' => $ticket->id,
                                ]),

                            Button::make('Удалить')
                                ->icon('trash')
                                ->confirm('Удаление тикета ID: ' . $ticket->id)
                                ->canSee(checkPermission('platform.systems.support'))
                                ->method('removeTicket', [
                                    'id' => $ticket->id,
                                ]),
                        ]);
                }),
        ];
    }
}
