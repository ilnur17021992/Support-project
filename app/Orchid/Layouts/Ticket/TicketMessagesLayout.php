<?php

namespace App\Orchid\Layouts\Ticket;

use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TicketMessagesLayout extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'messages';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('user_id', 'Автор')
                ->render(function (Message $message) {
                    $color = $message->user->hasAccess('platform.systems.support') ? 'red' : 'blue';
                    return Link::make($message->user->name)->style("color: $color !important")
                        ->route('platform.systems.users.edit', $message->user->id);
                }),

            TD::make('text', 'Сообщение')
                ->align(TD::ALIGN_LEFT),

            TD::make('file', 'Файл')
                ->render(fn (Message $message) => isset($message->file) ? Link::make(mb_substr($message->file, -10))->href(Storage::url($message->file))->target('_blank') : null),

            TD::make('created_at', 'Дата')
                ->render(fn (Message $message) => $message->created_at),
        ];
    }
}
