<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    /**
     * Dados para gráfico de tendência dos últimos 30 dias
     */
    public function trendData(Request $request): JsonResponse
    {
        $user = $request->user();
        $days = 30;
        
        // Base query considerando permissões do usuário
        $baseQuery = Ticket::query();
        
        if (!$user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();
                $baseQuery->whereIn('area_id', $areaIds);
            } elseif ($user->isAtendente()) {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                      ->orWhere('requester_id', $user->id);
                });
            } else {
                $baseQuery->where('requester_id', $user->id);
            }
        }

        // Dados dos últimos 30 dias
        $trendData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $nextDate = now()->subDays($i - 1)->format('Y-m-d');
            
            $created = (clone $baseQuery)->whereBetween('created_at', [$date, $nextDate])->count();
            $resolved = (clone $baseQuery)->whereBetween('resolved_at', [$date, $nextDate])->count();
            
            $trendData[] = [
                'date' => $date,
                'created' => $created,
                'resolved' => $resolved,
                'label' => now()->subDays($i)->format('d/m')
            ];
        }

        return response()->json([
            'labels' => array_column($trendData, 'label'),
            'datasets' => [
                [
                    'label' => 'Criados',
                    'data' => array_column($trendData, 'created'),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4
                ],
                [
                    'label' => 'Resolvidos',
                    'data' => array_column($trendData, 'resolved'),
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    /**
     * Dados para gráfico de distribuição por área
     */
    public function areaDistribution(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Area::withCount('tickets');
        
        if (!$user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();
                $query->whereIn('id', $areaIds);
            }
        }

        $areas = $query->where('active', true)->get();

        $colors = [
            'rgb(59, 130, 246)',   // Blue
            'rgb(16, 185, 129)',   // Emerald
            'rgb(245, 158, 11)',   // Amber
            'rgb(239, 68, 68)',    // Red
            'rgb(139, 92, 246)',    // Violet
            'rgb(236, 72, 153)',    // Pink
            'rgb(14, 165, 233)',    // Sky
            'rgb(34, 197, 94)',    // Green
        ];

        return response()->json([
            'labels' => $areas->pluck('name')->toArray(),
            'datasets' => [
                [
                    'data' => $areas->pluck('tickets_count')->toArray(),
                    'backgroundColor' => array_slice($colors, 0, $areas->count()),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff'
                ]
            ]
        ]);
    }

    /**
     * Dados para gráfico de SLA compliance
     */
    public function slaCompliance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $baseQuery = Ticket::query();
        
        if (!$user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();
                $baseQuery->whereIn('area_id', $areaIds);
            } elseif ($user->isAtendente()) {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                      ->orWhere('requester_id', $user->id);
                });
            } else {
                $baseQuery->where('requester_id', $user->id);
            }
        }

        $total = $baseQuery->where('status', '!=', 'open')->count();
        $onTime = $baseQuery->where('status', '!=', 'open')
            ->where('due_at', '>=', DB::raw('resolved_at'))
            ->count();
        
        $overdue = $baseQuery->where('status', '!=', 'open')
            ->where('due_at', '<', DB::raw('resolved_at'))
            ->count();

        $complianceRate = $total > 0 ? round(($onTime / $total) * 100, 1) : 0;

        return response()->json([
            'complianceRate' => $complianceRate,
            'total' => $total,
            'onTime' => $onTime,
            'overdue' => $overdue,
            'datasets' => [
                [
                    'data' => [$onTime, $overdue],
                    'backgroundColor' => ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                    'borderWidth' => 0
                ]
            ]
        ]);
    }

    /**
     * Dados para ranking de performance dos atendentes
     */
    public function attendantPerformance(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = User::whereHas('assignedTickets', function($q) {
            $q->where('status', 'finalized');
        });

        if (!$user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();
                $query->whereHas('areas', function($q) use ($areaIds) {
                    $q->whereIn('areas.id', $areaIds);
                });
            }
        }

        $attendants = $query->withCount(['assignedTickets as resolved_count' => function($q) {
            $q->where('status', 'finalized');
        }])
        ->with('areas')
        ->orderByDesc('resolved_count')
        ->limit(10)
        ->get();

        return response()->json([
            'labels' => $attendants->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Chamados Resolvidos',
                    'data' => $attendants->pluck('resolved_count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    /**
     * Dados para gráfico de status dos chamados
     */
    public function statusDistribution(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $baseQuery = Ticket::query();
        
        if (!$user->isAdmin()) {
            if ($user->isGestor()) {
                $areaIds = $user->groupsAreasIds();
                $baseQuery->whereIn('area_id', $areaIds);
            } elseif ($user->isAtendente()) {
                $baseQuery->where(function($q) use ($user) {
                    $q->where('assignee_id', $user->id)
                      ->orWhere('requester_id', $user->id);
                });
            } else {
                $baseQuery->where('requester_id', $user->id);
            }
        }

        $statusData = $baseQuery->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $statusLabels = [
            'open' => 'Abertos',
            'in_progress' => 'Em Progresso',
            'waiting_user' => 'Aguardando',
            'finalized' => 'Finalizados'
        ];

        $statusColors = [
            'open' => 'rgb(59, 130, 246)',
            'in_progress' => 'rgb(245, 158, 11)',
            'waiting_user' => 'rgb(249, 115, 22)',
            'finalized' => 'rgb(34, 197, 94)'
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statusLabels as $status => $label) {
            $count = $statusData->get($status)?->count ?? 0;
            if ($count > 0) {
                $labels[] = $label;
                $data[] = $count;
                $colors[] = $statusColors[$status];
            }
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff'
                ]
            ]
        ]);
    }
}