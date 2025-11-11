<?php

namespace App\Http\Controllers;

use App\Helpers\Sanitizer;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\FinalizeRequest;
use App\Http\Requests\TransitionRequest;
use App\Http\Requests\Tickets\DelegateRequest;
use App\Http\Requests\Tickets\ReturnToQueueRequest;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\NotificationService;
use App\Services\TicketEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketWorkController extends Controller
{
    public function assign(Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        if ($ticket->requester_id === Auth::id()) {
            return back()->with('error', 'Voce nao pode assumir um chamado que abriu.');
        }

        DB::transaction(function () use ($ticket) {
            $user = Auth::user();
            $now = now();
            $statusBefore = $ticket->status;
            $statusChanged = false;

            if ($ticket->assignee_id !== $user->id) {
                $ticket->assignee_id = $user->id;
                $ticket->assigned_at = $now;
                TicketEventService::log($ticket, $user, 'assigned');
            }

            if ($ticket->started_at === null) {
                $ticket->started_at = $now;
            }

            if ($ticket->status === Ticket::STATUS_OPEN) {
                $ticket->status = Ticket::STATUS_IN_PROGRESS;
                $ticket->last_status_change_at = $now;
                $statusChanged = true;
            }

            $ticket->save();

            if ($statusChanged) {
                TicketEventService::log($ticket, $user, 'status_changed', $statusBefore, $ticket->status);
            }
        });

        return redirect()->back()->with('success', 'Chamado atribuido para voce.');
    }

    public function comment(CommentRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();

            $comment = TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => Sanitizer::sanitizeWithFormatting($request->input('message') ?? ''),
                'is_internal' => false,
            ]);

            if ($ticket->first_response_at === null) {
                $ticket->first_response_at = now();
                $ticket->save();
            } else {
                $ticket->touch();
            }

            TicketEventService::log(
                $ticket,
                $user,
                'commented',
                null,
                null,
                [
                    'comment_id' => $comment->id,
                ]
            );
        });

        return redirect()->back()->with('success', 'Comentario registrado com sucesso.');
    }

    public function markWaiting(TransitionRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $now = now();
            $fromStatus = $ticket->status;

            $ticket->status = Ticket::STATUS_WAITING_USER;
            $ticket->last_status_change_at = $now;
            $ticket->save();

            $reason = $request->input('reason');

            if ($reason) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'body' => 'Aguardando retorno do solicitante: ' . Sanitizer::sanitize($reason ?? ''),
                    'is_internal' => false,
                ]);
            }

            TicketEventService::log(
                $ticket,
                $user,
                'status_changed',
                $fromStatus,
                $ticket->status,
                $reason ? ['reason' => $reason] : null
            );
        });

        return redirect()->back()->with('success', 'Chamado marcado como aguardando retorno do solicitante.');
    }

    public function finalize(FinalizeRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $now = now();
            $fromStatus = $ticket->status;

            if ($ticket->assignee_id === null) {
                $ticket->assignee_id = $user->id;
                $ticket->assigned_at = $now;
                TicketEventService::log($ticket, $user, 'assigned');
            }

            if ($ticket->started_at === null) {
                $ticket->started_at = $now;
            }

            $ticket->status = Ticket::STATUS_FINALIZED;
            $ticket->resolution_summary = Sanitizer::sanitizeWithFormatting($request->input('resolution_summary') ?? '');
            $ticket->resolution_by = $user->id;
            $ticket->resolved_at = $now;
            $ticket->last_status_change_at = $now;
            $ticket->save();

            TicketEventService::log(
                $ticket,
                $user,
                'status_changed',
                $fromStatus,
                $ticket->status
            );

            TicketEventService::log(
                $ticket,
                $user,
                'finalized',
                $fromStatus,
                $ticket->status
            );

            // Enviar notificação de ticket finalizado
            app(NotificationService::class)->notifyTicketFinalized($ticket);
        });

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('success', 'Chamado finalizado com sucesso.');
    }

    public function delegate(DelegateRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $newAssigneeId = (int) $request->input('assignee_id');
            $oldAssigneeId = $ticket->assignee_id;
            $statusBefore = $ticket->status;
            
            $now = now();

            // Atribui o ticket para o novo usuário
            $ticket->assignee_id = $newAssigneeId;
            $ticket->assigned_at = $now;
            
            // Se não tinha started_at, marca agora
            if ($ticket->started_at === null) {
                $ticket->started_at = $now;
            }

            // Muda o status para in_progress se estava open ou waiting_user
            if (in_array($ticket->status, [Ticket::STATUS_OPEN, Ticket::STATUS_WAITING_USER])) {
                $ticket->status = Ticket::STATUS_IN_PROGRESS;
                $ticket->last_status_change_at = $now;
            }

            $ticket->save();

            // Log do evento de delegação
            TicketEventService::log(
                $ticket,
                $user,
                'delegated',
                $statusBefore,
                $ticket->status,
                [
                    'from_assignee_id' => $oldAssigneeId,
                    'to_assignee_id' => $newAssigneeId,
                ]
            );

            // Enviar notificação de ticket atribuído
            app(NotificationService::class)->notifyTicketAssigned($ticket, $user);
        });

        return redirect()->back()->with('success', 'Chamado delegado com sucesso.');
    }

    public function returnToQueue(ReturnToQueueRequest $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $oldAssigneeId = $ticket->assignee_id;
            $statusBefore = $ticket->status;
            $now = now();
            
            $reason = $request->input('reason');

            // Remove a atribuição
            $ticket->assignee_id = null;
            $ticket->assigned_at = null;
            
            // Volta para open se estava in_progress ou waiting_user
            if (in_array($ticket->status, [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_WAITING_USER])) {
                $ticket->status = Ticket::STATUS_OPEN;
                $ticket->last_status_change_at = $now;
            }

            $ticket->save();

            // Opcionalmente cria um comentário se houve motivo
            if ($reason) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'body' => 'Chamado devolvido para fila: ' . Sanitizer::sanitize($reason ?? ''),
                    'is_internal' => false,
                ]);
            }

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'returned_to_queue',
                $statusBefore,
                $ticket->status,
                [
                    'previous_assignee_id' => $oldAssigneeId,
                    'reason' => $reason,
                ]
            );
        });

        return redirect()->back()->with('success', 'Chamado devolvido para a fila.');
    }

    public function markAnalysis(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $statusBefore = $ticket->status;
            $now = now();
            $reason = $request->input('reason');

            // Muda para status de análise (usando waiting_user temporariamente)
            $ticket->status = Ticket::STATUS_WAITING_USER;
            $ticket->last_status_change_at = $now;
            $ticket->save();

            // Cria comentário se houve motivo
            if ($reason) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'body' => 'Chamado marcado como em análise: ' . Sanitizer::sanitize($reason ?? ''),
                    'is_internal' => false,
                ]);
            }

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'marked_analysis',
                $statusBefore,
                $ticket->status,
                ['reason' => $reason]
            );
        });

        return redirect()->back()->with('success', 'Chamado marcado como em análise.');
    }

    public function markThirdParty(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $statusBefore = $ticket->status;
            $now = now();
            $reason = $request->input('reason');

            // Muda para status de aguardando terceiros (usando waiting_user temporariamente)
            $ticket->status = Ticket::STATUS_WAITING_USER;
            $ticket->last_status_change_at = $now;
            $ticket->save();

            // Cria comentário se houve motivo
            if ($reason) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'body' => 'Aguardando terceiros: ' . Sanitizer::sanitize($reason ?? ''),
                    'is_internal' => false,
                ]);
            }

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'marked_third_party',
                $statusBefore,
                $ticket->status,
                ['reason' => $reason]
            );
        });

        return redirect()->back()->with('success', 'Chamado marcado como aguardando terceiros.');
    }

    public function resume(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $statusBefore = $ticket->status;
            $now = now();
            $comment = $request->input('comment');

            // Retoma o chamado para in_progress
            $ticket->status = Ticket::STATUS_IN_PROGRESS;
            $ticket->last_status_change_at = $now;
            $ticket->save();

            // Cria comentário se houve motivo
            if ($comment) {
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'body' => 'Retorno do solicitante: ' . Sanitizer::sanitize($comment ?? ''),
                    'is_internal' => false,
                ]);
            }

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'resumed',
                $statusBefore,
                $ticket->status,
                ['comment' => $comment]
            );
        });

        return redirect()->back()->with('success', 'Chamado retomado.');
    }

    public function cancel(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('work', $ticket);

        DB::transaction(function () use ($request, $ticket) {
            $user = Auth::user();
            $statusBefore = $ticket->status;
            $now = now();
            $reason = $request->input('reason');

            // Cancela o chamado
            $ticket->status = 'cancelled';
            $ticket->last_status_change_at = $now;
            $ticket->save();

            // Cria comentário com motivo obrigatório
            TicketComment::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'body' => 'Chamado cancelado: ' . Sanitizer::sanitize($reason ?? ''),
                'is_internal' => false,
            ]);

            // Log do evento
            TicketEventService::log(
                $ticket,
                $user,
                'cancelled',
                $statusBefore,
                $ticket->status,
                ['reason' => $reason]
            );
        });

        return redirect()->back()->with('success', 'Chamado cancelado.');
    }
}
