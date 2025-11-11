<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Ticket;
use App\Services\SlaCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendUrgentAlerts extends Command
{
    protected $signature = 'notifications:urgent-alerts {--test : Enviar para usuário específico}';
    protected $description = 'Enviar alertas urgentes para chamados críticos';

    public function handle(SlaCalculationService $slaService): int
    {
        $this->info('🚨 Verificando alertas urgentes...');
        
        $isTest = $this->option('test');
        
        // Buscar chamados críticos
        $urgentTickets = $this->getUrgentTickets($slaService);
        
        if ($urgentTickets->isEmpty()) {
            $this->info('✅ Nenhum chamado urgente encontrado.');
            return 0;
        }

        $this->info("🚨 Encontrados {$urgentTickets->count()} chamados urgentes");

        if ($isTest) {
            // Modo teste
            $user = User::first();
            if (!$user) {
                $this->error('❌ Nenhum usuário encontrado para teste.');
                return 1;
            }
            
            $this->sendUrgentAlertToUser($user, $urgentTickets);
            $this->info("✅ Alerta urgente enviado para {$user->name}");
            return 0;
        }

        // Modo produção - enviar para usuários relevantes
        $sentCount = 0;
        $users = $this->getRelevantUsers($urgentTickets);

        foreach ($users as $user) {
            try {
                $userTickets = $urgentTickets->filter(function($ticket) use ($user) {
                    return $ticket->assignee_id === $user->id || 
                           $ticket->requester_id === $user->id ||
                           $this->isUserInTicketArea($user, $ticket);
                });

                if ($userTickets->isNotEmpty()) {
                    $this->sendUrgentAlertToUser($user, $userTickets);
                    $sentCount++;
                    $this->line("✅ Alerta enviado para {$user->name}");
                }
            } catch (\Exception $e) {
                $this->error("❌ Erro ao enviar para {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("📊 Alertas urgentes enviados para {$sentCount} usuários");
        return 0;
    }

    private function getUrgentTickets(SlaCalculationService $slaService)
    {
        return Ticket::with(['category', 'area', 'requester', 'assignee'])
            ->where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where(function($query) {
                // Chamados vencidos
                $query->where('due_at', '<', now())
                      // Chamados próximos do vencimento (próximas 24 horas)
                      ->orWhere('due_at', '<', now()->addDay())
                      // Chamados críticos próximos do vencimento (próximas 4 horas)
                      ->orWhere(function($q) {
                          $q->where('priority', 'critical')
                            ->where('due_at', '<', now()->addHours(4));
                      })
                      // Chamados sem primeira resposta há mais de 12 horas
                      ->orWhere(function($q) {
                          $q->where('first_response_at', null)
                            ->where('respond_by', '<', now());
                      });
            })
            ->get()
            ->filter(function($ticket) use ($slaService) {
                $compliance = $slaService->checkSlaCompliance($ticket);
                return $compliance['is_overdue'] || $compliance['is_near_due'];
            });
    }

    private function getRelevantUsers($urgentTickets)
    {
        $userIds = $urgentTickets->pluck('assignee_id')
            ->merge($urgentTickets->pluck('requester_id'))
            ->filter()
            ->unique();

        return User::whereIn('id', $userIds)
            ->orWhereHas('role', function($query) {
                $query->whereIn('name', ['admin', 'gestor']);
            })
            ->get();
    }

    private function isUserInTicketArea(User $user, Ticket $ticket): bool
    {
        if (!$ticket->area) return false;
        
        $userAreaIds = $user->groupsAreasIds();
        return in_array($ticket->area_id, $userAreaIds);
    }

    private function sendUrgentAlertToUser(User $user, $urgentTickets): void
    {
        Mail::send('emails.urgent-alert', [
            'user' => $user,
            'urgentTickets' => $urgentTickets,
            'date' => now()->format('d/m/Y H:i'),
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                   ->subject('🚨 ALERTA URGENTE - Chamados Críticos - ' . now()->format('d/m/Y H:i'));
        });
    }
}