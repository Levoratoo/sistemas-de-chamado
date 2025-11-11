<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketFinalizedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Ticket $ticket
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Chamado #' . $this->ticket->id . ' foi finalizado')
            ->greeting('Olá ' . $notifiable->name . '!')
            ->line('Seu chamado foi finalizado e está pronto para avaliação.')
            ->line('**Chamado:** ' . $this->ticket->title)
            ->line('**Área:** ' . ($this->ticket->area->name ?? 'N/A'))
            ->line('**Finalizado em:** ' . ($this->ticket->resolved_at ? $this->ticket->resolved_at->format('d/m/Y H:i') : 'Agora'))
            ->action('Avaliar Chamado', route('tickets.evaluate', $this->ticket))
            ->line('Agradecemos por usar nosso sistema!')
            ->salutation('Equipe Printbag Embalagens');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_title' => $this->ticket->title,
            'ticket_type' => $this->ticket->request_type ?? 'Geral',
            'area_name' => $this->ticket->area->name ?? 'N/A',
            'assignee_name' => $this->ticket->assignee->name ?? 'Sistema',
            'finalized_at' => $this->ticket->resolved_at,
            'message' => 'Seu chamado #' . $this->ticket->id . ' (' . $this->ticket->title . ') foi finalizado pela área ' . ($this->ticket->area->name ?? 'N/A') . '.',
            'action_text' => 'Avaliar atendimento',
            'url' => route('tickets.evaluate', $this->ticket),
            'priority' => 'low',
            'category' => 'ticket_finalized',
        ];
    }
}