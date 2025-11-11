<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\TicketFinalizedNotification;
use App\Models\Ticket;
use Illuminate\Console\Command;

class TestEmailNotification extends Command
{
    protected $signature = 'test:email-notification {email=pedro.levorato@weisul.com.br}';
    protected $description = 'Testar envio de notificação por email';

    public function handle(): int
    {
        $email = $this->argument('email');
        
        $this->info("Testando envio de email para: {$email}");
        
        try {
            // Criar um usuário temporário para teste
            $testUser = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Pedro Levorato',
                    'login' => 'pedro.levorato',
                    'password' => bcrypt('password'),
                    'role_id' => 1, // Admin
                ]
            );

            // Buscar um ticket existente ou criar um fictício
            $ticket = Ticket::with(['area', 'requester'])->first();
            
            if (!$ticket) {
                $this->error('Nenhum ticket encontrado no sistema. Crie um ticket primeiro.');
                return 1;
            }

            // Enviar notificação de teste
            $testUser->notify(new TicketFinalizedNotification($ticket));
            
            $this->info('✅ Email de teste enviado com sucesso!');
            $this->info("📧 Verifique a caixa de entrada de: {$email}");
            $this->info('📋 Se estiver usando Mailpit, acesse: http://localhost:8025');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erro ao enviar email: ' . $e->getMessage());
            return 1;
        }
    }
}