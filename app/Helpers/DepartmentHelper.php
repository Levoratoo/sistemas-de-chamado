<?php

namespace App\Helpers;

class DepartmentHelper
{
    /**
     * Converte slug de departamento para nome legível
     */
    public static function slugToName(string $slug): string
    {
        $departments = [
            'administracao-vendas' => 'Administração de Vendas',
            'administrativo-producao' => 'Administrativo Produção',
            'alca' => 'Alça',
            'almoxarifado' => 'Almoxarifado',
            'comercial-marketing' => 'Comercial e Marketing',
            'compras' => 'Compras',
            'controladoria' => 'Controladoria',
            'corte-vinco-automatico' => 'Corte Vinco Automático',
            'desbobinadeira' => 'Desbobinadeira',
            'desenvolvimento-tinta' => 'Desenvolvimento de Tinta',
            'destaque' => 'Destaque',
            'embalagem' => 'Embalagem',
            'engenharia' => 'Engenharia',
            'expedicao' => 'Expedição',
            'faturamento' => 'Faturamento',
            'financeiro' => 'Financeiro',
            'fiscal' => 'Fiscal',
            'gofragem' => 'Gofragem',
            'guardanapo-sachet' => 'Guardanapo Sachet',
            'guilhotina' => 'Guilhotina',
            'impressora-5-cores' => 'Impressora 5 cores',
            'impressora-flexo-feva' => 'Impressora Flexo Feva',
            'impressora-flexografica' => 'Impressora Flexográfica',
            'impressora-miraflex' => 'Impressora Miraflex',
            'limpeza-administrativa' => 'Limpeza administrativa',
            'manutencao' => 'Manutenção',
            'patch' => 'Patch',
            'pcp' => 'PCP',
            'planejamento' => 'Planejamento',
            'pre-impressao' => 'Pré-Impressão',
            'qualidade' => 'Qualidade',
            'recepcao' => 'Recepção',
            'recursos-humanos' => 'Recursos Humanos',
            'saco-fundo-quadrado' => 'Saco Fundo Quadrado',
            'sacoleira-newport' => 'Sacoleira Newport',
            'seguranca-trabalho' => 'Segurança do Trabalho',
            'tecnologia-informacao' => 'Tecnologia Informação',
            'transporte' => 'Transporte',
            'venda-varejo' => 'Venda Varejo',
            'vendas' => 'Vendas',
            'zeladoria' => 'Zeladoria',
            'weisul-administracao' => 'Weisul - Administração',
            'weisul-direcao-geral' => 'Weisul - Direção Geral',
            'weisul-comercial' => 'Weisul - Comercial',
            'weisul-agro-producao-agricola' => 'Weisul Agro - Produção Agrícola',
            'weisul-agro-manutencao' => 'Weisul Agro - Manutenção',
            'weisul-agro-administracao' => 'Weisul Agro - Administração',
            'weisul-agro-armazem-safrista-soja' => 'Weisul Agro - Armazém Safrista Soja',
            'weisul-agro-prod-agric-safrista-soja' => 'Weisul Agro - Prod. Agríc. Safrista Soja',
            'weisul-agro-refeitorio' => 'Weisul Agro - Refeitório',
            'weisul-agro-armazem' => 'Weisul Agro - Armazém',
        ];

        return $departments[$slug] ?? ucfirst(str_replace('-', ' ', $slug));
    }
}











