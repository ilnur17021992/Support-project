<?php

namespace App\Http\Controllers\API;

use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Services\TicketService;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\Http\Resources\MessageResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $tickets = auth()->user()->tickets;

        return TicketResource::collection($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department' => ['required', Rule::in(array_keys(Ticket::DEPARTMENT))],
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'new';

        $ticket = Ticket::create($validated);

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

        $validated['user_id'] = auth()->id();
        $validated['file'] = isset($validated['file']) ? Storage::putFile('files', $validated['file'], 'public') : null;
        $ticketService->send($ticket, $validated);

        return response()->json([
            'message' => 'Message sent successfully.',
            'data' => $validated,
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
