<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlaWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $warningType // 'warning' ou 'overdue'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = $this->warningType === 'overdue' 
            ? 'URGENTE: Chamado #' . $this->ticket->id . ' vencido!'
            : 'ATENÇÃO: Chamado #' . $this->ticket->id . ' próximo do vencimento';

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Olá ' . $notifiable->name . '!');

        if ($this->warningType === 'overdue') {
            $message->line('🚨 **URGENTE:** Este chamado já passou do prazo!')
                   ->line('**Chamado:** ' . $this->ticket->title)
                   ->line('**Prazo:** ' . $this->ticket->due_at->format('d/m/Y H:i'))
                   ->line('**Status:** ' . ucfirst(str_replace('_', ' ', $this->ticket->status)))
                   ->line('**Ação necessária:** Resolva este chamado imediatamente!');
        } else {
            $message->line('⚠️ **ATENÇÃO:** Este chamado está próximo do vencimento!')
                   ->line('**Chamado:** ' . $this->ticket->title)
                   ->line('**Prazo:** ' . $this->ticket->due_at->format('d/m/Y H:i'))
                   ->line('**Status:** ' . ucfirst(str_replace('_', ' ', $this->ticket->status)))
                   ->line('**Ação necessária:** Priorize a resolução deste chamado!');
        }

        return $message
            ->action('Ver Chamado', route('tickets.show', $this->ticket))
            ->line('Mantenha o SLA em dia para garantir a qualidade do atendimento.')
            ->salutation('Equipe Printbag Embalagens');
    }

    public function toDatabase($notifiable): array
    {
        $isOverdue = $this->warningType === 'overdue';
        $timeLeft = $this->ticket->due_at ? $this->ticket->due_at->diffForHumans() : 'N/A';
        
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_type' => $this->ticket->request_type ?? 'Geral',
            'area_name' => $this->ticket->area->name ?? 'N/A',
            'requester_name' => $this->ticket->requester->name,
            'assignee_name' => $this->ticket->assignee->name ?? 'Não atribuído',
            'due_at' => $this->ticket->due_at,
            'warning_type' => $this->warningType,
            'is_overdue' => $isOverdue,
            'time_left' => $timeLeft,
            'message' => $isOverdue 
                ? '🚨 URGENTE: Chamado #' . $this->ticket->id . ' (' . $this->ticket->title . ') está VENCIDO!'
                : '⚠️ ATENÇÃO: Chamado #' . $this->ticket->id . ' (' . $this->ticket->title . ') próximo do vencimento.',
            'action_text' => 'Ver chamado',
            'url' => route('tickets.show', $this->ticket),
            'priority' => $isOverdue ? 'critical' : 'high',
            'category' => 'sla_warning',
        ];
    }
}