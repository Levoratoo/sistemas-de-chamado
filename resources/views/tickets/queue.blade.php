@extends('layouts.app')

@section('content')
@once
    <style>
        [x-cloak] { display: none; }
    </style>
@endonce

@php
    $statusOptions = [
        App\Models\Ticket::STATUS_OPEN => 'Aberto',
        App\Models\Ticket::STATUS_IN_PROGRESS => 'Em andamento',
        App\Models\Ticket::STATUS_WAITING_USER => 'Aguardando usuário',
        App\Models\Ticket::STATUS_FINALIZED => 'Finalizado',
    ];
    
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

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Fila da minha area</h1>
                    <p class="text-sm text-gray-500">Acompanhe os chamados que precisam de atendimento.</p>
                </div>
                <span class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold text-emerald-700">
                    Finalizados por mim (mes): {{ $finalizadosMes }}
                </span>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="GET" action="{{ route('queue.index') }}" class="space-y-4">
                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex flex-col gap-1 w-full sm:w-48">
                            <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                            <select id="status" name="status" class="form-select w-full">
                                <option value="">Todos</option>
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex flex-col gap-1 w-full sm:w-48">
                            <label for="request_type" class="text-sm font-medium text-gray-700">Tipo</label>
                            <select id="request_type" name="request_type" class="form-select w-full">
                                <option value="">Todos</option>
                                <option value="geral" @selected($filters['request_type'] === 'geral')>Geral</option>
                                <option value="reembolso" @selected($filters['request_type'] === 'reembolso')>Solicitação de Reembolso</option>
                                <option value="adiantamento" @selected($filters['request_type'] === 'adiantamento')>Adiantamento a Fornecedores</option>
                                <option value="pagamento_geral" @selected($filters['request_type'] === 'pagamento_geral')>Pagamento Geral</option>
                                <option value="devolucao_clientes" @selected($filters['request_type'] === 'devolucao_clientes')>Devolução de Clientes</option>
                                <option value="pagamento_importacoes" @selected($filters['request_type'] === 'pagamento_importacoes')>Pagamento de Importações</option>
                                <option value="rh" @selected($filters['request_type'] === 'rh')>RH</option>
                                <option value="contabilidade" @selected($filters['request_type'] === 'contabilidade')>Contabilidade</option>
                            </select>
                        </div>

                        @if($canManageAll)
                            <div class="flex flex-col gap-1 w-full sm:w-48">
                                <label for="area_id" class="text-sm font-medium text-gray-700">Departamento</label>
                                <select id="area_id" name="area_id" class="form-select w-full">
                                    <option value="">Todas</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}" @selected($filters['area_id'] == $area->id)>{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <input id="only_unassigned" name="only_unassigned" type="checkbox" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-200" @checked($filters['only_unassigned'])>
                                <label for="only_unassigned" class="text-sm text-gray-700">Somente não atribuídos</label>
                            </div>

                            <div class="flex items-center gap-2">
                                <input id="assigned_to_me" name="assigned_to_me" type="checkbox" value="1" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-200" @checked($filters['assigned_to_me'])>
                                <label for="assigned_to_me" class="text-sm text-gray-700">Atribuídos a mim</label>
                            </div>
                        </div>

                        <div class="flex gap-2 w-full sm:w-auto sm:ml-auto">
                            <button type="submit" class="btn btn-primary">Aplicar</button>
                            <a href="{{ route('queue.index') }}" class="btn btn-secondary">Limpar</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-0">
                <table class="min-w-full table-fixed divide-y divide-gray-200">
                    <colgroup>
                        <col class="w-36">
                        <col class="w-28">
                        <col class="w-32">
                        <col class="w-64">
                        <col class="w-32">
                        <col class="w-32">
                        <col class="w-32">
                        <col class="w-32">
                        <col class="w-36">
                    </colgroup>
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Área</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Atribuído</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-gray-50 align-top">
                                <td class="px-5 py-4 text-sm font-semibold text-primary-600">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="hover:underline hover:text-primary-800">{{ $ticket->code }}</a>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">
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
                                <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">
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
                                <td class="px-5 py-4 text-sm text-gray-900 whitespace-normal break-words">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="hover:underline text-gray-900">{{ $ticket->title }}</a>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $ticket->requester->name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-500 whitespace-nowrap">{{ $ticket->assignee?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">
                                    @php
                                        $user = auth()->user();
                                        $alreadyAssignedToOther = $ticket->assignee_id && $ticket->assignee_id !== ($user->id ?? null);
                                        $assignDisabled = $alreadyAssignedToOther || $ticket->status === \App\Models\Ticket::STATUS_FINALIZED;
                                        $assignMessage = $alreadyAssignedToOther ? 'Chamado já atribuído a outro atendente.' : null;
                                    @endphp

                                    @if($assignDisabled)
                                            <span class="text-xs text-gray-400">
                                                @if($ticket->status === \App\Models\Ticket::STATUS_FINALIZED)
                                                    Finalizado
                                                @elseif($alreadyAssignedToOther)
                                                    Em atendimento por {{ $ticket->assignee?->name ?? "outro atendente" }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('tickets.assign', $ticket) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                    class="btn btn-primary btn-sm"
                                                    @if($assignMessage) title="{{ $assignMessage }}" @endif>
                                                    Assumir
                                                </button>
                                            </form>
                                        @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-6 text-center text-sm text-gray-500">Nenhum chamado encontrado nesta fila.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($tickets->hasPages())
                    <div class="px-5 py-4 border-t border-gray-200">
                        {{ $tickets->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
