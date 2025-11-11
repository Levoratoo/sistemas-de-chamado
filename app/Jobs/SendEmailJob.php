<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Ticket;
use App\Notifications\TicketFinalizedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\SlaWarningNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $backoff = [30, 60, 120]; // Retry após 30s, 60s, 120s

    protected $user;
    protected $notification;
    protected $type;

    public function __construct(User $user, $notification, string $type)
    {
        $this->user = $user;
        $this->notification = $notification;
        $this->type = $type;
        
        // Configurar prioridade baseada no tipo
        $this->onQueue($this->getQueueName());
    }

    public function handle(): void
    {
        try {
            Log::info("Enviando email {$this->type} para {$this->user->email}");
            
            $this->user->notify($this->notification);
            
            Log::info("Email {$this->type} enviado com sucesso para {$this->user->email}");
            
        } catch (\Exception $e) {
            Log::error("Erro ao enviar email {$this->type} para {$this->user->email}: " . $e->getMessage());
            throw $e; // Re-throw para tentar novamente
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Falha definitiva ao enviar email {$this->type} para {$this->user->email}: " . $exception->getMessage());
    }

    private function getQueueName(): string
    {
        return match($this->type) {
            'sla_warning' => 'high-priority',
            'ticket_assigned' => 'medium-priority',
            'ticket_finalized' => 'low-priority',
            default => 'default'
        };
    }
}