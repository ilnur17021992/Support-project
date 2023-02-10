<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Ticket;
use Illuminate\Console\Command;
use App\Services\Support\TicketService;

class CloseOldTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close old tickets';

    /**
     * The number of minutes to consider a ticket as old.
     *
     * @var int
     */
    const MINUTES_OLD = 1;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $ticketService = new TicketService;
        $tenMinutesAgo = Carbon::now()->subMinutes(self::MINUTES_OLD);

        $tickets = Ticket::where('status', 'processing')
            ->where('updated_at', '<', $tenMinutesAgo)
            ->get();

        foreach ($tickets as $ticket) {
            $ticketService->close($ticket->id);
        }

        $this->info(sprintf('Closed %s tickets', $tickets->count()));

        return Command::SUCCESS;
    }
}
