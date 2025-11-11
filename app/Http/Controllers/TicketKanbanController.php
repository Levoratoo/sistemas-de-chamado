<?php

namespace App\Http\Controllers;

use App\Helpers\Sanitizer;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\TicketEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketKanbanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = Ticket::query()
            ->with(['requester', 'assignee', 'area'])
            ->whereIn('status', [
                Ticket::STATUS_OPEN,
                Ticket::STATUS_IN_PROGRESS,
                Ticket::STATUS_WAITING_USER,
                Ticket::STATUS_FINALIZED,
            ])
            ->where(function ($inner) use ($user) {
                $inner->whereNull('assignee_id')
                    ->orWhere('assignee_id', $user->id)
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('status', Ticket::STATUS_FINALIZED)
                            ->where('resolution_by', $user->id);
                    })
                    ->orWhere(function ($sub) use ($user) {
                        $sub->where('status', Ticket::STATUS_WAITING_USER)
                            ->where('assignee_id', $user->id);
                    });
            })
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($inner) use ($search) {
                $inner->where('code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('assigned_to_me')) {
            $query->where('assignee_id', $user->id);
        }

        if ($request->boolean('my_tickets')) {
            $query->where('requester_id', $user->id);
        }

        if (! $user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();

                $query->where(function ($inner) use ($user, $areaIds) {
                    $inner->when(! empty($areaIds), function ($sub) use ($areaIds) {
                        $sub->whereIn('area_id', $areaIds);
                    })
                        ->orWhere('assignee_id', $user->id)
                        ->orWhere('requester_id', $user->id);
                });
            } elseif ($user->isAtendente()) {
                $query->where(function ($inner) use ($user) {
                    $inner->where('assignee_id', $user->id)
                        ->orWhere('requester_id', $user->id);
                });
            } else {
                $query->where('requester_id', $user->id);
            }
        }

        $tickets = $query->get();

        $columnMeta = [
            'queue' => [
                'label' => 'Fila',
                'description' => 'Chamados sem atribuição.',
                'bg' => 'bg-indigo-50/70',
                'accent' => 'bg-gradient-to-r from-indigo-400/80 to-sky-400/80',
            ],
            'in_progress' => [
                'label' => 'Em andamento',
                'description' => 'Chamados atribuídos e em trabalho.',
                'bg' => 'bg-amber-50/70',
                'accent' => 'bg-gradient-to-r from-amber-400/80 to-orange-400/80',
            ],
            'waiting_user' => [
                'label' => 'Aguardando Solicitante',
                'description' => 'Chamados aguardando resposta do solicitante.',
                'bg' => 'bg-yellow-50/70',
                'accent' => 'bg-gradient-to-r from-yellow-400/80 to-orange-400/80',
            ],
            'finalized' => [
                'label' => 'Finalizada',
                'description' => 'Chamados finalizados por você.',
                'bg' => 'bg-emerald-50/70',
                'accent' => 'bg-gradient-to-r from-emerald-400/80 to-teal-400/80',
            ],
        ];

        $ticketsData = $tickets
            ->filter(fn (Ticket $ticket) => $this->determineColumn($ticket, $user) !== null)
            ->map(function (Ticket $ticket) use ($user) {
                $column = $this->determineColumn($ticket, $user);

            return [
                'id' => $ticket->id,
                'code' => $ticket->code,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'status_label' => $ticket->status_label,
                'priority' => $ticket->priority,
                'priority_label' => $ticket->priority_label,
                'priority_badge' => $ticket->priority_badge_class,
                'requester' => $ticket->requester?->name,
                'assignee' => $ticket->assignee?->name,
                'assignee_id' => $ticket->assignee_id,
                'area' => $ticket->area?->name,
                'created_at' => $ticket->created_at?->format('d/m/Y H:i'),
                'updated_human' => $ticket->updated_at?->diffForHumans(),
                'updated_ts' => $ticket->updated_at?->timestamp ?? 0,
                'sla_status' => $ticket->sla_status,
                'card_class' => $this->resolveCardClass($ticket),
                'column' => $column,
                'description_html' => nl2br(e($ticket->description ?? '')),
                'show_url' => route('tickets.show', $ticket),
            ];
        })->values();

        $filters = [
            'search' => $request->input('search', ''),
            'assigned_to_me' => $request->boolean('assigned_to_me'),
            'my_tickets' => $request->boolean('my_tickets'),
        ];

        return view('tickets.kanban', [
            'columnsMeta' => $columnMeta,
            'tickets' => $ticketsData,
            'filters' => $filters,
        ]);
    }

    public function updateStatus(Request $request, Ticket $ticket): JsonResponse
    {
        $this->authorize('work', $ticket);

        $column = $request->input('column');

        $returnReasonRules = ['nullable', 'string', 'min:5', 'max:2000'];
        $summaryRules = ['nullable', 'string', 'min:10', 'max:20000'];

        if ($column === 'queue') {
            $returnReasonRules[] = 'required';
        }

        if ($column === 'finalized') {
            $summaryRules[] = 'required';
        }

        $validated = $request->validate([
            'column' => ['required', Rule::in(['queue', 'in_progress', 'waiting_user', 'finalized'])],
            'return_reason' => $returnReasonRules,
            'resolution_summary' => $summaryRules,
        ], [
            'return_reason.required' => 'Informe o motivo para devolver o chamado para a fila.',
            'return_reason.min' => 'Descreva um motivo com pelo menos :min caracteres.',
            'resolution_summary.required' => 'Informe um resumo da resolução para finalizar o chamado.',
            'resolution_summary.min' => 'O resumo precisa ter ao menos :min caracteres.',
            'resolution_summary.max' => 'O resumo pode ter no máximo :max caracteres.',
        ]);

        $user = $request->user();
        $currentColumn = $this->determineColumn($ticket, $user);
        $targetColumn = $validated['column'];

        if ($currentColumn === $targetColumn) {
            return response()->json([
                'message' => 'O chamado já está na coluna selecionada.',
                'ticket' => $this->ticketPayload($ticket->fresh(['assignee']), $user),
            ]);
        }

        if ($targetColumn === 'queue' && $ticket->assignee_id !== $user->id && $ticket->assignee_id !== null) {
            return response()->json([
                'message' => 'Apenas o responsável atual pode devolver o chamado para a fila.',
            ], 422);
        }

        $ticketId = $ticket->id;
        $now = now();
        
        DB::transaction(function () use ($ticketId, $user, $targetColumn, $validated, $now) {
            // Recarregar ticket fresco do banco
            $ticket = Ticket::findOrFail($ticketId);
            
            if ($targetColumn === 'finalized') {
                $fromStatus = $ticket->status;

                // Atribuir ao usuário se não estiver atribuído
                if ($ticket->assignee_id === null) {
                    $ticket->assignee_id = $user->id;
                    $ticket->assigned_at = $now;
                }

                // Marcar início do atendimento se não tiver
                if ($ticket->started_at === null) {
                    $ticket->started_at = $now;
                }

                // DEFINIR TODOS OS CAMPOS DE FINALIZAÇÃO
                $ticket->status = Ticket::STATUS_FINALIZED;
                $ticket->resolution_summary = Sanitizer::sanitizeWithFormatting($validated['resolution_summary'] ?? '');
                $ticket->resolution_by = $user->id;
                $ticket->resolved_at = $now;
                $ticket->closed_at = $now;
                $ticket->last_status_change_at = $now;
                
                // SALVAR E FORÇAR PERSISTÊNCIA
                $saved = $ticket->save();
                
                if (!$saved) {
                    throw new \Exception('Erro ao salvar ticket finalizado.');
                }
                
                // Recarregar imediatamente após salvar
                $ticket->refresh();
                
                // Verificar se realmente foi salvo
                if ($ticket->status !== Ticket::STATUS_FINALIZED) {
                    // FORÇAR ATUALIZAÇÃO DIRETA NO BANCO
                    DB::table('tickets')
                        ->where('id', $ticketId)
                        ->update([
                            'status' => Ticket::STATUS_FINALIZED,
                            'resolution_by' => $user->id,
                            'resolved_at' => $now,
                            'closed_at' => $now,
                            'resolution_summary' => Sanitizer::sanitizeWithFormatting($validated['resolution_summary'] ?? ''),
                            'last_status_change_at' => $now,
                            'updated_at' => $now,
                        ]);
                    
                    $ticket->refresh();
                }
                
                // Registrar eventos APÓS salvar e verificar
                if ($fromStatus !== Ticket::STATUS_FINALIZED) {
                    TicketEventService::log($ticket, $user, 'status_changed', $fromStatus, Ticket::STATUS_FINALIZED);
                }
                TicketEventService::log($ticket, $user, 'finalized', $fromStatus, Ticket::STATUS_FINALIZED);
                
                if ($ticket->assignee_id === null || $ticket->assignee_id !== $user->id) {
                    TicketEventService::log($ticket, $user, 'assigned');
                }
            } elseif ($targetColumn === 'waiting_user') {
                $fromStatus = $ticket->status;
                $previousAssignee = $ticket->assignee_id;

                if ($ticket->assignee_id !== $user->id) {
                    $ticket->assignee_id = $user->id;
                    $ticket->assigned_at = $now;
                    TicketEventService::log(
                        $ticket,
                        $user,
                        $previousAssignee ? 'delegated' : 'assigned',
                        $fromStatus,
                        $fromStatus,
                        [
                            'from_assignee_id' => $previousAssignee,
                            'to_assignee_id' => $user->id,
                        ]
                    );
                }

                if ($ticket->started_at === null) {
                    $ticket->started_at = $now;
                }

                if ($ticket->status !== Ticket::STATUS_WAITING_USER) {
                    $ticket->status = Ticket::STATUS_WAITING_USER;
                    $ticket->last_status_change_at = $now;
                }

                $ticket->touch();
                $ticket->save();

                if ($fromStatus !== $ticket->status) {
                    TicketEventService::log($ticket, $user, 'status_changed', $fromStatus, $ticket->status);
                }
            } elseif ($targetColumn === 'in_progress') {
                $fromStatus = $ticket->status;
                $previousAssignee = $ticket->assignee_id;

                if ($ticket->assignee_id !== $user->id) {
                    $ticket->assignee_id = $user->id;
                    $ticket->assigned_at = $now;
                    TicketEventService::log(
                        $ticket,
                        $user,
                        $previousAssignee ? 'delegated' : 'assigned',
                        $fromStatus,
                        $fromStatus,
                        [
                            'from_assignee_id' => $previousAssignee,
                            'to_assignee_id' => $user->id,
                        ]
                    );
                }

                if ($ticket->started_at === null) {
                    $ticket->started_at = $now;
                }

                if ($ticket->status !== Ticket::STATUS_IN_PROGRESS) {
                    $ticket->status = Ticket::STATUS_IN_PROGRESS;
                    $ticket->last_status_change_at = $now;
                }

                $ticket->touch();
                $ticket->save();

                if ($fromStatus !== $ticket->status) {
                    TicketEventService::log($ticket, $user, 'status_changed', $fromStatus, $ticket->status);
                }
            } else { // queue
                $fromStatus = $ticket->status;
                $previousAssignee = $ticket->assignee_id;

                $ticket->assignee_id = null;
                $ticket->assigned_at = null;

                if (in_array($ticket->status, [Ticket::STATUS_IN_PROGRESS, Ticket::STATUS_WAITING_USER], true)) {
                    $ticket->status = Ticket::STATUS_OPEN;
                    $ticket->last_status_change_at = $now;
                }

                $ticket->touch();
                $ticket->save();

                if (! empty($validated['return_reason'])) {
                    TicketComment::create([
                        'ticket_id' => $ticket->id,
                        'user_id' => $user->id,
                        'body' => 'Chamado devolvido para fila: ' . Sanitizer::sanitize($validated['return_reason']),
                        'is_internal' => false,
                    ]);
                }

                TicketEventService::log(
                    $ticket,
                    $user,
                    'returned_to_queue',
                    $fromStatus,
                    $ticket->status,
                    [
                        'previous_assignee_id' => $previousAssignee,
                        'reason' => $validated['return_reason'] ?? null,
                    ]
                );
            }
        });

        // NOTIFICAR FORA DA TRANSAÇÃO para evitar que erros de email causem rollback
        $shouldNotify = ($targetColumn === 'finalized');
        
        // RECARREGAR TICKET DIRETAMENTE DO BANCO SEM CACHE (após transação)
        $ticket = Ticket::withoutGlobalScopes()
            ->with('assignee')
            ->findOrFail($ticketId);
        
        // VERIFICAÇÃO FINAL CRÍTICA: Se foi finalizado, garantir que está correto
        if ($targetColumn === 'finalized') {
            // Verificar se o status está realmente FINALIZED
            if ($ticket->status !== Ticket::STATUS_FINALIZED) {
                // CORREÇÃO DE EMERGÊNCIA: Forçar finalização diretamente
                DB::table('tickets')
                    ->where('id', $ticketId)
                    ->update([
                        'status' => Ticket::STATUS_FINALIZED,
                        'resolution_by' => $user->id,
                        'resolved_at' => $now,
                        'closed_at' => $now,
                        'resolution_summary' => Sanitizer::sanitizeWithFormatting($validated['resolution_summary'] ?? 'Chamado finalizado pelo Kanban'),
                        'last_status_change_at' => $now,
                        'updated_at' => $now,
                    ]);
                
                // Recarregar após correção
                $ticket = Ticket::withoutGlobalScopes()
                    ->with('assignee')
                    ->findOrFail($ticketId);
            }
            
            // Garantir que resolution_by está correto
            if ($ticket->resolution_by !== $user->id) {
                DB::table('tickets')
                    ->where('id', $ticketId)
                    ->update([
                        'resolution_by' => $user->id,
                        'updated_at' => $now,
                    ]);
                
                $ticket = Ticket::withoutGlobalScopes()
                    ->with('assignee')
                    ->findOrFail($ticketId);
            }
            
            // Notificar APÓS garantir que está salvo
            try {
                app(NotificationService::class)->notifyTicketFinalized($ticket);
            } catch (\Exception $e) {
                // Logar erro mas não quebrar o fluxo
                \Log::warning('Erro ao notificar finalização do ticket', [
                    'ticket_id' => $ticketId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = match ($targetColumn) {
            'queue' => 'Chamado devolvido para a fila.',
            'in_progress' => 'Chamado movido para Em andamento.',
            'waiting_user' => 'Chamado movido para Aguardando Solicitante.',
            'finalized' => 'Chamado finalizado com sucesso.',
        };

        return response()->json([
            'message' => $message,
            'ticket' => $this->ticketPayload($ticket, $user),
        ]);
    }

    protected function ticketPayload(Ticket $ticket, User $user): array
    {
        // Recarregar ticket para garantir dados atualizados
        $ticket->refresh();
        
        // Garantir que determineColumn usa dados atualizados
        $column = $this->determineColumn($ticket, $user);
        
        return [
            'id' => $ticket->id,
            'status' => $ticket->status,
            'status_label' => $ticket->status_label,
            'assignee' => $ticket->assignee?->name,
            'assignee_id' => $ticket->assignee_id,
            'updated_human' => $ticket->updated_at?->diffForHumans(),
            'updated_ts' => $ticket->updated_at?->timestamp ?? 0,
            'sla_status' => $ticket->sla_status,
            'card_class' => $this->resolveCardClass($ticket),
            'column' => $column,
        ];
    }

    protected function determineColumn(Ticket $ticket, User $user): ?string
    {
        // PRIMEIRO: Verificar se está finalizado (prioridade máxima)
        if ($ticket->status === Ticket::STATUS_FINALIZED) {
            // Se foi finalizado pelo usuário atual, mostrar na coluna Finalizada
            if ($ticket->resolution_by === $user->id) {
                return 'finalized';
            }
            // Se foi finalizado por outro usuário, não mostrar (return null)
            return null;
        }

        // Aguardando Solicitante: status WAITING_USER atribuído ao usuário
        if ($ticket->status === Ticket::STATUS_WAITING_USER && $ticket->assignee_id === $user->id) {
            return 'waiting_user';
        }

        // Em andamento: atribuído ao usuário e status IN_PROGRESS
        if ($ticket->assignee_id === $user->id && $ticket->status === Ticket::STATUS_IN_PROGRESS) {
            return 'in_progress';
        }

        // Fila: sem atribuição e não finalizado
        if ($ticket->assignee_id === null && $ticket->status !== Ticket::STATUS_FINALIZED) {
            return 'queue';
        }

        return null;
    }

    protected function resolveCardClass(Ticket $ticket): string
    {
        // Cor verde apenas quando o ticket está finalizado
        if ($ticket->status === Ticket::STATUS_FINALIZED) {
            return 'card-green';
        }
        
        return '';
    }

}
