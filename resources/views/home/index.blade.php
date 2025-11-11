@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header da Página Inicial -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Bem-vindo, {{ auth()->user()->name }}!</h1>
            <p class="mt-2 text-gray-600">Aqui está um resumo do que está acontecendo com seus chamados.</p>
        </div>

        <!-- Cards de Métricas Principais - CLICÁVEIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Meus Chamados -->
            <a href="{{ route('tickets.index', ['my_tickets' => '1']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200 cursor-pointer">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $myTickets }}</div>
                            <div class="text-sm text-gray-500">Meus Chamados</div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Chamados Atribuídos -->
            <a href="{{ route('tickets.index', ['assigned_to_me' => '1']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200 cursor-pointer">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $assignedTickets }}</div>
                            <div class="text-sm text-gray-500">Atribuídos a Mim</div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Chamados Urgentes -->
            <a href="{{ route('tickets.index', ['sla' => 'warning']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200 cursor-pointer">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $urgentTickets }}</div>
                            <div class="text-sm text-gray-500">Urgentes (4h)</div>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Chamados Vencidos -->
            <a href="{{ route('tickets.index', ['sla' => 'overdue']) }}" class="bg-white overflow-hidden shadow-sm rounded-lg hover:shadow-md transition-shadow duration-200 cursor-pointer">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <div class="text-2xl font-bold text-gray-900">{{ $overdueTickets }}</div>
                            <div class="text-sm text-gray-500">Vencidos</div>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ações Rápidas -->
        <div class="mb-8">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Ações Rápidas</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($quickActions as $action)
                <a href="{{ route($action['route']) }}" class="group bg-white p-6 rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-200 hover:border-{{ $action['color'] }}-300">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($action['icon'] === 'plus')
                                <svg class="w-8 h-8 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            @elseif($action['icon'] === 'queue-list')
                                <svg class="w-8 h-8 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            @elseif($action['icon'] === 'ticket')
                                <svg class="w-8 h-8 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @elseif($action['icon'] === 'chart-bar')
                                <svg class="w-8 h-8 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            @elseif($action['icon'] === 'star')
                                <svg class="w-8 h-8 text-{{ $action['color'] }}-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-medium text-gray-900 group-hover:text-{{ $action['color'] }}-600 transition-colors">{{ $action['title'] }}</h3>
                            <p class="text-sm text-gray-500">{{ $action['description'] }}</p>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
        </div>

        <!-- Chamados Recentes -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold text-gray-900">Chamados Recentes</h2>
                    <a href="{{ route('tickets.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Ver todos</a>
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentTickets as $ticket)
                <div class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($ticket->status === 'open') bg-blue-100 text-blue-800
                                    @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800
                                    @elseif($ticket->status === 'waiting_user') bg-orange-100 text-orange-800
                                    @elseif($ticket->status === 'finalized') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($ticket->status === 'open')
                                        Aberto
                                    @elseif($ticket->status === 'in_progress')
                                        Em Progresso
                                    @elseif($ticket->status === 'waiting_user')
                                        Aguardando Usuário
                                    @elseif($ticket->status === 'finalized')
                                        Finalizado
                                    @else
                                        {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                    @endif
                                </span>
                                <span class="text-sm text-gray-500">#{{ $ticket->id }}</span>
                            </div>
                            <p class="mt-1 text-sm font-medium text-gray-900 truncate">{{ $ticket->title }}</p>
                            <div class="mt-1 flex items-center space-x-4 text-xs text-gray-500">
                                <span>{{ $ticket->area->name ?? 'N/A' }}</span>
                                <span>{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                @if($ticket->assignee)
                                <span>Atribuído: {{ $ticket->assignee->name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('tickets.show', $ticket) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Ver detalhes
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>Nenhum chamado encontrado</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
