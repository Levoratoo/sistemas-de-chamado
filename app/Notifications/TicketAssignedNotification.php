<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket,
        public string $assignedBy
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo chamado atribuído: #' . $this->ticket->id)
            ->greeting('Olá ' . $notifiable->name . '!')
            ->line('Um novo chamado foi atribuído para você.')
            ->line('**Chamado:** ' . $this->ticket->title)
            ->line('**Área:** ' . ($this->ticket->area->name ?? 'N/A'))
            ->line('**Solicitante:** ' . $this->ticket->requester->name)
            ->line('**Atribuído por:** ' . $this->assignedBy)
            ->line('**Prazo:** ' . ($this->ticket->due_at ? $this->ticket->due_at->format('d/m/Y H:i') : 'Não definido'))
            ->action('Ver Chamado', route('tickets.show', $this->ticket))
            ->line('Por favor, acesse o sistema para trabalhar neste chamado.')
            ->salutation('Equipe Printbag Embalagens');
    }

    public function toDatabase($notifiable): array
    {
        $urgency = $this->ticket->due_at && $this->ticket->due_at->isBefore(now()->addHours(4)) ? 'urgente' : 'normal';
        
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_type' => $this->ticket->request_type ?? 'Geral',
            'area_name' => $this->ticket->area->name ?? 'N/A',
            'requester_name' => $this->ticket->requester->name,
            'assigned_by' => $this->assignedBy,
            'due_at' => $this->ticket->due_at,
            'urgency' => $urgency,
            'message' => 'Novo chamado #' . $this->ticket->id . ' (' . $this->ticket->title . ') foi atribuído a você por ' . $this->assignedBy . '.',
            'action_text' => 'Ver chamado',
            'url' => route('tickets.show', $this->ticket),
            'priority' => $urgency === 'urgente' ? 'high' : 'medium',
            'category' => 'ticket_assigned',
        ];
    }
}