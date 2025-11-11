<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Ticket $ticket)
    {
        // Verificar se o usuário pode comentar neste ticket
        if (!Auth::user()->canManageTickets() && 
            $ticket->requester_id !== Auth::id() && 
            $ticket->assignee_id !== Auth::id()) {
            abort(403);
        }

        $ticket->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'is_internal' => $request->boolean('is_internal', false),
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Comentário adicionado com sucesso!');
    }
}











