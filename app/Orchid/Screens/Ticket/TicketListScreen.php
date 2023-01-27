<?php

namespace App\Orchid\Screens\Ticket;

use App\Models\Ticket;
use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;
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
            TicketListLayout::class
        ];
    }

    public function removeTicket(Request $request, $id)
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
