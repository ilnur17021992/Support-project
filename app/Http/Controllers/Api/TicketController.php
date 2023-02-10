<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\Support\Message;
use App\Services\Support\Ticket as SupportTicket;
use App\Services\Support\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $tickets = auth()->user()->tickets;

        return TicketResource::collection($tickets);
    }

    public function store(Request $request, TicketService $ticketService): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
        ]);

        $ticket = $ticketService->create(new SupportTicket(
            auth()->id(),
            $validated['title'],
            $validated['department'],
            'new',
        ));

        return response()->json([
            'message' => 'Ticket created successfully.',
            'data' => new TicketResource($ticket),
        ], 201);
    }

    public function storeMessage(Request $request, int $id, TicketService $ticketService): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1024'],
            'file' => ['nullable', 'mimes:pdf,png,jpg,gif', 'max:5120']
        ]);

        $ticket = Ticket::find($id);

        if (!$ticket) return response()->json([
            'message' => 'Ticket not found.'
        ], 404);

        if ($ticket->status == 'closed') return response()->json([
            'message' => 'Ticket already closed.'
        ], 422);

        $message = $ticketService->send($ticket, new Message(
            auth()->id(),
            $validated['message'],
            isset($validated['file']) ? Storage::putFile('files', $validated['file'], 'public') : null
        ));

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => new MessageResource($message),
        ], 201);
    }

    public function messages(int $id): AnonymousResourceCollection
    {
        $ticket = Ticket::find($id);

        if (!$ticket) return response()->json([
            'message' => 'Ticket not found.'
        ], 404);

        return MessageResource::collection($ticket->messages);
    }
}
