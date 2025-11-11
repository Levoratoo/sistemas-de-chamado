<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ConfigureSendGrid extends Command
{
    protected $signature = 'configure:sendgrid';
    protected $description = 'Configurar SendGrid para envio profissional de emails';

    public function handle(): int
    {
        $this->info('🔧 Configuração SendGrid para 500+ usuários');
        $this->line('');
        
        $this->info('📊 Vantagens do SendGrid:');
        $this->line('   • ✅ 100 emails/dia GRÁTIS');
        $this->line('   • ✅ 99%+ taxa de entrega');
        $this->line('   • ✅ Sem bloqueios por spam');
        $this->line('   • ✅ Analytics detalhados');
        $this->line('   • ✅ Templates profissionais');
        $this->line('');
        
        $this->warn('💰 Preços SendGrid:');
        $this->line('   • Gratuito: 100 emails/dia');
        $this->line('   • Essentials: $15/mês (40.000 emails)');
        $this->line('   • Pro: $90/mês (100.000 emails)');
        $this->line('');
        
        $this->info('🔗 Links importantes:');
        $this->line('   • Cadastro: https://sendgrid.com/');
        $this->line('   • API Key: https://app.sendgrid.com/settings/api_keys');
        $this->line('   • Documentação: https://docs.sendgrid.com/');
        $this->line('');
        
        if ($this->confirm('Deseja configurar SendGrid agora?')) {
            $apiKey = $this->secret('Digite sua API Key do SendGrid:');
            
            if (empty($apiKey)) {
                $this->error('❌ API Key não pode estar vazia!');
                return 1;
            }
            
            // Atualizar configurações
            $this->updateMailConfig($apiKey);
            
            $this->info('✅ SendGrid configurado com sucesso!');
            $this->line('');
            
            // Instalar pacote SendGrid
            if ($this->confirm('Deseja instalar o pacote SendGrid?')) {
                $this->call('composer', ['require', 'sendgrid/sendgrid']);
            }
            
            // Testar configuração
            if ($this->confirm('Deseja testar o envio agora?')) {
                $this->call('test:email-notification');
            }
        }
        
        return 0;
    }
    
    private function updateMailConfig(string $apiKey): void
    {
        $envContent = File::get('.env');
        
        // Atualizar configurações para SendGrid
        $envContent = preg_replace('/MAIL_MAILER=.*/', 'MAIL_MAILER=smtp', $envContent);
        $envContent = preg_replace('/MAIL_HOST=.*/', 'MAIL_HOST=smtp.sendgrid.net', $envContent);
        $envContent = preg_replace('/MAIL_PORT=.*/', 'MAIL_PORT=587', $envContent);
        $envContent = preg_replace('/MAIL_USERNAME=.*/', 'MAIL_USERNAME=apikey', $envContent);
        $envContent = preg_replace('/MAIL_PASSWORD=.*/', 'MAIL_PASSWORD="' . $apiKey . '"', $envContent);
        $envContent = preg_replace('/MAIL_ENCRYPTION=.*/', 'MAIL_ENCRYPTION=tls', $envContent);
        
        File::put('.env', $envContent);
        
        $this->info('📧 Configurações atualizadas:');
        $this->line('   • Host: smtp.sendgrid.net');
        $this->line('   • Porta: 587');
        $this->line('   • Username: apikey');
        $this->line('   • Password: [API Key configurada]');
        $this->line('   • Encryption: TLS');
    }
}