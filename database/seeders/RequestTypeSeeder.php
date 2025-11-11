<?php

namespace Database\Seeders;

use App\Models\RequestArea;
use App\Models\RequestType;
use Illuminate\Database\Seeder;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Área Financeiro
        $financeiro = RequestArea::where('slug', 'financeiro')->first();
        if ($financeiro) {
            $financeiroTypes = [
                [
                    'name' => 'Solicitação de Reembolso',
                    'slug' => 'reembolso',
                    'description' => 'Solicitar reembolso para o Financeiro',
                    'icon' => 'currency-dollar',
                    'color' => '#8B5CF6', // Roxo
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Solicitação de Adiantamento',
                    'slug' => 'adiantamento',
                    'description' => 'Solicitação de adiantamento para o Financeiro',
                    'icon' => 'calendar-days',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Solicitações de Pagamento Geral',
                    'slug' => 'pagamento-geral',
                    'description' => 'Envio de Recibos/B Boletos para o Financeiro',
                    'icon' => 'envelope',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Devolução de Clientes',
                    'slug' => 'devolucao-clientes',
                    'description' => 'Devolução de Clientes',
                    'icon' => 'user',
                    'color' => '#10B981', // Verde
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Solicitação de Pagamento de Importações',
                    'slug' => 'pagamento-importacoes',
                    'description' => 'Solicitação de Pagamento de Importações',
                    'icon' => 'document-text',
                    'color' => '#3B82F6', // Azul
                    'sort_order' => 5,
                ],
                [
                    'name' => 'RH',
                    'slug' => 'rh',
                    'description' => 'Solicitações do RH para o Financeiro',
                    'icon' => 'user-group',
                    'color' => '#3B82F6', // Azul
                    'sort_order' => 6,
                ],
                [
                    'name' => 'Contabilidade',
                    'slug' => 'contabilidade',
                    'description' => 'Solicitações da Contabilidade para o Financeiro',
                    'icon' => 'chart-bar',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 7,
                ],
            ];

            foreach ($financeiroTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $financeiro->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $financeiro->id])
                );
            }
        }

        // Área TI
        $ti = RequestArea::where('slug', 'ti')->first();
        if ($ti) {
            $tiTypes = [
                [
                    'name' => 'Equipamentos e Periféricos',
                    'slug' => 'equipamentos-perifericos',
                    'description' => 'Se você está tendo problemas com o computador, avise-nos aqui.',
                    'icon' => 'computer-desktop',
                    'color' => '#3B82F6', // Azul
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Sistemas e Programas',
                    'slug' => 'sistemas-programas',
                    'description' => 'Obtenha auxílio referente a problemas, atualizações e instalações de softwares ou dos sistemas da empresa.',
                    'icon' => 'chat-bubble-left-right',
                    'color' => '#10B981', // Verde
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Internet e Comunicação',
                    'slug' => 'internet-comunicacao',
                    'description' => 'Caso você necessite de auxílio com sua navegação na internet ou esteja com dificuldades na área de telefonia.',
                    'icon' => 'phone',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Liberação de Acessos',
                    'slug' => 'liberacao-acessos',
                    'description' => 'Se você precisa de acesso a outros sistemas ou pastas na rede.',
                    'icon' => 'lock-closed',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Novo Colaborador - Contratação ou Realocação',
                    'slug' => 'novo-colaborador',
                    'description' => 'Solicitar o acesso de colaboradores novos ou realocados.',
                    'icon' => 'user-plus',
                    'color' => '#10B981', // Verde
                    'sort_order' => 5,
                ],
                [
                    'name' => 'Substituição, Aquisição ou Adequação (Softwares/Equipamentos)',
                    'slug' => 'substituicao-aquisicao',
                    'description' => 'Necessidades de apoio técnico para projetos que envolvam recursos de TI, compras, softwares, implantações e mudanças.',
                    'icon' => 'arrow-path',
                    'color' => '#059669', // Verde escuro
                    'sort_order' => 6,
                ],
            ];

            foreach ($tiTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $ti->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $ti->id])
                );
            }
        }

        // Área Compras
        $compras = RequestArea::where('slug', 'compras')->first();
        if ($compras) {
            // Delete old Compras types
            RequestType::where('request_area_id', $compras->id)->delete();

            $comprasTypes = [
                [
                    'name' => 'Solicitação de compra',
                    'slug' => 'solicitacao-compra',
                    'description' => 'Solicitar compra',
                    'icon' => 'shopping-cart',
                    'color' => '#8B5CF6', // Roxo
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Solicitação de amostra',
                    'slug' => 'solicitacao-amostra',
                    'description' => 'Solicitar amostra',
                    'icon' => 'document-text',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Solicitação de cadastro - Item',
                    'slug' => 'cadastro-item',
                    'description' => 'Solicitação de cadastro de item',
                    'icon' => 'plus-circle',
                    'color' => '#10B981', // Verde
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Solicitação de cadastro - Fornecedor/Transportadora',
                    'slug' => 'cadastro-fornecedor',
                    'description' => 'Solicitar cadastro de fornecedor / transportadora',
                    'icon' => 'user-plus',
                    'color' => '#F59E0B', // Amarelo/Laranja
                    'sort_order' => 4,
                ],
            ];

            foreach ($comprasTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $compras->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $compras->id])
                );
            }
        }

        // Área Gente e Gestão
        $genteGestao = RequestArea::where('slug', 'gente-gestao')->first();
        if ($genteGestao) {
            // Delete old Gente e Gestão types
            RequestType::where('request_area_id', $genteGestao->id)->delete();

            $genteGestaoTypes = [
                [
                    'name' => 'Abertura de vaga',
                    'slug' => 'abertura-vaga',
                    'description' => 'Solicitar a abertura de uma vaga para início do processo de recrutamento e seleção de pessoas.',
                    'icon' => 'user-plus',
                    'color' => '#F59E0B', // Laranja
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Movimentação de pessoal',
                    'slug' => 'movimentacao-pessoal',
                    'description' => 'Solicitar a movimentação de um colaborador para alteração de departamento, cargo, escala, função e/ou salário.',
                    'icon' => 'user-group',
                    'color' => '#10B981', // Verde
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Solicitação de Desligamento',
                    'slug' => 'solicitacao-desligamento',
                    'description' => 'Solicitar desligamento de um colaborador.',
                    'icon' => 'user-minus',
                    'color' => '#3B82F6', // Azul
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Solicitação de Férias',
                    'slug' => 'solicitacao-ferias',
                    'description' => 'Solicitar férias para o colaborador no período aquisitivo.',
                    'icon' => 'home',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Medidas disciplinares',
                    'slug' => 'medidas-disciplinares',
                    'description' => 'Solicitar advertência e/ou suspensão para colaborador.',
                    'icon' => 'envelope',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 5,
                ],
                [
                    'name' => 'Benefícios',
                    'slug' => 'beneficios',
                    'description' => 'Solicitar alteração de benefícios.',
                    'icon' => 'currency-dollar',
                    'color' => '#8B5CF6', // Roxo
                    'sort_order' => 6,
                ],
                [
                    'name' => 'Solicitação de treinamento',
                    'slug' => 'solicitacao-treinamento',
                    'description' => 'Solicitar o treinamento de um colaborador dentro da matriz de competências.',
                    'icon' => 'document-text',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 7,
                ],
                [
                    'name' => 'Solicitação de Comunicados',
                    'slug' => 'solicitacao-comunicados',
                    'description' => 'Solicitar comunicados internos para divulgação aos colaboradores.',
                    'icon' => 'device-phone-mobile',
                    'color' => '#10B981', // Verde
                    'sort_order' => 8,
                ],
                [
                    'name' => 'Solicitação Hora Extra',
                    'slug' => 'solicitacao-hora-extra',
                    'description' => 'Solicitar horas extras para colaborador.',
                    'icon' => 'clock',
                    'color' => '#F59E0B', // Laranja
                    'sort_order' => 9,
                ],
                [
                    'name' => 'Lançamentos da folha',
                    'slug' => 'lancamentos-folha',
                    'description' => 'Lançamentos da folha',
                    'icon' => 'envelope',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 10,
                ],
                [
                    'name' => 'Atestados e declarações médicas',
                    'slug' => 'atestados-declaracoes-medicas',
                    'description' => 'Envio de atestados e declarações médicas.',
                    'icon' => 'plus-circle',
                    'color' => '#10B981', // Verde
                    'sort_order' => 11,
                ],
            ];

            foreach ($genteGestaoTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $genteGestao->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $genteGestao->id])
                );
            }
        }

        // Área RR - Registro de Reclamações
        $rr = RequestArea::where('slug', 'registro-reclamacoes')->first();
        if ($rr) {
            // Delete old RR types
            RequestType::where('request_area_id', $rr->id)->delete();

            $rrTypes = [
                [
                    'name' => 'RRL - Abertura de Registro de Reclamação p/ Logística',
                    'slug' => 'rrl-reclamacao-logistica',
                    'description' => 'Utilize para abrir chamados de reclamações de clientes referente a logística',
                    'icon' => 'bolt',
                    'color' => '#8B5CF6', // Roxo
                    'sort_order' => 1,
                ],
                [
                    'name' => 'RRI - Abertura de Registro de Reclamação Interna',
                    'slug' => 'rri-reclamacao-interna',
                    'description' => 'Utilize para abrir chamados de reclamações Internas',
                    'icon' => 'home',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 2,
                ],
                [
                    'name' => 'RRQ - Abertura de Registro de Reclamação de Qualidade',
                    'slug' => 'rrq-reclamacao-qualidade',
                    'description' => 'Utilize para abrir chamados de reclamações de clientes referente Qualidade',
                    'icon' => 'currency-dollar',
                    'color' => '#8B5CF6', // Roxo
                    'sort_order' => 3,
                ],
            ];

            foreach ($rrTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $rr->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $rr->id])
                );
            }
        }

        // Área Pré Impressão
        $preImpressao = RequestArea::where('slug', 'pre-impressao')->first();
        if ($preImpressao) {
            // Delete old Pré Impressão types
            RequestType::where('request_area_id', $preImpressao->id)->delete();

            $preImpressaoTypes = [
                [
                    'name' => 'Gabarito',
                    'slug' => 'gabarito',
                    'description' => 'Arquivo utilizado para posicionar a arte desejada pelo cliente',
                    'icon' => 'document-text',
                    'color' => '#EF4444', // Vermelho
                    'sort_order' => 1,
                ],
                [
                    'name' => 'Layout',
                    'slug' => 'layout',
                    'description' => 'Arquivo em formato .jpg, utilizado para aprovação de arte pelo cliente',
                    'icon' => 'document-text',
                    'color' => '#3B82F6', // Azul
                    'sort_order' => 2,
                ],
                [
                    'name' => 'Mock up',
                    'slug' => 'mockup',
                    'description' => 'Solicitações de Mock up para pré-impressão',
                    'icon' => 'document-text',
                    'color' => '#10B981', // Verde
                    'sort_order' => 3,
                ],
                [
                    'name' => 'Mock up Impresso',
                    'slug' => 'mockup-impresso',
                    'description' => 'Solicitações de Mock up impresso para pré-impressão',
                    'icon' => 'document-text',
                    'color' => '#F59E0B', // Amarelo
                    'sort_order' => 4,
                ],
                [
                    'name' => 'Puxada de Cor',
                    'slug' => 'puxada-cor',
                    'description' => 'Solicitações de Puxada de Cor',
                    'icon' => 'document-text',
                    'color' => '#EF4444',
                    'sort_order' => 5,
                ],
                [
                    'name' => '3D/Site',
                    'slug' => '3d-site',
                    'description' => 'Solicitações de 3D/Site',
                    'icon' => 'document-text',
                    'color' => '#10B981',
                    'sort_order' => 6,
                ],
                [
                    'name' => 'Prova Contratual',
                    'slug' => 'prova-contratual',
                    'description' => 'Solicitações de Prova Contratual',
                    'icon' => 'document-text',
                    'color' => '#3B82F6',
                    'sort_order' => 7,
                ],
                [
                    'name' => 'Impressão',
                    'slug' => 'impressao',
                    'description' => 'Solicitações de impressão',
                    'icon' => 'document-text',
                    'color' => '#111827',
                    'sort_order' => 8,
                ],
                [
                    'name' => 'Desenvolvimento de Produto',
                    'slug' => 'desenvol-produto',
                    'description' => 'Solicitações de Desenvolvimento de Produto',
                    'icon' => 'document-text',
                    'color' => '#6366F1',
                    'sort_order' => 8,
                ],
            ];

            foreach ($preImpressaoTypes as $type) {
                RequestType::firstOrCreate(
                    [
                        'request_area_id' => $preImpressao->id,
                        'slug' => $type['slug'],
                    ],
                    array_merge($type, ['request_area_id' => $preImpressao->id])
                );
            }
        }

        // Área Geral (mantém o tipo geral)
        $geral = RequestArea::where('slug', 'geral')->first();
        if ($geral) {
            RequestType::firstOrCreate(
                [
                    'request_area_id' => $geral->id,
                    'slug' => 'geral',
                ],
                [
                    'name' => 'Geral',
                    'slug' => 'geral',
                    'description' => 'Abrir um chamado geral',
                    'icon' => 'document-text',
                    'color' => '#6B7280',
                    'sort_order' => 1,
                    'request_area_id' => $geral->id,
                ]
            );
        }
    }
}