<?php

namespace App\Console\Commands;

use App\Services\SlaCalculationService;
use Illuminate\Console\Command;

class TestSlaCalculation extends Command
{
    protected $signature = 'test:sla-calculation';
    protected $description = 'Testar cálculo de SLA para diferentes tipos de chamado';

    public function handle(SlaCalculationService $slaService): int
    {
        $this->info('🧪 Testando Sistema de SLA Real');
        $this->line('');

        $requestTypes = [
            'reembolso' => 'Solicitação de Reembolso',
            'adiantamento' => 'Solicitação de Adiantamento',
            'pagamento_geral' => 'Solicitação de Pagamento Geral',
            'rh' => 'Solicitação de RH',
            'contabilidade' => 'Solicitação de Contabilidade',
            'geral' => 'Ticket Geral',
        ];

        $priorities = ['low', 'medium', 'high', 'critical'];

        foreach ($requestTypes as $requestType => $description) {
            $this->info("📋 {$description} ({$requestType})");
            $this->line('');

            foreach ($priorities as $priority) {
                $slaData = $slaService->calculateSlaForTicket($requestType, $priority);
                
                $this->line("  {$priority}:");
                $this->line("    • Fonte: {$slaData['source']}");
                $this->line("    • Resposta: {$slaData['sla']->formatted_response_time}");
                $this->line("    • Resolução: {$slaData['sla']->formatted_resolve_time}");
                $this->line("    • Vence em: {$slaData['due_at']->format('d/m/Y H:i')}");
                $this->line('');
            }
            
            $this->line('─' . str_repeat('─', 50));
            $this->line('');
        }

        // Testar estatísticas
        $this->info('📊 Estatísticas de SLA:');
        $stats = $slaService->getSlaStatistics();
        $this->line("  • Total de tickets: {$stats['total_tickets']}");
        $this->line("  • Dentro do SLA: {$stats['on_time_tickets']}");
        $this->line("  • Próximos do vencimento: {$stats['near_due_tickets']}");
        $this->line("  • Vencidos: {$stats['overdue_tickets']}");
        $this->line("  • Taxa de compliance: {$stats['sla_compliance_rate']}%");
        $this->line('');

        $this->info('✅ Teste de SLA concluído com sucesso!');
        $this->line('');
        $this->info('🎯 Próximos passos:');
        $this->line('  1. Testar criação de tickets com SLA real');
        $this->line('  2. Verificar alertas SLA automáticos');
        $this->line('  3. Implementar rate limiting avançado');

        return 0;
    }
}