@extends('layouts.app')

@section('content')
@once
@php
    // Mapear Area do banco para perfil da interface (RequestArea)
    function getRequestAreaName($areaName) {
        $areaMapping = [
            'Financeiro' => 'Financeiro',
            'TI' => 'TI',
            'Compras' => 'Compras',
            'RH' => 'Gente e Gestão',
            'Produto' => 'Pré Impressão',
            'Logística' => 'RR - Registro de Reclamações',
        ];
        
        if ($areaName && isset($areaMapping[$areaName])) {
            return $areaMapping[$areaName];
        }
        
        // Para outras áreas, tenta encontrar correspondência parcial
        if ($areaName) {
            if (stripos($areaName, 'RH') !== false || stripos($areaName, 'Gente') !== false || stripos($areaName, 'Gestão') !== false) {
                return 'Gente e Gestão';
            }
            if (stripos($areaName, 'Produto') !== false || stripos($areaName, 'Pré') !== false || stripos($areaName, 'Impressão') !== false) {
                return 'Pré Impressão';
            }
            if (stripos($areaName, 'Logística') !== false || stripos($areaName, 'Reclamação') !== false || stripos($areaName, 'RR') !== false) {
                return 'RR - Registro de Reclamações';
            }
        }
        
        return 'Geral';
    }
