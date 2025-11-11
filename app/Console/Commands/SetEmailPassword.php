<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetEmailPassword extends Command
{
    protected $signature = 'email:set-password {password}';
    protected $description = 'Configurar senha do email SMTP';

    public function handle(): int
    {
        $password = $this->argument('password');
        
        $this->info('🔧 Configurando senha do email SMTP...');
        
        // Ler arquivo .env
        $envPath = base_path('.env');
        $envContent = File::get($envPath);
        
        // Atualizar configurações SMTP
        $envContent = preg_replace('/MAIL_MAILER=.*/', 'MAIL_MAILER=smtp', $envContent);
        $envContent = preg_replace('/MAIL_HOST=.*/', 'MAIL_HOST=smtp.office365.com', $envContent);
        $envContent = preg_replace('/MAIL_PORT=.*/', 'MAIL_PORT=587', $envContent);
        $envContent = preg_replace('/MAIL_USERNAME=.*/', 'MAIL_USERNAME=pedro.levorato@weisul.com.br', $envContent);
        $envContent = preg_replace('/MAIL_PASSWORD=.*/', 'MAIL_PASSWORD="' . $password . '"', $envContent);
        $envContent = preg_replace('/MAIL_ENCRYPTION=.*/', 'MAIL_ENCRYPTION=tls', $envContent);
        
        // Salvar arquivo .env
        File::put($envPath, $envContent);
        
        $this->info('✅ Configuração SMTP atualizada:');
        $this->line('   • Host: smtp.office365.com');
        $this->line('   • Porta: 587');
        $this->line('   • Username: pedro.levorato@weisul.com.br');
        $this->line('   • Encryption: TLS');
        $this->line('   • Senha: [CONFIGURADA]');
        
        // Limpar cache de configuração
        $this->call('config:clear');
        
        $this->info('🧪 Testando envio de email...');
        
        try {
            // Enviar email de teste
            \Mail::raw('Teste de configuração SMTP - Sistema de Chamados', function ($message) {
                $message->to('pedro.levorato@weisul.com.br', 'Pedro Levorato')
                        ->subject('✅ SMTP Configurado - Sistema de Chamados');
            });
            
            $this->info('✅ Email de teste enviado com sucesso!');
            $this->line('📧 Verifique a caixa de entrada de: pedro.levorato@weisul.com.br');
            
        } catch (\Exception $e) {
            $this->error('❌ Erro ao enviar email: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}