<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class SecurityDashboard extends Command
{
    protected $signature = 'security:dashboard';
    protected $description = 'Mostrar dashboard de monitoramento de segurança';

    public function handle(): int
    {
        $this->info('🛡️ Dashboard de Segurança - Sistema de Chamados');
        $this->line('');

        // Estatísticas de Rate Limiting
        $this->info('📊 Estatísticas de Rate Limiting:');
        $this->line('');

        // Verificar rate limits ativos
        $rateLimitKeys = [
            'rate_limit_ip:login',
            'rate_limit_user:tickets.store',
            'rate_limit_user:tickets.comment',
            'rate_limit_user:tickets.assign',
        ];

        foreach ($rateLimitKeys as $key) {
            $attempts = RateLimiter::attempts($key);
            $remaining = RateLimiter::remaining($key, 10);
            $availableIn = RateLimiter::availableIn($key);
            
            if ($attempts > 0) {
                $this->line("   🔴 {$key}: {$attempts} tentativas, {$remaining} restantes");
                if ($availableIn > 0) {
                    $this->line("      ⏰ Reset em {$availableIn} segundos");
                }
            } else {
                $this->line("   🟢 {$key}: Sem tentativas recentes");
            }
        }

        $this->line('');

        // Verificar logs de segurança
        $this->info('📋 Logs de Segurança (Últimas 24h):');
        
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            
            // Contar diferentes tipos de logs de segurança
            $rateLimitLogs = substr_count($logs, 'Rate limit exceeded');
            $spamLogs = substr_count($logs, 'Spam detected and blocked');
            $userRateLimitLogs = substr_count($logs, 'User rate limit exceeded');
            
            $this->line("   🚫 Rate limits por IP: {$rateLimitLogs}");
            $this->line("   🚫 Rate limits por usuário: {$userRateLimitLogs}");
            $this->line("   🚫 Spam bloqueado: {$spamLogs}");
        } else {
            $this->line('   ⚠️  Arquivo de log não encontrado');
        }

        $this->line('');

        // Verificar cache de spam
        $this->info('🛡️ Proteção contra Spam:');
        $spamDelayKeys = Cache::get('spam_delay:*');
        if ($spamDelayKeys) {
            $this->line('   🔴 IPs com delay ativo: ' . count($spamDelayKeys));
        } else {
            $this->line('   🟢 Nenhum IP com delay ativo');
        }

        $this->line('');

        // Configurações ativas
        $this->info('⚙️ Configurações de Segurança Ativas:');
        $this->line('   🔐 Login: 5 tentativas por 15 minutos');
        $this->line('   📝 Tickets: 10 por hora por usuário');
        $this->line('   💬 Comentários: 50 por 15 minutos por usuário');
        $this->line('   📎 Anexos: 20 por 30 minutos por usuário');
        $this->line('   🚫 Spam: Score >= 3 bloqueia');
        $this->line('   ⏰ Delay spam: 5 segundos para score >= 1');
        $this->line('');

        // Recomendações
        $this->info('💡 Recomendações:');
        
        if ($rateLimitLogs > 10) {
            $this->line('   ⚠️  Muitos rate limits detectados - considere ajustar limites');
        }
        
        if ($spamLogs > 5) {
            $this->line('   ⚠️  Muito spam detectado - considere reforçar proteção');
        }
        
        if ($rateLimitLogs === 0 && $spamLogs === 0) {
            $this->line('   ✅ Sistema funcionando normalmente');
        }

        $this->line('');

        // Comandos úteis
        $this->info('🔧 Comandos Úteis:');
        $this->line('   • Testar rate limiting: php artisan test:rate-limiting');
        $this->line('   • Limpar cache: php artisan cache:clear');
        $this->line('   • Ver logs: tail -f storage/logs/laravel.log');
        $this->line('   • Monitorar em tempo real: php artisan security:dashboard');
        $this->line('');

        $this->info('🎯 Sistema de Segurança Monitorado!');
        
        return 0;
    }
}