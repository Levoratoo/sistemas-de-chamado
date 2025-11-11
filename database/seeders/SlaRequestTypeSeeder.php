<?php

namespace Database\Seeders;

use App\Models\SlaRequestType;
use Illuminate\Database\Seeder;

class SlaRequestTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slaConfigurations = [
            // REEMBOLSO - Financeiro (Base: 7 dias)
            [
                'request_type' => 'reembolso',
                'priority' => 'low',
                'response_time_minutes' => 1440, // 1 dia
                'resolve_time_minutes' => 5040, // 7 dias úteis (5 dias)
                'description' => 'SLA para solicitações de reembolso - Baixa prioridade (7 dias)',
            ],
            [
                'request_type' => 'reembolso',
                'priority' => 'medium',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis (3.5 dias)
                'description' => 'SLA para solicitações de reembolso - Média prioridade (5 dias)',
            ],
            [
                'request_type' => 'reembolso',
                'priority' => 'high',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis (2.5 dias)
                'description' => 'SLA para solicitações de reembolso - Alta prioridade (3 dias)',
            ],
            [
                'request_type' => 'reembolso',
                'priority' => 'critical',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para solicitações de reembolso - Crítica (1 dia)',
            ],

            // ADIANTAMENTO - Financeiro (Base: 7 dias, mas mais rápido)
            [
                'request_type' => 'adiantamento',
                'priority' => 'low',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis
                'description' => 'SLA para solicitações de adiantamento - Baixa prioridade (5 dias)',
            ],
            [
                'request_type' => 'adiantamento',
                'priority' => 'medium',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis
                'description' => 'SLA para solicitações de adiantamento - Média prioridade (3 dias)',
            ],
            [
                'request_type' => 'adiantamento',
                'priority' => 'high',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para solicitações de adiantamento - Alta prioridade (1 dia)',
            ],
            [
                'request_type' => 'adiantamento',
                'priority' => 'critical',
                'response_time_minutes' => 60, // 1 hora
                'resolve_time_minutes' => 480, // 8 horas
                'description' => 'SLA para solicitações de adiantamento - Crítica (8 horas)',
            ],

            // PAGAMENTO GERAL - Financeiro (Base: 7 dias)
            [
                'request_type' => 'pagamento_geral',
                'priority' => 'low',
                'response_time_minutes' => 1440, // 1 dia
                'resolve_time_minutes' => 5040, // 7 dias úteis
                'description' => 'SLA para solicitações de pagamento geral - Baixa prioridade (7 dias)',
            ],
            [
                'request_type' => 'pagamento_geral',
                'priority' => 'medium',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis
                'description' => 'SLA para solicitações de pagamento geral - Média prioridade (5 dias)',
            ],
            [
                'request_type' => 'pagamento_geral',
                'priority' => 'high',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis
                'description' => 'SLA para solicitações de pagamento geral - Alta prioridade (3 dias)',
            ],
            [
                'request_type' => 'pagamento_geral',
                'priority' => 'critical',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para solicitações de pagamento geral - Crítica (1 dia)',
            ],

            // RH - Recursos Humanos (Base: 7 dias)
            [
                'request_type' => 'rh',
                'priority' => 'low',
                'response_time_minutes' => 1440, // 1 dia
                'resolve_time_minutes' => 5040, // 7 dias úteis
                'description' => 'SLA para solicitações de RH - Baixa prioridade (7 dias)',
            ],
            [
                'request_type' => 'rh',
                'priority' => 'medium',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis
                'description' => 'SLA para solicitações de RH - Média prioridade (5 dias)',
            ],
            [
                'request_type' => 'rh',
                'priority' => 'high',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis
                'description' => 'SLA para solicitações de RH - Alta prioridade (3 dias)',
            ],
            [
                'request_type' => 'rh',
                'priority' => 'critical',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para solicitações de RH - Crítica (1 dia)',
            ],

            // CONTABILIDADE (Base: 7 dias)
            [
                'request_type' => 'contabilidade',
                'priority' => 'low',
                'response_time_minutes' => 1440, // 1 dia
                'resolve_time_minutes' => 5040, // 7 dias úteis
                'description' => 'SLA para solicitações de contabilidade - Baixa prioridade (7 dias)',
            ],
            [
                'request_type' => 'contabilidade',
                'priority' => 'medium',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis
                'description' => 'SLA para solicitações de contabilidade - Média prioridade (5 dias)',
            ],
            [
                'request_type' => 'contabilidade',
                'priority' => 'high',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis
                'description' => 'SLA para solicitações de contabilidade - Alta prioridade (3 dias)',
            ],
            [
                'request_type' => 'contabilidade',
                'priority' => 'critical',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para solicitações de contabilidade - Crítica (1 dia)',
            ],

            // GERAL - Tickets sem tipo específico (Base: 7 dias)
            [
                'request_type' => 'geral',
                'priority' => 'low',
                'response_time_minutes' => 1440, // 1 dia
                'resolve_time_minutes' => 5040, // 7 dias úteis
                'description' => 'SLA para tickets gerais - Baixa prioridade (7 dias)',
            ],
            [
                'request_type' => 'geral',
                'priority' => 'medium',
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis
                'description' => 'SLA para tickets gerais - Média prioridade (5 dias)',
            ],
            [
                'request_type' => 'geral',
                'priority' => 'high',
                'response_time_minutes' => 360, // 6 horas
                'resolve_time_minutes' => 2160, // 3 dias úteis
                'description' => 'SLA para tickets gerais - Alta prioridade (3 dias)',
            ],
            [
                'request_type' => 'geral',
                'priority' => 'critical',
                'response_time_minutes' => 180, // 3 horas
                'resolve_time_minutes' => 1440, // 1 dia útil
                'description' => 'SLA para tickets gerais - Crítica (1 dia)',
            ],
        ];

        foreach ($slaConfigurations as $config) {
            SlaRequestType::firstOrCreate(
                [
                    'request_type' => $config['request_type'],
                    'priority' => $config['priority'],
                ],
                $config
            );
        }

        // Criar SLA padrão (Base: 7 dias)
        SlaRequestType::firstOrCreate(
            ['request_type' => 'default', 'priority' => 'medium'],
            [
                'response_time_minutes' => 720, // 12 horas
                'resolve_time_minutes' => 3600, // 5 dias úteis (base 7 dias)
                'active' => true,
                'description' => 'SLA padrão para tickets sem configuração específica (5 dias úteis)',
            ]
        );
    }
}