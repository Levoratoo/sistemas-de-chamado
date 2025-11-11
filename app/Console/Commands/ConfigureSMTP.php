<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureSMTP extends Command
{
    protected $signature = 'configure:smtp';
    protected $description = 'Configurar SMTP de forma interativa';

    public function handle(): int
    {
        $this->info('🔧 Configuração SMTP Interativa');
        $this->line('');
        
        // Verificar se .env existe
        if (!File::exists('.env')) {
            $this->error('❌ Arquivo .env não encontrado!');
            return 1;
        }
        
        $this->info('📧 Configurações atuais:');
        $this->line('   Host: smtp-mail.outlook.com');
        $this->line('   Porta: 587');
        $this->line('   Username: pedro.levorato@weisul.com.br');
        $this->line('   Encryption: TLS');
        $this->line('');
        
        $this->warn('⚠️  IMPORTANTE: Para usar Outlook/Hotmail:');
        $this->line('   1. Use sua senha normal do Outlook');
        $this->line('   2. Não precisa de senha de app');
        $this->line('   3. Certifique-se que a conta está ativa');
        $this->line('');
        
        $this->info('🔗 Links úteis:');
        $this->line('   • Outlook: https://outlook.live.com/');
        $this->line('   • Configurações: https://account.microsoft.com/');
        $this->line('');
        
        // Solicitar senha de forma segura
        $password = $this->secret('Digite sua senha do Outlook:');
        
        if (empty($password)) {
            $this->error('❌ Senha não pode estar vazia!');
            return 1;
        }
        
        // Atualizar arquivo .env
        $envContent = File::get('.env');
        $envContent = preg_replace('/MAIL_PASSWORD=.*/', 'MAIL_PASSWORD="' . $password . '"', $envContent);
        File::put('.env', $envContent);
        
        $this->info('✅ Senha configurada com sucesso!');
        $this->line('');
        
        // Testar configuração
        if ($this->confirm('Deseja testar o envio de email agora?')) {
            $this->call('test:email-notification');
        }
        
        return 0;
    }
}