@endphp
<style>
    .column-drag-handle {
        cursor: move;
        cursor: grab;
        user-select: none;
    }
    .column-drag-handle:active {
        cursor: grabbing;
    }
    .column-drag-handle:hover {
        opacity: 0.7;
    }
    th.dragging {
        opacity: 0.5;
    }
    th.drag-over {
        border-left: 3px solid #3b82f6;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endonce
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 space-y-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Chamados</h1>
                        <p class="text-sm text-gray-500">Acompanhe e filtre os chamados registrados por voce ou pela sua equipe.</p>
                    </div>
                    <a href="{{ route('request-areas.index') }}" class="btn btn-primary">
                        Novo Chamado
                    </a>
                </div>

                <div class="card">
                    <div class="p-6">

                        <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap items-end gap-4">
                            <div class="flex flex-col gap-1 w-full sm:w-48">
                                <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Aberto</option>
                                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Em andamento</option>
                                    <option value="waiting_user" {{ request('status') === 'waiting_user' ? 'selected' : '' }}>Aguardando usuário</option>
                                    <option value="finalized" {{ request('status') === 'finalized' ? 'selected' : '' }}>Finalizado</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1 w-full sm:w-48">
                                <label for="request_type" class="text-sm font-medium text-gray-700">Tipo</label>
                                <select name="request_type" id="request_type" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="geral" {{ request('request_type') === 'geral' ? 'selected' : '' }}>Geral</option>
                                    <option value="reembolso" {{ request('request_type') === 'reembolso' ? 'selected' : '' }}>Solicitação de Reembolso</option>
                                    <option value="adiantamento" {{ request('request_type') === 'adiantamento' ? 'selected' : '' }}>Adiantamento a Fornecedores</option>
                                    <option value="pagamento_geral" {{ request('request_type') === 'pagamento_geral' ? 'selected' : '' }}>Pagamento Geral</option>
                                    <option value="devolucao_clientes" {{ request('request_type') === 'devolucao_clientes' ? 'selected' : '' }}>Devolução de Clientes</option>
                                    <option value="pagamento_importacoes" {{ request('request_type') === 'pagamento_importacoes' ? 'selected' : '' }}>Pagamento de Importações</option>
                                    <option value="rh" {{ request('request_type') === 'rh' ? 'selected' : '' }}>RH</option>
                                    <option value="contabilidade" {{ request('request_type') === 'contabilidade' ? 'selected' : '' }}>Contabilidade</option>
                                </select>
                            </div>

                            <div class="flex flex-col gap-1 w-full sm:w-48">
                                <label for="priority" class="text-sm font-medium text-gray-700">Prioridade</label>
                                <select name="priority" id="priority" class="form-select">
                                    <option value="">Todas</option>
                                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baixa</option>
                                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Média</option>
                                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Crítica</option>
                                </select>
                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                @if(auth()->user()->canManageTickets())
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="assigned_to_me" id="assigned_to_me" value="1" {{ request('assigned_to_me') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                        <label for="assigned_to_me" class="text-sm text-gray-700">Atribuídos a mim</label>
                                    </div>
                                @endif

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" name="my_tickets" id="my_tickets" value="1" {{ request('my_tickets') ? 'checked' : '' }} class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                    <label for="my_tickets" class="text-sm text-gray-700">Meus chamados</label>
                                </div>
                            </div>

                            <div class="flex gap-2 w-full sm:w-auto sm:ml-auto">
                                <button type="submit" class="btn btn-primary">
                                    Filtrar
                                </button>
                                <a href="{{ route('tickets.index') }}" class="btn btn-secondary">
                                    Limpar
                                </a>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="card">
                    <div class="p-0 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" style="table-layout: auto;">
                            <thead class="bg-gray-50" id="table-header">
                                <tr>
                                    <th scope="col" data-column="id" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            ID
                                        </span>
                                    </th>
                                    <th scope="col" data-column="tipo" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Tipo
                                        </span>
                                    </th>
                                    <th scope="col" data-column="titulo" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle max-w-xs">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Título
                                        </span>
                                    </th>
                                    <th scope="col" data-column="area" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Área
                                        </span>
                                    </th>
                                    <th scope="col" data-column="status" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Status
                                        </span>
                                    </th>
                                    <th scope="col" data-column="prioridade" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Prioridade
                                        </span>
                                    </th>
                                    <th scope="col" data-column="sla" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            SLA
                                        </span>
                                    </th>
                                    <th scope="col" data-column="atribuido" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Atribuído
                                        </span>
                                    </th>
                                    <th scope="col" data-column="acoes" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider column-drag-handle whitespace-nowrap">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                                            </svg>
                                            Ações
                                        </span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tickets as $ticket)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-3 py-4 text-sm font-medium text-primary-600 whitespace-nowrap" data-column="id">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="hover:text-primary-800 hover:underline">
                                                {{ $ticket->code }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-600 whitespace-nowrap" data-column="tipo">
                                            @if($ticket->request_type === 'reembolso')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Reembolso</span>
                                            @elseif($ticket->request_type === 'adiantamento')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">Adiantamento</span>
                                            @elseif($ticket->request_type === 'pagamento_geral')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pagamento</span>
                                            @elseif($ticket->request_type === 'devolucao_clientes')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Devolução</span>
                                            @elseif($ticket->request_type === 'pagamento_importacoes')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Importação</span>
                                            @elseif($ticket->request_type === 'rh')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">RH</span>
                                            @elseif($ticket->request_type === 'contabilidade')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Contabilidade</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Geral</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-900 break-words max-w-xs" data-column="titulo">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="block hover:underline text-gray-900 truncate">
                                                {{ $ticket->title }}
                                            </a>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-600 whitespace-nowrap" data-column="area">
                                            @php
                                                $areaName = $ticket->area?->name ?? '';
                                                $requestAreaName = getRequestAreaName($areaName);
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                @if($requestAreaName === 'Financeiro') bg-green-100 text-green-800
                                                @elseif($requestAreaName === 'TI') bg-blue-100 text-blue-800
                                                @elseif($requestAreaName === 'Compras') bg-orange-100 text-orange-800
                                                @elseif($requestAreaName === 'Gente e Gestão') bg-purple-100 text-purple-800
                                                @elseif($requestAreaName === 'Pré Impressão') bg-red-100 text-red-800
                                                @elseif($requestAreaName === 'RR - Registro de Reclamações') bg-orange-100 text-orange-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ $requestAreaName }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap" data-column="status">
                                            <span class="badge {{ $ticket->status_badge_class }}">
                                                {{ $ticket->status_label }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap" data-column="prioridade">
                                            <span class="badge {{ $ticket->priority_badge_class }}">
                                                {{ $ticket->priority_label }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap" data-column="sla">
                                            @php
                                                // SLA é sempre 7 dias após a abertura do chamado
                                                $slaDate = $ticket->created_at->copy()->addDays(7);
                                                $now = now();
                                                
                                                if ($slaDate->isPast()) {
                                                    // Calcular quantos dias se passaram desde que venceu
                                                    $daysOverdue = (int) $slaDate->diffInDays($now);
                                                    $badgeClass = 'badge-danger';
                                                    $slaText = 'Vencido há ' . $daysOverdue . ' ' . ($daysOverdue === 1 ? 'dia' : 'dias');
                                                } else {
                                                    // Calcular dias restantes e arredondar para inteiro
                                                    $daysRemaining = (int) round($now->diffInDays($slaDate, false));
                                                    if ($daysRemaining <= 1) {
                                                        $badgeClass = 'badge-warning';
                                                        $slaText = 'Proximo do vencimento';
                                                    } else {
                                                        $badgeClass = 'badge-success';
                                                        $slaText = $daysRemaining . ' dias restantes';
                                                    }
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ $slaText }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-gray-500 whitespace-nowrap" data-column="atribuido">
                                            {{ $ticket->assignee?->name ?? '-' }}
                                        </td>
                                        <td class="px-3 py-4 text-sm font-medium whitespace-nowrap" data-column="acoes">
                                            <a href="{{ route('tickets.show', $ticket) }}" class="text-primary-600 hover:text-primary-900">
                                                Ver
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-5 py-4 text-center text-sm text-gray-500">
                                            Nenhum chamado encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($tickets->hasPages())
                        <div class="px-5 py-4 border-t border-gray-200">
                            {{ $tickets->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tableHeader = document.getElementById('table-header');
    if (!tableHeader) return;
    
    const headerRow = tableHeader.querySelector('tr');
    const tableBody = document.querySelector('tbody');
    
    if (!headerRow || !tableBody) return;
    
    // Ordem padrão das colunas conforme a imagem: ID, ÁREA, TIPO, TÍTULO, PRIORIDADE, SLA, ATRIBUÍDO, STATUS, AÇÕES
    const defaultOrder = ['id', 'area', 'tipo', 'titulo', 'prioridade', 'sla', 'atribuido', 'status', 'acoes'];
    
    // Carregar ordem salva do localStorage
    const savedOrder = localStorage.getItem('tickets_table_column_order');
    let columnOrder = savedOrder ? JSON.parse(savedOrder) : defaultOrder;
    
    // Função para aplicar ordem das colunas
    function applyColumnOrder(order) {
        if (!headerRow || !tableBody) return;
        
        // Garantir que todas as colunas estejam presentes
        const allColumns = ['id', 'area', 'tipo', 'titulo', 'prioridade', 'sla', 'atribuido', 'status', 'acoes'];
        const completeOrder = allColumns.filter(col => order.includes(col))
            .concat(allColumns.filter(col => !order.includes(col)));
        
        // Reordenar header
        const headers = Array.from(headerRow.querySelectorAll('th[data-column]'));
        const sortedHeaders = completeOrder.map(col => 
            headers.find(th => th.dataset.column === col)
        ).filter(Boolean);
        
        // Remover todos os headers primeiro
        headers.forEach(th => th.remove());
        
        // Adicionar na ordem correta
        sortedHeaders.forEach(th => headerRow.appendChild(th));
        
        // Reordenar células nas linhas do body
        tableBody.querySelectorAll('tr').forEach(row => {
            const cells = Array.from(row.querySelectorAll('td[data-column]'));
            const sortedCells = completeOrder.map(col => 
                cells.find(td => td.dataset.column === col)
            ).filter(Boolean);
            
            // Remover todas as células primeiro
            cells.forEach(td => td.remove());
            
            // Adicionar na ordem correta
            sortedCells.forEach(td => row.appendChild(td));
        });
        
        // Salvar no localStorage
        localStorage.setItem('tickets_table_column_order', JSON.stringify(completeOrder));
    }
    
    // Aplicar ordem salva ao carregar
    if (savedOrder) {
        applyColumnOrder(columnOrder);
    }
    
    // Inicializar SortableJS
    if (typeof Sortable !== 'undefined') {
        const sortable = Sortable.create(headerRow, {
            animation: 150,
            handle: '.column-drag-handle',
            draggable: 'th',
            onEnd: function(evt) {
                // Obter nova ordem dos headers
                const newOrder = Array.from(headerRow.querySelectorAll('th[data-column]'))
                    .map(th => th.dataset.column)
                    .filter(Boolean);
                
                // Garantir que todas as colunas estejam presentes
                const allColumns = ['id', 'area', 'tipo', 'titulo', 'prioridade', 'sla', 'atribuido', 'status', 'acoes'];
                const completeOrder = newOrder.filter(col => allColumns.includes(col))
                    .concat(allColumns.filter(col => !newOrder.includes(col)));
                
                // Aplicar nova ordem nas células do body
                if (tableBody) {
                    tableBody.querySelectorAll('tr').forEach(row => {
                        const cells = Array.from(row.querySelectorAll('td[data-column]'));
                        const newOrderedCells = completeOrder.map(col => 
                            cells.find(td => td.dataset.column === col)
                        ).filter(Boolean);
                        
                        // Remover todas as células
                        cells.forEach(td => td.remove());
                        
                        // Adicionar na nova ordem
                        newOrderedCells.forEach(td => row.appendChild(td));
                    });
                }
                
                // Salvar nova ordem
                localStorage.setItem('tickets_table_column_order', JSON.stringify(completeOrder));
            }
        });
    }
});
</script>
@endsection

