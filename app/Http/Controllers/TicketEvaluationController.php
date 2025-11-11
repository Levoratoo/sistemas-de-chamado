<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketEvaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketEvaluationController extends Controller
{
    /**
     * Mostra o formulário de avaliação
     */
    public function create(Ticket $ticket): View
    {
        $user = auth()->user();
        
        // Verifica se pode avaliar
        if (!$ticket->canBeEvaluatedBy($user)) {
            abort(403, 'Você não pode avaliar este chamado.');
        }

        return view('tickets.evaluate', compact('ticket'));
    }

    /**
     * Salva a avaliação
     */
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = auth()->user();
        
        // Verifica se pode avaliar
        if (!$ticket->canBeEvaluatedBy($user)) {
            abort(403, 'Você não pode avaliar este chamado.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        TicketEvaluation::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
            'evaluated_at' => now(),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Avaliação registrada com sucesso! Obrigado pelo seu feedback.');
    }

    /**
     * Lista chamados que precisam de avaliação
     */
    public function pending(): View
    {
        $user = auth()->user();
        
        $pendingEvaluations = Ticket::where('requester_id', $user->id)
            ->where('status', Ticket::STATUS_FINALIZED)
            ->whereDoesntHave('evaluations', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['category', 'area', 'assignee'])
            ->orderBy('resolved_at', 'desc')
            ->paginate(10);

        return view('tickets.pending-evaluations', compact('pendingEvaluations'));
    }
}