<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupCronJob extends Command
{
    protected $signature = 'setup:cron-job';
    protected $description = 'Configurar cron job para alertas SLA automáticos';

    public function handle(): int
    {
        $this->info('🔧 Configurando cron job para alertas SLA...');
        
        $projectPath = base_path();
        $cronCommand = "* * * * * cd {$projectPath} && php artisan notifications:sla-alerts >> /dev/null 2>&1";
        
        $this->info('📋 Adicione esta linha ao seu crontab:');
        $this->line('');
        $this->line("    {$cronCommand}");
        $this->line('');
        
        $this->info('💡 Para editar o crontab, execute:');
        $this->line('    crontab -e');
        $this->line('');
        
        $this->info('📝 Ou execute este comando para adicionar automaticamente:');
        $this->line("    echo '{$cronCommand}' | crontab -");
        $this->line('');
        
        $this->info('✅ Configuração concluída!');
        $this->info('🔄 Os alertas SLA serão enviados automaticamente a cada minuto.');
        
        return 0;
    }
}