<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Ticket;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class TestQueueSystem extends Command
{
    protected $signature = 'test:queue-system {--count=10 : Número de emails para testar}';
    protected $description = 'Testar sistema de filas com múltiplos emails';

    public function handle(NotificationService $notificationService): int
    {
        $count = (int) $this->option('count');
        
        $this->info("🧪 Testando sistema de filas com {$count} emails...");
        
        try {
            // Buscar usuários para teste
            $users = User::limit($count)->get();
            
            if ($users->isEmpty()) {
                $this->error('❌ Nenhum usuário encontrado no sistema.');
                return 1;
            }
            
            $this->info("📧 Enviando emails para {$users->count()} usuários...");
            
            // Buscar um ticket para teste
            $ticket = Ticket::with(['area', 'requester'])->first();
            
            if (!$ticket) {
                $this->error('❌ Nenhum ticket encontrado. Crie um ticket primeiro.');
                return 1;
            }
            
            // Testar envio em lote
            $startTime = microtime(true);
            
            foreach ($users as $user) {
                $notificationService->notifyTicketFinalized($ticket);
            }
            
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            $this->info("✅ {$users->count()} emails adicionados à fila em {$duration}ms");
            $this->line('');
            
            $this->info('📊 Status da fila:');
            $this->line('   • Jobs pendentes: ' . $this->getPendingJobsCount());
            $this->line('   • Jobs processados: ' . $this->getProcessedJobsCount());
            $this->line('   • Jobs falhados: ' . $this->getFailedJobsCount());
            $this->line('');
            
            $this->info('🚀 Para processar a fila, execute:');
            $this->line('   php artisan queue:work');
            $this->line('');
            
            $this->info('📈 Para monitorar a fila:');
            $this->line('   php artisan queue:monitor');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Erro no teste: ' . $e->getMessage());
            return 1;
        }
    }
    
    private function getPendingJobsCount(): int
    {
        return \DB::table('jobs')->count();
    }
    
    private function getProcessedJobsCount(): int
    {
        return \DB::table('jobs')->where('reserved_at', '>', 0)->count();
    }
    
    private function getFailedJobsCount(): int
    {
        return \DB::table('failed_jobs')->count();
    }
}