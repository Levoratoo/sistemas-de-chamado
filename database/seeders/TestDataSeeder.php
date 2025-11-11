<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Category;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\SlaCalculationService;
use App\Services\TicketEventService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slaService = app(SlaCalculationService::class);

        // 1. Criar usuários de teste para cada perfil
        $roles = Role::all()->keyBy('name');
        $areas = Area::where('active', true)->get();
        
        // Criar usuários para todas as áreas principais da interface
        $testUsers = [
            // Admin
            [
                'name' => 'Admin Teste',
                'email' => 'admin.teste@empresa.com',
                'login' => 'admin.teste',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'areas' => [], // Admin vê todas
            ],
            
            // Gestores - uma para cada área principal
            [
                'name' => 'Gestor Financeiro',
                'email' => 'gestor.financeiro@empresa.com',
                'login' => 'gestor.financeiro',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['Financeiro'],
            ],
            [
                'name' => 'Gestor TI',
                'email' => 'gestor.ti@empresa.com',
                'login' => 'gestor.ti',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['TI'],
            ],
            [
                'name' => 'Gestor Compras',
                'email' => 'gestor.compras@empresa.com',
                'login' => 'gestor.compras',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['Compras'],
            ],
            [
                'name' => 'Gestor Gente e Gestão',
                'email' => 'gestor.rh@empresa.com',
                'login' => 'gestor.rh',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['RH'],
            ],
            [
                'name' => 'Gestor Pré Impressão',
                'email' => 'gestor.preimpressao@empresa.com',
                'login' => 'gestor.preimpressao',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['Produto'],
            ],
            [
                'name' => 'Gestor RR - Reclamações',
                'email' => 'gestor.reclamacoes@empresa.com',
                'login' => 'gestor.reclamacoes',
                'password' => Hash::make('password'),
                'role' => 'gestor',
                'areas' => ['Logística'],
            ],
            
            // Atendentes - dois para cada área principal
            [
                'name' => 'Atendente Financeiro 1',
                'email' => 'atendente.financeiro1@empresa.com',
                'login' => 'atendente.financeiro1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Financeiro'],
            ],
            [
                'name' => 'Atendente Financeiro 2',
                'email' => 'atendente.financeiro2@empresa.com',
                'login' => 'atendente.financeiro2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Financeiro'],
            ],
            [
                'name' => 'Atendente TI 1',
                'email' => 'atendente.ti1@empresa.com',
                'login' => 'atendente.ti1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['TI'],
            ],
            [
                'name' => 'Atendente TI 2',
                'email' => 'atendente.ti2@empresa.com',
                'login' => 'atendente.ti2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['TI'],
            ],
            [
                'name' => 'Atendente Compras 1',
                'email' => 'atendente.compras1@empresa.com',
                'login' => 'atendente.compras1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Compras'],
            ],
            [
                'name' => 'Atendente Compras 2',
                'email' => 'atendente.compras2@empresa.com',
                'login' => 'atendente.compras2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Compras'],
            ],
            [
                'name' => 'Atendente RH 1',
                'email' => 'atendente.rh1@empresa.com',
                'login' => 'atendente.rh1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['RH'],
            ],
            [
                'name' => 'Atendente RH 2',
                'email' => 'atendente.rh2@empresa.com',
                'login' => 'atendente.rh2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['RH'],
            ],
            [
                'name' => 'Atendente Pré Impressão 1',
                'email' => 'atendente.preimpressao1@empresa.com',
                'login' => 'atendente.preimpressao1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Produto'],
            ],
            [
                'name' => 'Atendente Pré Impressão 2',
                'email' => 'atendente.preimpressao2@empresa.com',
                'login' => 'atendente.preimpressao2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Produto'],
            ],
            [
                'name' => 'Atendente Reclamações 1',
                'email' => 'atendente.reclamacoes1@empresa.com',
                'login' => 'atendente.reclamacoes1',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Logística'],
            ],
            [
                'name' => 'Atendente Reclamações 2',
                'email' => 'atendente.reclamacoes2@empresa.com',
                'login' => 'atendente.reclamacoes2',
                'password' => Hash::make('password'),
                'role' => 'atendente',
                'areas' => ['Logística'],
            ],
            
            // Usuários finais
            [
                'name' => 'Usuario Teste 1',
                'email' => 'usuario1@empresa.com',
                'login' => 'usuario1',
                'password' => Hash::make('password'),
                'role' => 'usuario',
                'areas' => [],
            ],
            [
                'name' => 'Usuario Teste 2',
                'email' => 'usuario2@empresa.com',
                'login' => 'usuario2',
                'password' => Hash::make('password'),
                'role' => 'usuario',
                'areas' => [],
            ],
            [
                'name' => 'Usuario Teste 3',
                'email' => 'usuario3@empresa.com',
                'login' => 'usuario3',
                'password' => Hash::make('password'),
                'role' => 'usuario',
                'areas' => [],
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'login' => $userData['login'],
                    'password' => $userData['password'],
                    'role_id' => $roles[$userData['role']]->id,
                    'team_id' => null,
                ]
            );

            // Vincular áreas
            if (!empty($userData['areas'])) {
                $areaIds = $areas->filter(function ($area) use ($userData) {
                    return in_array($area->name, $userData['areas']);
                })->pluck('id')->toArray();
                
                if (!empty($areaIds)) {
                    $user->areas()->sync($areaIds);
                }
            }
        }

        // 2. Buscar áreas e categorias existentes
        $allAreas = Area::where('active', true)->get();
        $allCategories = Category::where('active', true)->get();
        $requesterUser = User::where('role_id', $roles['usuario']->id)->first();

        if (!$requesterUser) {
            $this->command->error('Usuário para criar tickets não encontrado!');
            return;
        }

        // 3. Criar tickets para cada área
        $ticketCounter = Ticket::count() + 1;
        
        $requestTypes = [
            'TI' => [
                'sistemas-programas',
                'equipamentos-perifericos',
                'internet-comunicacao',
                'liberacao-acessos',
                'novo-colaborador',
                'substituicao-aquisicao',
            ],
            'Compras' => [
                'solicitacao-compra',
                'solicitacao-amostra',
                'cadastro-item',
                'cadastro-fornecedor',
            ],
            'RH' => [
                'abertura-vaga',
                'movimentacao-pessoal',
                'solicitacao-desligamento',
                'solicitacao-ferias',
                'medidas-disciplinares',
                'beneficios',
                'solicitacao-treinamento',
                'solicitacao-comunicados',
                'solicitacao-hora-extra',
                'lancamentos-folha',
                'atestados-declaracoes-medicas',
            ],
            'Financeiro' => [
                'reembolso',
                'adiantamento',
                'pagamento-geral',
                'devolucao-clientes',
                'pagamento-importacoes',
                'rh',
                'contabilidade',
            ],
            'Logística' => [
                'rrl-reclamacao-logistica',
                'rri-reclamacao-interna',
                'rrq-reclamacao-qualidade',
            ],
            'Produto' => [
                'gabarito',
                'layout',
                'mockup',
                'mockup-impresso',
                'puxada-cor',
                '3d-site',
                'prova-contratual',
                'impressao',
                'desenvol-produto',
            ],
        ];

        $priorities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['open', 'in_progress', 'waiting_user', 'finalized'];

        foreach ($allAreas as $area) {
            // Encontrar categorias relacionadas ou usar a primeira disponível
            $category = $allCategories->first();
            
            // Tentar encontrar request types para esta área
            $areaRequestTypes = $requestTypes[$area->name] ?? [];
            
            if (empty($areaRequestTypes)) {
                // Se não tiver request types específicos, criar um ticket genérico
                $this->createTicket(
                    $area,
                    $category,
                    $requesterUser,
                    $slaService,
                    $ticketCounter++,
                    'geral',
                    $priorities[array_rand($priorities)],
                    $statuses[array_rand($statuses)]
                );
            } else {
                // Criar um ticket para cada request type desta área
                foreach ($areaRequestTypes as $requestType) {
                    $this->createTicket(
                        $area,
                        $category,
                        $requesterUser,
                        $slaService,
                        $ticketCounter++,
                        $requestType,
                        $priorities[array_rand($priorities)],
                        $statuses[array_rand($statuses)]
                    );
                }
            }
        }

        $this->command->info('✅ Usuários de teste criados!');
        $this->command->info('✅ Tickets de teste criados!');
        $this->command->info('');
        $this->command->info('📋 Credenciais de teste:');
        $this->command->info('');
        $this->command->info('   👑 Admin:');
        $this->command->info('      - admin.teste / password');
        $this->command->info('');
        $this->command->info('   👔 Gestores (uma para cada área):');
        $this->command->info('      - gestor.financeiro / password');
        $this->command->info('      - gestor.ti / password');
        $this->command->info('      - gestor.compras / password');
        $this->command->info('      - gestor.rh / password');
        $this->command->info('      - gestor.preimpressao / password');
        $this->command->info('      - gestor.reclamacoes / password');
        $this->command->info('');
        $this->command->info('   🔧 Atendentes (dois para cada área):');
        $this->command->info('      Financeiro: atendente.financeiro1, atendente.financeiro2 / password');
        $this->command->info('      TI: atendente.ti1, atendente.ti2 / password');
        $this->command->info('      Compras: atendente.compras1, atendente.compras2 / password');
        $this->command->info('      RH: atendente.rh1, atendente.rh2 / password');
        $this->command->info('      Pré Impressão: atendente.preimpressao1, atendente.preimpressao2 / password');
        $this->command->info('      Reclamações: atendente.reclamacoes1, atendente.reclamacoes2 / password');
        $this->command->info('');
        $this->command->info('   👤 Usuários:');
        $this->command->info('      - usuario1, usuario2, usuario3 / password');
    }

    private function createTicket(
        Area $area,
        Category $category,
        User $requester,
        SlaCalculationService $slaService,
        int $counter,
        string $requestType,
        string $priority,
        string $status
    ): void {
        // Calcular SLA
        $slaData = $slaService->calculateSlaForTicket($requestType, $priority, $category->id);

        // Criar código único
        $code = 'CH-' . now()->format('Y') . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT);

        // Títulos variados por request type
        $titles = [
            'sistemas-programas' => 'Problema no sistema ERP',
            'equipamentos-perifericos' => 'Mouse não funciona',
            'internet-comunicacao' => 'Internet lenta no escritório',
            'liberacao-acessos' => 'Solicitar acesso ao sistema X',
            'novo-colaborador' => 'Acesso para novo colaborador',
            'substituicao-aquisicao' => 'Solicitar novo notebook',
            'solicitacao-compra' => 'Solicitar compra de material',
            'solicitacao-amostra' => 'Solicitar amostra de produto',
            'cadastro-item' => 'Cadastrar novo item',
            'cadastro-fornecedor' => 'Cadastrar novo fornecedor',
            'abertura-vaga' => 'Abrir vaga para Desenvolvedor',
            'movimentacao-pessoal' => 'Movimentação de colaborador',
            'solicitacao-desligamento' => 'Solicitar desligamento',
            'solicitacao-ferias' => 'Solicitar férias',
            'medidas-disciplinares' => 'Aplicar medida disciplinar',
            'beneficios' => 'Alteração de benefícios',
            'solicitacao-treinamento' => 'Solicitar treinamento',
            'solicitacao-comunicados' => 'Publicar comunicado',
            'solicitacao-hora-extra' => 'Solicitar hora extra',
            'lancamentos-folha' => 'Lançamento na folha',
            'atestados-declaracoes-medicas' => 'Enviar atestado médico',
            'reembolso' => 'Solicitar reembolso',
            'adiantamento' => 'Solicitar adiantamento',
            'pagamento-geral' => 'Solicitar pagamento',
            'devolucao-clientes' => 'Processar devolução',
            'pagamento-importacoes' => 'Pagamento de importação',
            'rrl-reclamacao-logistica' => 'Reclamação logística de cliente',
            'rri-reclamacao-interna' => 'Reclamação interna',
            'rrq-reclamacao-qualidade' => 'Reclamação de qualidade',
            'gabarito' => 'Solicitar gabarito',
            'layout' => 'Solicitar layout',
            'mockup' => 'Solicitar mockup',
            'mockup-impresso' => 'Solicitar mockup impresso',
            'puxada-cor' => 'Solicitar puxada de cor',
            '3d-site' => 'Solicitar 3D/Site',
            'prova-contratual' => 'Solicitar prova contratual',
            'impressao' => 'Solicitar impressão',
            'desenvol-produto' => 'Desenvolvimento de produto',
            'geral' => "Chamado geral - {$area->name}",
        ];

        $title = $titles[$requestType] ?? "Chamado de teste - {$area->name}";
        
        $descriptions = [
            'Este é um chamado de teste para validar o funcionamento do sistema.',
            'Chamado criado para testar permissões e filas.',
            'Ticket de teste para verificar se está caindo na área correta.',
            'Chamado de validação do sistema de permissões.',
            'Este ticket foi criado automaticamente para testes.',
        ];

        $description = $descriptions[array_rand($descriptions)] . "\n\n" .
            "Área: {$area->name}\n" .
            "Categoria: {$category->name}\n" .
            "Tipo: {$requestType}\n" .
            "Prioridade: {$priority}\n" .
            "Status: {$status}";

        $ticket = Ticket::create([
            'code' => $code,
            'title' => $title,
            'description' => $description,
            'area_id' => $area->id,
            'category_id' => $category->id,
            'requester_id' => $requester->id,
            'status' => $status,
            'priority' => $priority,
            'request_type' => $requestType,
            'respond_by' => $slaData['respond_by'] ?? now()->addHours(24),
            'due_at' => $slaData['due_at'] ?? now()->addDays(3),
            'last_status_change_at' => now(),
        ]);

        // Se o status for in_progress ou finalized, atribuir a um atendente
        if (in_array($status, ['in_progress', 'finalized'])) {
            $attendees = User::whereHas('role', function ($query) {
                $query->whereIn('name', ['atendente', 'admin']);
            })->whereHas('areas', function ($query) use ($area) {
                $query->where('areas.id', $area->id);
            })->get();

            if ($attendees->isEmpty()) {
                // Se não tiver atendente na área, usar admin
                $attendees = User::whereHas('role', function ($query) {
                    $query->where('name', 'admin');
                })->get();
            }

            if ($attendees->isNotEmpty()) {
                $assignee = $attendees->random();
                $ticket->assignee_id = $assignee->id;
                $ticket->assigned_at = now();
                
                if ($status === 'in_progress') {
                    $ticket->started_at = now();
                }
                
                if ($status === 'finalized') {
                    $ticket->resolved_at = now();
                    $ticket->resolution_by = $assignee->id;
                    $ticket->resolution_summary = 'Resolvido automaticamente para teste.';
                }
                
                $ticket->save();
            }
        }

        // Criar evento de criação
        TicketEventService::log(
            $ticket,
            $requester,
            'created',
            null,
            $status,
            [
                'request_type' => $requestType,
                'test_ticket' => true,
            ]
        );
    }
}

