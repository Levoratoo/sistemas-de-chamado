<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SlaWarningNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketFinalizedNotification;
use App\Jobs\SendEmailJob;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Enviar notificação quando ticket é finalizado
     */
    public function notifyTicketFinalized(Ticket $ticket): void
    {
        if ($ticket->requester) {
            $this->queueEmail($ticket->requester, new TicketFinalizedNotification($ticket), 'ticket_finalized');
        }
    }

    /**
     * Enviar notificação quando ticket é atribuído
     */
    public function notifyTicketAssigned(Ticket $ticket, User $assignedBy): void
    {
        if ($ticket->assignee) {
            $this->queueEmail($ticket->assignee, new TicketAssignedNotification($ticket, $assignedBy->name), 'ticket_assigned');
        }
    }

    /**
     * Enviar alertas SLA para tickets próximos do vencimento ou vencidos
     */
    public function sendSlaAlerts(): void
    {
        // Tickets próximos do vencimento (2 horas)
        $warningTickets = Ticket::where('due_at', '>', now())
            ->where('due_at', '<=', now()->addHours(2))
            ->where('status', '!=', Ticket::STATUS_FINALIZED)
            ->whereNotNull('assignee_id')
            ->with(['assignee', 'area'])
            ->get();

        foreach ($warningTickets as $ticket) {
            if ($ticket->assignee && $this->isValidEmail($ticket->assignee->email)) {
                $this->queueEmail($ticket->assignee, new SlaWarningNotification($ticket, 'warning'), 'sla_warning');
            }
        }

        // Tickets vencidos - apenas se passaram mais de 7 dias desde a criação
        $sevenDaysAgo = now()->subDays(7);
        $overdueTickets = Ticket::where('status', '!=', Ticket::STATUS_FINALIZED)
            ->whereNotNull('assignee_id')
            ->where('created_at', '<', $sevenDaysAgo)
            ->with(['assignee', 'area'])
            ->get();

        foreach ($overdueTickets as $ticket) {
            if ($ticket->assignee && $this->isValidEmail($ticket->assignee->email)) {
                $this->queueEmail($ticket->assignee, new SlaWarningNotification($ticket, 'overdue'), 'sla_overdue');
            }
        }

        // Notificar gestores sobre tickets vencidos
        $managers = User::whereHas('role', function($query) {
            $query->whereIn('name', ['admin', 'gestor']);
        })->get();

        if ($overdueTickets->count() > 0) {
            foreach ($managers as $manager) {
                if ($this->isValidEmail($manager->email)) {
                    $this->queueEmail($manager, new SlaWarningNotification($overdueTickets->first(), 'overdue'), 'sla_overdue');
                }
            }
        }
    }

    /**
     * Enviar notificação para novos chamados na fila
     */
    public function notifyNewTicketInQueue(Ticket $ticket): void
    {
        // Notificar atendentes da área
        $attendants = User::whereHas('areas', function($query) use ($ticket) {
            $query->where('areas.id', $ticket->area_id);
        })->whereHas('role', function($query) {
            $query->whereIn('name', ['atendente', 'gestor', 'admin']);
        })->get();

        foreach ($attendants as $attendant) {
            $this->queueEmail($attendant, new TicketAssignedNotification($ticket, 'Sistema'), 'ticket_assigned');
        }
    }

    /**
     * Alias para notifyNewTicketInQueue - manter compatibilidade
     */
    public function notifyTicketCreated(Ticket $ticket): void
    {
        $this->notifyNewTicketInQueue($ticket);
    }

    /**
     * Enviar email para fila com retry automático
     */
    private function queueEmail(User $user, $notification, string $type): void
    {
        // Validar se o email é válido antes de tentar enviar
        if (!$this->isValidEmail($user->email)) {
            Log::warning("Email inválido ignorado", [
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => $type
            ]);
            return;
        }
        
        // Verificar se não é um domínio de teste em produção
        if (app()->environment('production')) {
            $domain = $this->getEmailDomain($user->email);
            $testDomains = ['local', 'test', 'example', 'localhost'];
            
            if (in_array($domain, $testDomains, true)) {
                Log::warning("Email de teste ignorado em produção", [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'domain' => $domain,
                    'type' => $type
                ]);
                return;
            }
        }
        
        SendEmailJob::dispatch($user, $notification, $type);
    }

    /**
     * Validar se um email é válido
     */
    private function isValidEmail(string $email): bool
    {
        if (empty($email)) {
            return false;
        }
        
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Extrair domínio de um email
     */
    private function getEmailDomain(string $email): string
    {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower($parts[1]) : '';
    }

    /**
     * Enviar notificação em lote para múltiplos usuários
     */
    public function notifyBulkUsers(array $users, $notification, string $type): void
    {
        foreach ($users as $user) {
            $this->queueEmail($user, $notification, $type);
        }
    }
}
