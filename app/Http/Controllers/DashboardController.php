<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ticket;
use App\Models\TicketEvaluation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard principal - métricas e indicadores reais
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAtendente = $user->isAtendente() && !$user->isAdmin() && !$user->isGestor();
        
        // Métricas básicas do usuário
        $myTickets = $user->requestedTickets()->count();
        $assignedTickets = $user->assignedTickets()->where('status', '!=', Ticket::STATUS_FINALIZED)->count();
        
        // Métricas gerais do sistema
        $totalTickets = $this->getTotalTickets($user, $isAtendente);
        $ticketsThisMonth = $this->getTicketsThisMonth($user, $isAtendente);
        
        // Status dos chamados
        $openTickets = $this->getTicketsByStatus('open', $user, $isAtendente);
        $inProgressTickets = $this->getTicketsByStatus('in_progress', $user, $isAtendente);
        $waitingTickets = $this->getTicketsByStatus('waiting_user', $user, $isAtendente);
        $resolvedTickets = $this->getTicketsByStatus('finalized', $user, $isAtendente);
        
        // SLA Metrics
        $sevenDaysAgo = now()->subDays(7);
        $overdueTickets = $this->getOverdueTickets($user, $isAtendente);
        $nearDueTickets = $this->getNearDueTickets($user, $isAtendente);
        
        // Calcular SLA em dia
        $totalActiveTickets = $this->getActiveTickets($user, $isAtendente);
        $slaOnTime = $totalActiveTickets > 0 
            ? round((($totalActiveTickets - $overdueTickets) / $totalActiveTickets) * 100, 1) 
            : 100;
        
        // Calcular satisfação real
        $satisfactionRate = TicketEvaluation::getOverallSatisfactionRate();
        $totalEvaluations = TicketEvaluation::count();
        
        // Tempo médio de resolução (real)
        $avgResolutionTime = $this->calculateAverageResolutionTime($user, $isAtendente);
        
        // Performance por área (com tempo médio real)
        $areaPerformance = $this->getAreaPerformance($user, $isAtendente);
        
        // Top atendentes (real)
        $topAttendants = $this->getTopAttendants($user, $isAtendente);
        
        // Chamados recentes
        $recentTickets = $this->getRecentTickets($user, $isAtendente);

        return view('dashboard.index', compact(
            'myTickets', 'assignedTickets', 'overdueTickets', 'nearDueTickets', 'recentTickets',
            'totalTickets', 'ticketsThisMonth', 'openTickets', 'inProgressTickets', 
            'waitingTickets', 'resolvedTickets', 'slaOnTime', 'avgResolutionTime',
            'areaPerformance', 'topAttendants', 'satisfactionRate', 'totalEvaluations'
        ));
    }

    /**
     * Obter total de tickets com filtros de permissão
     */
    private function getTotalTickets(User $user, bool $isAtendente): int
    {
        $query = Ticket::query();
        
        if (!$user->canManageTickets()) {
            return $query->where('requester_id', $user->id)->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Obter tickets do mês atual
     */
    private function getTicketsThisMonth(User $user, bool $isAtendente): int
    {
        $query = Ticket::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        
        if (!$user->canManageTickets()) {
            return $query->where('requester_id', $user->id)->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Obter tickets por status
     */
    private function getTicketsByStatus(string $status, User $user, bool $isAtendente): int
    {
        $query = Ticket::where('status', $status);
        
        if (!$user->canManageTickets()) {
            return $query->where('requester_id', $user->id)->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Obter tickets vencidos
     */
    private function getOverdueTickets(User $user, bool $isAtendente): int
    {
        $sevenDaysAgo = now()->subDays(7);
        $query = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->where('created_at', '<', $sevenDaysAgo);
        
        if (!$user->canManageTickets()) {
            return $query->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            })->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Obter tickets próximos do vencimento
     */
    private function getNearDueTickets(User $user, bool $isAtendente): int
    {
        // Tickets que vencem nas próximas 2 horas mas ainda não venceram
        $query = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->whereNotNull('due_at')
            ->where('due_at', '>', now())
            ->where('due_at', '<=', now()->addHours(2));
        
        if (!$user->canManageTickets()) {
            return $query->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            })->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Obter tickets ativos
     */
    private function getActiveTickets(User $user, bool $isAtendente): int
    {
        $query = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED);
        
        if (!$user->canManageTickets()) {
            return $query->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            })->count();
        }
        
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            return $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])->count();
        }
        
        return $query->count();
    }

    /**
     * Calcular tempo médio de resolução real
     */
    private function calculateAverageResolutionTime(User $user, bool $isAtendente): string
    {
        $query = Ticket::where('status', Ticket::STATUS_FINALIZED)
            ->whereNotNull('resolved_at')
            ->whereNotNull('created_at');
        
        // Aplicar filtros de permissão
        if (!$user->canManageTickets()) {
            $query->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            });
        } elseif ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            $query->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1]);
        }
        
        $resolvedTickets = $query->get();
        
        if ($resolvedTickets->isEmpty()) {
            return '0h';
        }
        
        $totalMinutes = $resolvedTickets->sum(function($ticket) {
            return $ticket->created_at->diffInMinutes($ticket->resolved_at);
        });
        
        $avgMinutes = round($totalMinutes / $resolvedTickets->count());
        
        if ($avgMinutes < 60) {
            return $avgMinutes . 'min';
        } elseif ($avgMinutes < 1440) { // Menos de 24h
            $hours = floor($avgMinutes / 60);
            $minutes = $avgMinutes % 60;
            return $minutes > 0 ? "{$hours}h {$minutes}min" : "{$hours}h";
        } else {
            $days = floor($avgMinutes / 1440);
            $hours = floor(($avgMinutes % 1440) / 60);
            return $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
        }
    }

    /**
     * Obter performance por área com tempo médio real
     */
    private function getAreaPerformance(User $user, bool $isAtendente): array
    {
        $areasQuery = Area::withCount('tickets');
        
        // Filtrar áreas se for atendente
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            $areasQuery->whereIn('id', !empty($areaIds) ? $areaIds : [-1]);
        }
        
        $areas = $areasQuery->get();
        
        return $areas->map(function($area) use ($user, $isAtendente) {
            $ticketsQuery = $area->tickets();
            
            // Aplicar filtros de permissão
            if (!$user->canManageTickets()) {
                $ticketsQuery->where(function($q) use ($user) {
                    $q->where('requester_id', $user->id)
                      ->orWhere('assignee_id', $user->id);
                });
            }
            
            $resolvedTickets = (clone $ticketsQuery)
                ->where('status', Ticket::STATUS_FINALIZED)
                ->whereNotNull('resolved_at')
                ->whereNotNull('created_at')
                ->get();
            
            // Calcular tempo médio real
            $avgTime = '0h';
            if ($resolvedTickets->isNotEmpty()) {
                $totalMinutes = $resolvedTickets->sum(function($ticket) {
                    return $ticket->created_at->diffInMinutes($ticket->resolved_at);
                });
                $avgMinutes = round($totalMinutes / $resolvedTickets->count());
                
                if ($avgMinutes < 60) {
                    $avgTime = $avgMinutes . 'min';
                } elseif ($avgMinutes < 1440) {
                    $hours = floor($avgMinutes / 60);
                    $avgTime = $hours . 'h';
                } else {
                    $days = floor($avgMinutes / 1440);
                    $hours = floor(($avgMinutes % 1440) / 60);
                    $avgTime = $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
                }
            }
            
            return [
                'id' => $area->id,
                'name' => $area->name,
                'tickets' => $area->tickets_count,
                'resolved' => $resolvedTickets->count(),
                'avg_time' => $avgTime,
            ];
        })
        ->filter(fn($area) => $area['tickets'] > 0) // Apenas áreas com tickets
        ->sortByDesc('tickets')
        ->take(5)
        ->values()
        ->toArray();
    }

    /**
     * Obter top atendentes
     */
    private function getTopAttendants(User $user, bool $isAtendente): array
    {
        $query = User::whereHas('assignedTickets', function($q) {
            $q->where('status', Ticket::STATUS_FINALIZED);
        });
        
        // Se for atendente, só mostrar da mesma área
        if ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            $query->whereHas('areas', function($q) use ($areaIds) {
                $q->whereIn('areas.id', !empty($areaIds) ? $areaIds : [-1]);
            });
        }
        
        return $query->withCount(['assignedTickets as resolved_count' => function($q) {
                $q->where('status', Ticket::STATUS_FINALIZED);
            }])
            ->with('areas')
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'area' => $user->areas->first()?->name ?? 'N/A',
                    'resolved' => $user->resolved_count,
                ];
            })
            ->sortByDesc('resolved')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Obter chamados recentes
     */
    private function getRecentTickets(User $user, bool $isAtendente)
    {
        $query = Ticket::with(['category', 'requester', 'assignee', 'area'])
            ->orderByDesc('created_at');
        
        if (!$user->canManageTickets()) {
            $query->where(function($q) use ($user) {
                $q->where('requester_id', $user->id)
                  ->orWhere('assignee_id', $user->id);
            });
        } elseif ($isAtendente) {
            $areaIds = $user->groupsAreasIds();
            $query->where(function($inner) use ($user, $areaIds) {
                $inner->whereIn('area_id', !empty($areaIds) ? $areaIds : [-1])
                    ->orWhere('assignee_id', $user->id);
            });
        }
        
        return $query->limit(10)->get();
    }
}
