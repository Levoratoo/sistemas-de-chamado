<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Ticket;
use App\Services\SlaCalculationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    protected $signature = 'notifications:daily-digest {--test : Enviar para usuário específico}';
    protected $description = 'Enviar resumo diário de chamados para usuários';

    public function handle(SlaCalculationService $slaService): int
    {
        $this->info('📧 Enviando resumo diário de chamados...');
        
        $isTest = $this->option('test');
        
        if ($isTest) {
            // Modo teste - enviar apenas para um usuário
            $user = User::first();
            if (!$user) {
                $this->error('❌ Nenhum usuário encontrado para teste.');
                return 1;
            }
            
            $this->sendDigestToUser($user, $slaService);
            $this->info("✅ Resumo enviado para {$user->name} ({$user->email})");
            return 0;
        }

        // Modo produção - enviar para todos os usuários ativos
        $users = User::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'gestor', 'atendente']);
        })->get();

        $sentCount = 0;
        foreach ($users as $user) {
            try {
                $this->sendDigestToUser($user, $slaService);
                $sentCount++;
                $this->line("✅ Enviado para {$user->name}");
            } catch (\Exception $e) {
                $this->error("❌ Erro ao enviar para {$user->name}: " . $e->getMessage());
            }
        }

        $this->info("📊 Resumo enviado para {$sentCount} usuários");
        return 0;
    }

    private function sendDigestToUser(User $user, SlaCalculationService $slaService): void
    {
        $today = now()->startOfDay();
        $tomorrow = now()->addDay()->startOfDay();

        // Buscar chamados relevantes para o usuário (últimos 7 dias + próximos 7 dias)
        $sevenDaysAgo = now()->subDays(7)->startOfDay();
        $sevenDaysFromNow = now()->addDays(7)->endOfDay();
        $userTickets = $this->getUserRelevantTickets($user, $sevenDaysAgo, $sevenDaysFromNow);
        
        if ($userTickets->isEmpty()) {
            // Não enviar email se não há chamados relevantes
            return;
        }

        // Organizar chamados por categoria
        $ticketsByCategory = $this->organizeTicketsByCategory($userTickets, $slaService);

        // Enviar email
        Mail::send('emails.daily-digest', [
            'user' => $user,
            'ticketsByCategory' => $ticketsByCategory,
            'date' => now()->format('d/m/Y'),
            'totalTickets' => $userTickets->count(),
        ], function ($message) use ($user) {
            $message->to($user->email, $user->name)
                   ->subject('📋 Resumo Diário - Sistema de Chamados - ' . now()->format('d/m/Y'));
        });
    }

    private function getUserRelevantTickets(User $user, $startDate, $endDate)
    {
        $query = Ticket::with(['category', 'area', 'requester', 'assignee'])
            ->where(function($q) use ($user) {
                // Chamados que o usuário criou
                $q->where('requester_id', $user->id)
                  // Chamados atribuídos ao usuário
                  ->orWhere('assignee_id', $user->id)
                  // Chamados da área do usuário (se for gestor/atendente)
                  ->orWhereHas('area', function($areaQuery) use ($user) {
                      $areaQuery->whereIn('id', $user->groupsAreasIds());
                  });
            })
            ->where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where(function($q) use ($startDate, $endDate) {
                // Chamados criados no período
                $q->whereBetween('created_at', [$startDate, $endDate])
                  // Ou chamados que vencem no período
                  ->orWhereBetween('due_at', [$startDate, $endDate])
                  // Ou chamados atribuídos no período
                  ->orWhereBetween('assigned_at', [$startDate, $endDate])
                  // Ou chamados próximos do vencimento (próximos 3 dias)
                  ->orWhere('due_at', '<=', now()->addDays(3))
                  ->where('due_at', '>=', now());
            });

        return $query->orderBy('due_at', 'asc')->get();
    }

    private function organizeTicketsByCategory($tickets, SlaCalculationService $slaService)
    {
        $categories = [
            'urgent' => ['tickets' => [], 'title' => '🚨 URGENTES (Vencidos ou Críticos)'],
            'near_due' => ['tickets' => [], 'title' => '⚠️ PRÓXIMOS DO VENCIMENTO'],
            'assigned_today' => ['tickets' => [], 'title' => '👤 ATRIBUÍDOS HOJE'],
            'created_today' => ['tickets' => [], 'title' => '📝 CRIADOS HOJE'],
            'other' => ['tickets' => [], 'title' => '📋 OUTROS CHAMADOS'],
        ];

        foreach ($tickets as $ticket) {
            $compliance = $slaService->checkSlaCompliance($ticket);
            
            if ($compliance['is_overdue']) {
                $categories['urgent']['tickets'][] = $ticket;
            } elseif ($compliance['is_near_due']) {
                $categories['near_due']['tickets'][] = $ticket;
            } elseif ($ticket->assigned_at && $ticket->assigned_at->isToday()) {
                $categories['assigned_today']['tickets'][] = $ticket;
            } elseif ($ticket->created_at->isToday()) {
                $categories['created_today']['tickets'][] = $ticket;
            } else {
                $categories['other']['tickets'][] = $ticket;
            }
        }

        // Remover categorias vazias
        return array_filter($categories, function($category) {
            return !empty($category['tickets']);
        });
    }
}