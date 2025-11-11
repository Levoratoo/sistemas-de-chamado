<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupNotificationCron extends Command
{
    protected $signature = 'setup:notification-cron';
    protected $description = 'Configurar cron jobs para notificações automáticas';

    public function handle(): int
    {
        $this->info('⏰ Configurando Cron Jobs para Notificações Automáticas');
        $this->line('');

        $projectPath = base_path();
        
        $this->info('📋 Comandos para adicionar ao crontab:');
        $this->line('');
        
        $this->line('# Sistema de Chamados - Notificações Automáticas');
        $this->line('# Adicione estas linhas ao crontab (crontab -e):');
        $this->line('');
        
        // Resumo diário às 8h da manhã
        $this->line('# Resumo diário - 8h da manhã');
        $this->line("0 8 * * * cd {$projectPath} && php artisan notifications:daily-digest >> /dev/null 2>&1");
        $this->line('');
        
        // Alertas urgentes a cada 30 minutos
        $this->line('# Alertas urgentes - A cada 30 minutos');
        $this->line("*/30 * * * * cd {$projectPath} && php artisan notifications:urgent-alerts >> /dev/null 2>&1");
        $this->line('');
        
        // SLA warnings a cada hora
        $this->line('# Alertas SLA - A cada hora');
        $this->line("0 * * * * cd {$projectPath} && php artisan notifications:sla-warnings >> /dev/null 2>&1");
        $this->line('');
        
        $this->info('🔧 Como configurar:');
        $this->line('1. Abra o terminal');
        $this->line('2. Execute: crontab -e');
        $this->line('3. Adicione as linhas acima');
        $this->line('4. Salve e saia (Ctrl+X, Y, Enter)');
        $this->line('');
        
        $this->info('📊 Frequência das Notificações:');
        $this->line('• Resumo Diário: 8h da manhã');
        $this->line('• Alertas Urgentes: A cada 30 minutos');
        $this->line('• Alertas SLA: A cada hora');
        $this->line('');
        
        $this->info('🧪 Para testar:');
        $this->line('• Resumo diário: php artisan notifications:daily-digest --test');
        $this->line('• Alertas urgentes: php artisan notifications:urgent-alerts --test');
        $this->line('');
        
        $this->info('✅ Configuração concluída!');
        $this->line('');
        $this->warn('⚠️  IMPORTANTE: Certifique-se de que o SMTP está configurado corretamente.');
        $this->line('   Execute: php artisan configure:smtp');

        return 0;
    }
}