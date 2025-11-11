<?php

namespace App\Services;

use App\Models\SlaRequestType;
use App\Models\Sla;
use App\Models\Ticket;
use Carbon\Carbon;

class SlaCalculationService
{
    /**
     * Calcular SLA para um ticket baseado no tipo de chamado e prioridade
     */
    public function calculateSlaForTicket(string $requestType, string $priority = 'medium', int $categoryId = null): array
    {
        // 1. Tentar encontrar SLA por tipo de chamado (PRIORIDADE MÁXIMA)
        $slaRequestType = SlaRequestType::getSlaForRequestType($requestType, $priority);
        
        if ($slaRequestType) {
            return [
                'source' => 'request_type',
                'sla' => $slaRequestType,
                'due_at' => $slaRequestType->calculateDueDate(),
                'respond_by' => $slaRequestType->calculateResponseDate(),
                'response_time_minutes' => $slaRequestType->response_time_minutes,
                'resolve_time_minutes' => $slaRequestType->resolve_time_minutes,
            ];
        }

        // 2. Tentar encontrar SLA por categoria (FALLBACK)
        if ($categoryId) {
            $slaCategory = Sla::where('category_id', $categoryId)
                ->where('priority', $priority)
                ->where('active', true)
                ->first();

            if ($slaCategory) {
                return [
                    'source' => 'category',
                    'sla' => $slaCategory,
                    'due_at' => now()->addMinutes($slaCategory->resolve_time_minutes),
                    'respond_by' => now()->addMinutes($slaCategory->response_time_minutes),
                    'response_time_minutes' => $slaCategory->response_time_minutes,
                    'resolve_time_minutes' => $slaCategory->resolve_time_minutes,
                ];
            }
        }

        // 3. Usar SLA padrão (ÚLTIMO RECURSO)
        $defaultSla = SlaRequestType::getDefaultSla();
        
        return [
            'source' => 'default',
            'sla' => $defaultSla,
            'due_at' => $defaultSla->calculateDueDate(),
            'respond_by' => $defaultSla->calculateResponseDate(),
            'response_time_minutes' => $defaultSla->response_time_minutes,
            'resolve_time_minutes' => $defaultSla->resolve_time_minutes,
        ];
    }

    /**
     * Aplicar SLA a um ticket existente
     */
    public function applySlaToTicket(Ticket $ticket): Ticket
    {
        $requestType = $ticket->request_type ?? 'geral';
        $priority = $ticket->priority ?? 'medium';
        $categoryId = $ticket->category_id;

        $slaData = $this->calculateSlaForTicket($requestType, $priority, $categoryId);

        $ticket->update([
            'due_at' => $slaData['due_at'],
            'respond_by' => $slaData['respond_by'],
        ]);

        return $ticket;
    }

    /**
     * Verificar se ticket está dentro do SLA
     */
    public function checkSlaCompliance(Ticket $ticket): array
    {
        $now = now();
        $isOverdue = $ticket->due_at && $ticket->due_at->isBefore($now);
        $isNearDue = $ticket->due_at && $ticket->due_at->isBefore($now->addHours(2));
        
        // Verificar primeira resposta
        $firstResponseOnTime = true;
        if ($ticket->respond_by && $ticket->first_response_at) {
            $firstResponseOnTime = $ticket->first_response_at->isBefore($ticket->respond_by);
        } elseif ($ticket->respond_by) {
            $firstResponseOnTime = $ticket->respond_by->isAfter($now);
        }

        return [
            'is_overdue' => $isOverdue,
            'is_near_due' => $isNearDue,
            'first_response_on_time' => $firstResponseOnTime,
            'sla_status' => $isOverdue ? 'overdue' : ($isNearDue ? 'warning' : 'on_time'),
            'time_remaining' => $ticket->due_at ? $ticket->due_at->diffForHumans($now) : null,
            'overdue_hours' => $isOverdue && $ticket->due_at ? $now->diffInHours($ticket->due_at) : 0,
        ];
    }

    /**
     * Obter estatísticas de SLA para dashboard
     */
    public function getSlaStatistics(\Carbon\Carbon $startDate = null, \Carbon\Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $tickets = Ticket::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', Ticket::STATUS_FINALIZED)
            ->get();

        $totalTickets = $tickets->count();
        $overdueTickets = 0;
        $nearDueTickets = 0;
        $onTimeTickets = 0;

        foreach ($tickets as $ticket) {
            $compliance = $this->checkSlaCompliance($ticket);
            
            switch ($compliance['sla_status']) {
                case 'overdue':
                    $overdueTickets++;
                    break;
                case 'warning':
                    $nearDueTickets++;
                    break;
                case 'on_time':
                    $onTimeTickets++;
                    break;
            }
        }

        $slaComplianceRate = $totalTickets > 0 ? round(($onTimeTickets / $totalTickets) * 100, 2) : 0;

        return [
            'total_tickets' => $totalTickets,
            'overdue_tickets' => $overdueTickets,
            'near_due_tickets' => $nearDueTickets,
            'on_time_tickets' => $onTimeTickets,
            'sla_compliance_rate' => $slaComplianceRate,
            'period' => [
                'start' => $startDate->format('d/m/Y'),
                'end' => $endDate->format('d/m/Y'),
            ],
        ];
    }

    /**
     * Obter SLA por tipo de chamado para exibição
     */
    public function getSlaByRequestType(string $requestType): array
    {
        $priorities = ['low', 'medium', 'high', 'critical'];
        $slas = [];

        foreach ($priorities as $priority) {
            $sla = SlaRequestType::getSlaForRequestType($requestType, $priority);
            if ($sla) {
                $slas[$priority] = [
                    'response_time' => $sla->formatted_response_time,
                    'resolve_time' => $sla->formatted_resolve_time,
                    'response_minutes' => $sla->response_time_minutes,
                    'resolve_minutes' => $sla->resolve_time_minutes,
                ];
            }
        }

        return $slas;
    }
}










