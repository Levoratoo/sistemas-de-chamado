<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleBackups extends Command
{
    protected $signature = 'backup:schedule';
    protected $description = 'Configurar cron jobs para backups automáticos';

    public function handle(): int
    {
        $this->info('⏰ Configurando Cron Jobs para Backups Automáticos');
        $this->line('');

        $projectPath = base_path();
        
        $this->info('📋 Comandos para adicionar ao crontab:');
        $this->line('');
        
        $this->line('# Sistema de Chamados - Backups Automáticos');
        $this->line('# Adicione estas linhas ao crontab (crontab -e):');
        $this->line('');
        
        // Backup diário às 2h da manhã
        $this->line('# Backup diário completo - 2h da manhã');
        $this->line("0 2 * * * cd {$projectPath} && php artisan backup:system --type=full --compress=true >> /dev/null 2>&1");
        $this->line('');
        
        // Backup semanal (domingo às 3h)
        $this->line('# Backup semanal - Domingo às 3h');
        $this->line("0 3 * * 0 cd {$projectPath} && php artisan backup:system --type=database --compress=true >> /dev/null 2>&1");
        $this->line('');
        
        // Backup mensal (primeiro dia do mês às 4h)
        $this->line('# Backup mensal - Primeiro dia do mês às 4h');
        $this->line("0 4 1 * * cd {$projectPath} && php artisan backup:system --type=full --compress=true >> /dev/null 2>&1");
        $this->line('');
        
        $this->info('🔧 Como configurar:');
        $this->line('1. Abra o terminal');
        $this->line('2. Execute: crontab -e');
        $this->line('3. Adicione as linhas acima');
        $this->line('4. Salve e saia (Ctrl+X, Y, Enter)');
        $this->line('');
        
        $this->info('📊 Frequência dos Backups:');
        $this->line('• Backup Diário: 2h da manhã (completo)');
        $this->line('• Backup Semanal: Domingo às 3h (banco de dados)');
        $this->line('• Backup Mensal: Dia 1 às 4h (completo)');
        $this->line('• Limpeza: Automática (mantém últimos 7 dias)');
        $this->line('');
        
        $this->info('🧪 Para testar:');
        $this->line('• Backup completo: php artisan backup:system --type=full');
        $this->line('• Backup banco: php artisan backup:system --type=database');
        $this->line('• Backup arquivos: php artisan backup:system --type=files');
        $this->line('');
        
        $this->info('📁 Local dos Backups:');
        $this->line('• Diretório: storage/backups/');
        $this->line('• Formato: backup_{tipo}_{data_hora}');
        $this->line('• Compressão: Automática');
        $this->line('');
        
        $this->info('✅ Configuração de backup concluída!');
        $this->line('');
        $this->warn('⚠️  IMPORTANTE: Configure também backup externo (cloud, servidor remoto)');
        $this->line('   para máxima segurança dos dados.');

        return 0;
    }
}