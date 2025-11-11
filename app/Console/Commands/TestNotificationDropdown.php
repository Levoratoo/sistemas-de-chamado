<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Ticket;
use App\Notifications\TicketFinalizedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\SlaWarningNotification;
use Illuminate\Console\Command;

class TestNotificationDropdown extends Command
{
    protected $signature = 'test:notification-dropdown {--count=5 : Número de notificações para criar}';
    protected $description = 'Criar notificações de teste para o dropdown';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        
        $this->info("🔔 Criando {$count} notificações de teste...");
        
        try {
            // Buscar usuário para teste
            $user = User::first();
            
            if (!$user) {
                $this->error('❌ Nenhum usuário encontrado no sistema.');
                return 1;
            }
            
            // Buscar um ticket para teste
            $ticket = Ticket::with(['area', 'requester'])->first();
            
            if (!$ticket) {
                $this->error('❌ Nenhum ticket encontrado. Crie um ticket primeiro.');
                return 1;
            }
            
            $this->info("👤 Usuário: {$user->name} ({$user->email})");
            $this->info("🎫 Ticket: #{$ticket->id} - {$ticket->title}");
            $this->line('');
            
            // Criar diferentes tipos de notificações
            for ($i = 1; $i <= $count; $i++) {
                switch ($i % 3) {
                    case 1:
                        $user->notify(new TicketFinalizedNotification($ticket));
                        $this->line("✅ Notificação {$i}: Ticket Finalizado");
                        break;
                    case 2:
                        $user->notify(new TicketAssignedNotification($ticket, $user));
                        $this->line("👤 Notificação {$i}: Ticket Atribuído");
                        break;
                    case 0:
                        $user->notify(new SlaWarningNotification($ticket, 'warning'));
                        $this->line("⚠️  Notificação {$i}: Alerta SLA");
                        break;
                }
                
                // Pequeno delay para timestamps diferentes
                usleep(100000); // 0.1 segundo
            }
            
            $this->line('');
            $this->info("🎉 {$count} notificações criadas com sucesso!");
            $this->line('');
            
            $this->info('📱 Para testar:');
            $this->line('   1. Acesse o sistema no navegador');
            $this->line('   2. Faça login como: ' . $user->email);
            $this->line('   3. Clique no sininho 🔔 ao lado do seu nome');
            $this->line('   4. Veja as notificações no dropdown');
            $this->line('');
            
            $this->info('🔧 Funcionalidades disponíveis:');
            $this->line('   • Contador de notificações não lidas');
            $this->line('   • Lista das últimas 10 notificações');
            $this->line('   • Marcar individual como lida');
            $this->line('   • Marcar todas como lidas');
            $this->line('   • Ícones diferentes por tipo');
            $this->line('   • Timestamps relativos');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao criar notificações: ' . $e->getMessage());
            return 1;
        }
    }
}