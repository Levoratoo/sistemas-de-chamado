<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Area;
use App\Services\SlaCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExecutiveDashboardController extends Controller
{
    public function index(SlaCalculationService $slaService): \Illuminate\View\View
    {
        $now = now();
        $thirtyDaysAgo = $now->copy()->subDays(30);
        $sevenDaysAgo = $now->copy()->subDays(7);

        // Métricas principais baseadas em SLA de 7 dias
        $metrics = $this->getExecutiveMetrics($slaService, $thirtyDaysAgo, $now);
        
        // Gráficos e tendências
        $charts = $this->getChartData($thirtyDaysAgo, $now);
        
        // Performance por área
        $areaPerformance = $this->getAreaPerformance($slaService, $thirtyDaysAgo, $now);
        
        // Alertas SLA
        $slaAlerts = $this->getSlaAlerts($slaService);
        
        // Top performers
        $topPerformers = $this->getTopPerformers($thirtyDaysAgo, $now);

        return view('dashboard.executive', compact(
            'metrics',
            'charts', 
            'areaPerformance',
            'slaAlerts',
            'topPerformers'
        ));
    }

    private function getExecutiveMetrics(SlaCalculationService $slaService, Carbon $startDate, Carbon $endDate): array
    {
        $totalTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolvedTickets = Ticket::whereBetween('resolved_at', [$startDate, $endDate])->count();
        
        // SLA Compliance baseado em 7 dias
        $slaStats = $slaService->getSlaStatistics($startDate, $endDate);
        
        // Tempo médio de resolução
        $avgResolutionTime = Ticket::whereBetween('resolved_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        // Satisfação média
        $avgSatisfaction = DB::table('ticket_evaluations')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating');

        return [
            'total_tickets' => $totalTickets,
            'resolved_tickets' => $resolvedTickets,
            'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0,
            'sla_compliance' => $slaStats['sla_compliance_rate'],
            'avg_resolution_time' => round($avgResolutionTime ?? 0, 1),
            'avg_satisfaction' => round($avgSatisfaction ?? 0, 1),
            'overdue_tickets' => $slaStats['overdue_tickets'],
            'near_due_tickets' => $slaStats['near_due_tickets'],
        ];
    }

    private function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        // Tendência de chamados (últimos 30 dias)
        $ticketTrend = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        // Distribuição por área
        $areaDistribution = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->join('areas', 'tickets.area_id', '=', 'areas.id')
            ->selectRaw('areas.name, COUNT(*) as count')
            ->groupBy('areas.id', 'areas.name')
            ->get()
            ->pluck('count', 'name');

        // SLA compliance ao longo do tempo
        $slaTrend = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('DATE(resolved_at) as date, 
                COUNT(*) as total,
                SUM(CASE WHEN resolved_at <= due_at THEN 1 ELSE 0 END) as on_time')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'compliance' => $item->total > 0 ? round(($item->on_time / $item->total) * 100, 1) : 0
                ];
            });

        return [
            'ticket_trend' => $ticketTrend,
            'area_distribution' => $areaDistribution,
            'sla_trend' => $slaTrend,
        ];
    }

    private function getAreaPerformance(SlaCalculationService $slaService, Carbon $startDate, Carbon $endDate): array
    {
        $areas = Area::withCount(['tickets' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        $performance = [];
        foreach ($areas as $area) {
            $areaTickets = Ticket::where('area_id', $area->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $totalTickets = $areaTickets->count();
            $resolvedTickets = $areaTickets->where('status', Ticket::STATUS_FINALIZED)->count();
            
            $onTimeTickets = $areaTickets->filter(function($ticket) {
                return $ticket->resolved_at && $ticket->resolved_at->lte($ticket->due_at);
            })->count();

            $performance[] = [
                'area' => $area->name,
                'total_tickets' => $totalTickets,
                'resolved_tickets' => $resolvedTickets,
                'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0,
                'sla_compliance' => $totalTickets > 0 ? round(($onTimeTickets / $totalTickets) * 100, 1) : 0,
                'avg_resolution_hours' => $this->calculateAvgResolutionTime($areaTickets),
            ];
        }

        return collect($performance)->sortByDesc('sla_compliance')->values()->toArray();
    }

    private function getSlaAlerts(SlaCalculationService $slaService): array
    {
        // Tickets vencidos - apenas se passaram mais de 7 dias desde a criação
        $sevenDaysAgo = now()->subDays(7);
        $overdueTickets = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where('created_at', '<', $sevenDaysAgo)
            ->with(['area', 'assignee', 'requester'])
            ->get();
        
        // Tickets próximos do vencimento (criados há menos de 7 dias mas perto do limite)
        $nearDueTickets = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where('created_at', '>=', $sevenDaysAgo)
            ->where('created_at', '<=', now()->subDays(6))
            ->with(['area', 'assignee', 'requester'])
            ->get();

        $alerts = [];
        
        // Adicionar tickets vencidos
        foreach ($overdueTickets as $ticket) {
            $slaDate = $ticket->created_at->copy()->addDays(7);
            $daysOverdue = (int) $slaDate->diffInDays(now());
            
            $alerts[] = [
                'ticket_id' => $ticket->id,
                'title' => $ticket->title,
                'area' => $ticket->area->name ?? 'N/A',
                'assignee' => $ticket->assignee->name ?? 'Não atribuído',
                'due_at' => $slaDate,
                'is_overdue' => true,
                'is_near_due' => false,
                'time_remaining' => "Vencido há {$daysOverdue} " . ($daysOverdue === 1 ? 'dia' : 'dias'),
                'priority' => $ticket->priority,
                'days_overdue' => $daysOverdue,
            ];
        }
        
        // Adicionar tickets próximos do vencimento
        foreach ($nearDueTickets as $ticket) {
            $slaDate = $ticket->created_at->copy()->addDays(7);
            $daysRemaining = (int) $slaDate->diffInDays(now(), false);
            
            $alerts[] = [
                'ticket_id' => $ticket->id,
                'title' => $ticket->title,
                'area' => $ticket->area->name ?? 'N/A',
                'assignee' => $ticket->assignee->name ?? 'Não atribuído',
                'due_at' => $slaDate,
                'is_overdue' => false,
                'is_near_due' => true,
                'time_remaining' => $daysRemaining > 0 ? "{$daysRemaining} dias restantes" : "Próximo do vencimento",
                'priority' => $ticket->priority,
            ];
        }

        return collect($alerts)->sortByDesc('is_overdue')->sortBy('due_at')->values()->toArray();
    }

    private function getTopPerformers(Carbon $startDate, Carbon $endDate): array
    {
        $performers = User::whereHas('tickets', function($query) use ($startDate, $endDate) {
            $query->whereBetween('resolved_at', [$startDate, $endDate]);
        })
        ->withCount(['tickets' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('resolved_at', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($user) use ($startDate, $endDate) {
            $resolvedTickets = $user->tickets()
                ->whereBetween('resolved_at', [$startDate, $endDate])
                ->get();

            $onTimeTickets = $resolvedTickets->filter(function($ticket) {
                return $ticket->resolved_at->lte($ticket->due_at);
            })->count();

            $avgResolutionTime = $resolvedTickets->avg(function($ticket) {
                return $ticket->created_at->diffInHours($ticket->resolved_at);
            });

            return [
                'user' => $user->name,
                'resolved_tickets' => $resolvedTickets->count(),
                'sla_compliance' => $resolvedTickets->count() > 0 ? 
                    round(($onTimeTickets / $resolvedTickets->count()) * 100, 1) : 0,
                'avg_resolution_hours' => round($avgResolutionTime ?? 0, 1),
            ];
        })
        ->sortByDesc('sla_compliance')
        ->take(10)
        ->values()
        ->toArray();

        return $performers;
    }

    private function calculateAvgResolutionTime($tickets): float
    {
        $resolvedTickets = $tickets->where('status', Ticket::STATUS_FINALIZED)
            ->whereNotNull('resolved_at');

        if ($resolvedTickets->isEmpty()) {
            return 0;
        }

        $totalHours = $resolvedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInHours($ticket->resolved_at);
        });

        return round($totalHours / $resolvedTickets->count(), 1);
    }
}