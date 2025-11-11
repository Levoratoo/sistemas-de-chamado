@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header do Dashboard Executivo -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Executivo</h1>
            <p class="mt-2 text-gray-600">Métricas avançadas e análises detalhadas do sistema de chamados</p>
        </div>
        
        <div class="space-y-6">
        <!-- Cards de Métricas Principais - CLICÁVEIS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total de Chamados -->
            <a href="{{ route('tickets.index') }}" class="bg-white border-2 border-blue-200 overflow-hidden shadow-lg rounded-lg hover:shadow-xl hover:scale-105 hover:border-blue-400 transition-all duration-200 transform">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-600 mb-1">Total de Chamados</div>
                            <div class="text-3xl font-bold text-gray-900">{{ $totalTickets ?? 0 }}</div>
                            <div class="text-sm text-gray-500 mt-2 flex items-center">
                                <svg class="w-4 h-4 mr-1 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span class="text-green-600 font-semibold">+{{ $ticketsThisMonth ?? 0 }}</span> este mês
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

            <!-- SLA Status -->
            <a href="{{ route('tickets.index', ['sla' => $overdueTickets > 0 ? 'overdue' : null]) }}" class="bg-white border-2 {{ $slaOnTime >= 90 ? 'border-green-200 hover:border-green-400' : ($slaOnTime >= 70 ? 'border-yellow-200 hover:border-yellow-400' : 'border-red-200 hover:border-red-400') }} overflow-hidden shadow-lg rounded-lg hover:shadow-xl hover:scale-105 transition-all duration-200 transform">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-600 mb-1">SLA em Dia</div>
                            <div class="text-3xl font-bold {{ $slaOnTime >= 90 ? 'text-green-600' : ($slaOnTime >= 70 ? 'text-yellow-600' : 'text-red-600') }}">{{ $slaOnTime ?? 0 }}%</div>
                            <div class="text-sm text-gray-500 mt-2">
                                @if($overdueTickets > 0)
                                    <span class="text-red-600 font-semibold">{{ $overdueTickets }} vencidos</span>
                                @else
                                    <span class="text-green-600">Todos no prazo</span>
                                @endif
                            </div>
                        </div>
                        <div class="w-16 h-16 {{ $slaOnTime >= 90 ? 'bg-green-100' : ($slaOnTime >= 70 ? 'bg-yellow-100' : 'bg-red-100') }} rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 {{ $slaOnTime >= 90 ? 'text-green-600' : ($slaOnTime >= 70 ? 'text-yellow-600' : 'text-red-600') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Tempo Médio de Resolução -->
            <div class="bg-white border-2 border-yellow-200 overflow-hidden shadow-lg rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-600 mb-1">Tempo Médio</div>
                            <div class="text-3xl font-bold text-gray-900">{{ $avgResolutionTime ?? '0h' }}</div>
                            <div class="text-sm text-gray-500 mt-2">Resolução</div>
                        </div>
                        <div class="w-16 h-16 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Taxa de Satisfação -->
            <a href="{{ route('tickets.index', ['status' => 'finalized']) }}" class="bg-white border-2 border-purple-200 overflow-hidden shadow-lg rounded-lg hover:shadow-xl hover:scale-105 hover:border-purple-400 transition-all duration-200 transform">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-600 mb-1">Satisfação</div>
                            <div class="text-3xl font-bold text-purple-600">{{ $satisfactionRate ?? 0 }}%</div>
                            <div class="text-sm text-gray-500 mt-2">
                                {{ $totalEvaluations ?? 0 }} avaliações
                            </div>
                        </div>
                        <div class="w-16 h-16 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cards de Status Detalhados - CLICÁVEIS -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Status dos Chamados -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">Status dos Chamados</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('tickets.index', ['status' => 'open']) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-blue-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-blue-500 rounded-full mr-3 group-hover:scale-125 transition-transform"></div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-blue-700">Abertos</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900 group-hover:text-blue-600">{{ $openTickets ?? 0 }}</span>
                        </a>
                        <a href="{{ route('tickets.index', ['status' => 'in_progress']) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-yellow-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-yellow-500 rounded-full mr-3 group-hover:scale-125 transition-transform"></div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-yellow-700">Em Progresso</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900 group-hover:text-yellow-600">{{ $inProgressTickets ?? 0 }}</span>
                        </a>
                        <a href="{{ route('tickets.index', ['status' => 'waiting_user']) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-orange-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-orange-500 rounded-full mr-3 group-hover:scale-125 transition-transform"></div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-orange-700">Aguardando</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900 group-hover:text-orange-600">{{ $waitingTickets ?? 0 }}</span>
                        </a>
                        <a href="{{ route('tickets.index', ['status' => 'finalized']) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-green-50 transition-colors group">
                            <div class="flex items-center">
                                <div class="w-4 h-4 bg-green-500 rounded-full mr-3 group-hover:scale-125 transition-transform"></div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-green-700">Finalizados</span>
                            </div>
                            <span class="text-lg font-bold text-gray-900 group-hover:text-green-600">{{ $resolvedTickets ?? 0 }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Performance por Área -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">Performance por Área</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($areaPerformance ?? [] as $area)
                        <a href="{{ route('tickets.index', ['area_id' => $area['id']]) }}" class="flex items-center justify-between p-3 rounded-lg hover:bg-indigo-50 transition-colors group">
                            <div class="flex items-center flex-1 min-w-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                                    <span class="text-xs font-bold text-white">{{ substr($area['name'], 0, 2) }}</span>
                                </div>
                                <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700 truncate">{{ $area['name'] }}</span>
                            </div>
                            <div class="text-right ml-3 flex-shrink-0">
                                <div class="text-sm font-bold text-gray-900 group-hover:text-indigo-600">{{ $area['tickets'] }}</div>
                                <div class="text-xs text-gray-500">{{ $area['avg_time'] }}</div>
                            </div>
                        </a>
                        @empty
                        <div class="text-center text-gray-500 py-8">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-sm">Nenhum dado disponível</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Alertas SLA -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="text-lg font-semibold text-gray-900">Alertas SLA</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @if(($overdueTickets ?? 0) > 0)
                        <a href="{{ route('tickets.index', ['sla' => 'overdue']) }}" class="flex items-center p-4 bg-gradient-to-r from-red-50 to-red-100 rounded-lg hover:from-red-100 hover:to-red-200 transition-all cursor-pointer border-l-4 border-red-500">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-base font-bold text-red-800">{{ $overdueTickets }} chamados vencidos</div>
                                <div class="text-sm text-red-600 mt-1">Ação necessária imediatamente</div>
                            </div>
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @endif

                        @if(($nearDueTickets ?? 0) > 0)
                        <a href="{{ route('tickets.index', ['sla' => 'warning']) }}" class="flex items-center p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg hover:from-yellow-100 hover:to-yellow-200 transition-all cursor-pointer border-l-4 border-yellow-500">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-base font-bold text-yellow-800">{{ $nearDueTickets }} próximos do vencimento</div>
                                <div class="text-sm text-yellow-600 mt-1">Atenção necessária</div>
                            </div>
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @endif

                        @if(($overdueTickets ?? 0) == 0 && ($nearDueTickets ?? 0) == 0)
                        <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-green-100 rounded-lg border-l-4 border-green-500">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="text-base font-bold text-green-800">Todos os SLAs em dia</div>
                                <div class="text-sm text-green-600 mt-1">Excelente desempenho!</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Atendentes -->
        @if(!empty($topAttendants))
        <div class="bg-white overflow-hidden shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                    <h3 class="text-base font-semibold text-gray-900">Top Atendentes</h3>
                </div>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($topAttendants as $index => $attendant)
                @php
                    $position = $index + 1;
                    $isTopThree = $position <= 3;
                    
                    // Cores minimalistas baseadas na posição
                    $rankStyles = [
                        1 => ['accent' => 'text-yellow-600', 'badge' => 'text-yellow-600'],
                        2 => ['accent' => 'text-gray-500', 'badge' => 'text-gray-500'],
                        3 => ['accent' => 'text-orange-600', 'badge' => 'text-orange-600']
                    ];
                    
                    $style = $rankStyles[$position] ?? ['accent' => 'text-indigo-600', 'badge' => 'text-indigo-600'];
                @endphp
                <div class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors">
                    <!-- Posição e Troféu -->
                    <div class="flex-shrink-0 w-10 flex flex-col items-center mr-3">
                        @if($isTopThree)
                            <svg class="w-4 h-4 {{ $style['accent'] }} mb-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                        @endif
                        <span class="text-xs font-medium {{ $style['badge'] }}">
                            {{ $position }}º
                        </span>
                    </div>
                    
                    <!-- Avatar minimalista -->
                    <div class="flex-shrink-0 mr-3">
                        <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Informações -->
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $attendant['name'] }}</h4>
                        <div class="flex items-center mt-0.5">
                            <svg class="w-3 h-3 text-gray-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-xs text-gray-500">{{ $attendant['area'] }}</span>
                        </div>
                    </div>
                    
                    <!-- Contador de resolvidos -->
                    <div class="flex items-center ml-4 flex-shrink-0">
                        <div class="text-right mr-3">
                            <div class="text-base font-semibold text-gray-900">{{ $attendant['resolved'] }}</div>
                            <div class="text-xs text-gray-400">resolvidos</div>
                        </div>
                        <div class="w-6 h-6 rounded-full {{ $style['badge'] }} bg-opacity-10 flex items-center justify-center">
                            <svg class="w-3 h-3 {{ $style['accent'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Tabela de Chamados Recentes -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Chamados Recentes</h3>
                    <a href="{{ route('tickets.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                        Ver todos
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SLA</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($recentTickets as $ticket)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        {{ $ticket->code }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <a href="{{ route('tickets.show', $ticket) }}" class="max-w-xs truncate block hover:underline">
                                        {{ $ticket->title }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge {{ $ticket->status_badge_class }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="badge {{ $ticket->priority_badge_class }}">
                                        {{ $ticket->priority_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($ticket->isOverdue())
                                        @php
                                            $slaDate = $ticket->created_at->copy()->addDays(7);
                                            $daysOverdue = (int) $slaDate->diffInDays(now());
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Vencido há {{ $daysOverdue }} {{ $daysOverdue === 1 ? 'dia' : 'dias' }}
                                        </span>
                                    @elseif($ticket->due_at && $ticket->due_at->isFuture() && $ticket->due_at->diffInHours(now()) <= 2)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Próximo do Vencimento
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            OK
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Nenhum chamado encontrado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
