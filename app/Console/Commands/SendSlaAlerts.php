<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendSlaAlerts extends Command
{
    protected $signature = 'notifications:sla-alerts';
    protected $description = 'Enviar alertas SLA para tickets próximos do vencimento ou vencidos';

    public function handle(): int
    {
        $this->info('Enviando alertas SLA...');
        
        try {
            app(NotificationService::class)->sendSlaAlerts();
            $this->info('Alertas SLA enviados com sucesso!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao enviar alertas SLA: ' . $e->getMessage());
            return 1;
        }
    }
}