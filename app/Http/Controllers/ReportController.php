<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Area;
use App\Services\SlaCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ReportController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('reports.index');
    }

    public function generate(Request $request, SlaCalculationService $slaService): \Illuminate\Http\Response
    {
        $request->validate([
            'report_type' => 'required|in:weekly,monthly,quarterly,custom',
            'start_date' => 'required_if:report_type,custom|date',
            'end_date' => 'required_if:report_type,custom|date|after:start_date',
            'format' => 'required|in:pdf,excel,email',
            'email' => 'required_if:format,email|email',
        ]);

        $dateRange = $this->getDateRange($request);
        $reportData = $this->generateReportData($slaService, $dateRange['start'], $dateRange['end']);
        
        switch ($request->format) {
            case 'pdf':
                return $this->generatePdfReport($reportData, $dateRange);
            case 'excel':
                return $this->generateExcelReport($reportData, $dateRange);
            case 'email':
                $this->sendEmailReport($reportData, $dateRange, $request->email);
                return response()->json(['message' => 'Relatório enviado por email com sucesso!']);
        }
    }

    public function scheduleReport(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'report_type' => 'required|in:weekly,monthly,quarterly',
            'email' => 'required|email',
            'day_of_week' => 'required_if:report_type,weekly|integer|between:1,7',
            'day_of_month' => 'required_if:report_type,monthly|integer|between:1,31',
        ]);

        // Aqui você salvaria a configuração no banco de dados
        // Por simplicidade, vamos apenas retornar sucesso
        
        return response()->json([
            'message' => 'Relatório automático agendado com sucesso!',
            'schedule' => [
                'type' => $request->report_type,
                'email' => $request->email,
                'frequency' => $this->getFrequencyDescription($request),
            ]
        ]);
    }

    private function getDateRange(Request $request): array
    {
        switch ($request->report_type) {
            case 'weekly':
                return [
                    'start' => now()->subWeek()->startOfWeek(),
                    'end' => now()->subWeek()->endOfWeek(),
                ];
            case 'monthly':
                return [
                    'start' => now()->subMonth()->startOfMonth(),
                    'end' => now()->subMonth()->endOfMonth(),
                ];
            case 'quarterly':
                $quarter = ceil(now()->month / 3);
                $startMonth = ($quarter - 1) * 3 + 1;
                return [
                    'start' => now()->subMonth()->setMonth($startMonth)->startOfMonth(),
                    'end' => now()->subMonth()->setMonth($startMonth + 2)->endOfMonth(),
                ];
            case 'custom':
                return [
                    'start' => Carbon::parse($request->start_date),
                    'end' => Carbon::parse($request->end_date),
                ];
        }
    }

    private function generateReportData(SlaCalculationService $slaService, Carbon $startDate, Carbon $endDate): array
    {
        // Métricas gerais
        $totalTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])->count();
        $resolvedTickets = Ticket::whereBetween('resolved_at', [$startDate, $endDate])->count();
        
        // SLA Statistics
        $slaStats = $slaService->getSlaStatistics($startDate, $endDate);
        
        // Performance por área
        $areaPerformance = $this->getAreaPerformanceData($startDate, $endDate);
        
        // Performance por usuário
        $userPerformance = $this->getUserPerformanceData($startDate, $endDate);
        
        // Tendências
        $trends = $this->getTrendData($startDate, $endDate);
        
        // Tipos de chamado
        $requestTypes = $this->getRequestTypeData($startDate, $endDate);

        return [
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
                'days' => $startDate->diffInDays($endDate),
            ],
            'metrics' => [
                'total_tickets' => $totalTickets,
                'resolved_tickets' => $resolvedTickets,
                'resolution_rate' => $totalTickets > 0 ? round(($resolvedTickets / $totalTickets) * 100, 1) : 0,
                'sla_compliance' => $slaStats['sla_compliance_rate'],
                'avg_resolution_time' => $this->getAvgResolutionTime($startDate, $endDate),
                'satisfaction_rate' => $this->getSatisfactionRate($startDate, $endDate),
            ],
            'area_performance' => $areaPerformance,
            'user_performance' => $userPerformance,
            'trends' => $trends,
            'request_types' => $requestTypes,
        ];
    }

    private function getAreaPerformanceData(Carbon $startDate, Carbon $endDate): array
    {
        return Area::withCount(['tickets' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])
        ->get()
        ->map(function($area) use ($startDate, $endDate) {
            $tickets = Ticket::where('area_id', $area->id)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            $resolved = $tickets->where('status', Ticket::STATUS_FINALIZED)->count();
            $onTime = $tickets->filter(function($ticket) {
                return $ticket->resolved_at && $ticket->resolved_at->lte($ticket->due_at);
            })->count();

            return [
                'area' => $area->name,
                'total_tickets' => $tickets->count(),
                'resolved_tickets' => $resolved,
                'resolution_rate' => $tickets->count() > 0 ? round(($resolved / $tickets->count()) * 100, 1) : 0,
                'sla_compliance' => $tickets->count() > 0 ? round(($onTime / $tickets->count()) * 100, 1) : 0,
            ];
        })
        ->sortByDesc('sla_compliance')
        ->values()
        ->toArray();
    }

    private function getUserPerformanceData(Carbon $startDate, Carbon $endDate): array
    {
        return User::whereHas('tickets', function($query) use ($startDate, $endDate) {
            $query->whereBetween('resolved_at', [$startDate, $endDate]);
        })
        ->get()
        ->map(function($user) use ($startDate, $endDate) {
            $resolvedTickets = $user->tickets()
                ->whereBetween('resolved_at', [$startDate, $endDate])
                ->get();

            $onTimeTickets = $resolvedTickets->filter(function($ticket) {
                return $ticket->resolved_at->lte($ticket->due_at);
            })->count();

            return [
                'user' => $user->name,
                'resolved_tickets' => $resolvedTickets->count(),
                'sla_compliance' => $resolvedTickets->count() > 0 ? 
                    round(($onTimeTickets / $resolvedTickets->count()) * 100, 1) : 0,
            ];
        })
        ->sortByDesc('sla_compliance')
        ->take(10)
        ->values()
        ->toArray();
    }

    private function getTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $dailyTickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        $dailyResolved = Ticket::whereBetween('resolved_at', [$startDate, $endDate])
            ->selectRaw('DATE(resolved_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date');

        return [
            'daily_created' => $dailyTickets,
            'daily_resolved' => $dailyResolved,
        ];
    }

    private function getRequestTypeData(Carbon $startDate, Carbon $endDate): array
    {
        return Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('request_type, COUNT(*) as count')
            ->groupBy('request_type')
            ->get()
            ->pluck('count', 'request_type')
            ->toArray();
    }

    private function getAvgResolutionTime(Carbon $startDate, Carbon $endDate): float
    {
        $avgHours = Ticket::whereBetween('resolved_at', [$startDate, $endDate])
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours')
            ->value('avg_hours');

        return round($avgHours ?? 0, 1);
    }

    private function getSatisfactionRate(Carbon $startDate, Carbon $endDate): float
    {
        $avgRating = DB::table('ticket_evaluations')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('rating');

        return round($avgRating ?? 0, 1);
    }

    private function generatePdfReport(array $data, array $dateRange): \Illuminate\Http\Response
    {
        // Aqui você implementaria a geração de PDF usando uma biblioteca como DomPDF
        // Por simplicidade, vamos retornar uma resposta JSON
        return response()->json([
            'message' => 'Relatório PDF gerado com sucesso!',
            'data' => $data,
            'period' => $dateRange,
        ]);
    }

    private function generateExcelReport(array $data, array $dateRange): \Illuminate\Http\Response
    {
        // Aqui você implementaria a geração de Excel usando uma biblioteca como Laravel Excel
        // Por simplicidade, vamos retornar uma resposta JSON
        return response()->json([
            'message' => 'Relatório Excel gerado com sucesso!',
            'data' => $data,
            'period' => $dateRange,
        ]);
    }

    private function sendEmailReport(array $data, array $dateRange, string $email): void
    {
        Mail::send('emails.report', [
            'data' => $data,
            'dateRange' => $dateRange,
        ], function ($message) use ($email, $dateRange) {
            $message->to($email)
                   ->subject('Relatório de Chamados - ' . $dateRange['start']->format('d/m/Y') . ' a ' . $dateRange['end']->format('d/m/Y'));
        });
    }

    private function getFrequencyDescription(Request $request): string
    {
        switch ($request->report_type) {
            case 'weekly':
                $days = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];
                return "Toda {$days[$request->day_of_week - 1]}";
            case 'monthly':
                return "Todo dia {$request->day_of_month} do mês";
            case 'quarterly':
                return "A cada 3 meses";
        }
    }
}