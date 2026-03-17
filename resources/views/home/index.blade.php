@extends('layouts.app')

@section('content')
{{-- Hero Section --}}
<div class="relative overflow-hidden bg-gradient-to-br from-slate-900 via-blue-950 to-indigo-900">
    {{-- Decorative background circles --}}
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500 opacity-10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-16 -left-16 w-80 h-80 bg-indigo-500 opacity-10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            {{-- Greeting --}}
            <div>
                <p class="text-blue-300 text-sm font-medium uppercase tracking-widest mb-2">
                    {{ now()->locale('pt_BR')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
                <h1 class="text-4xl font-bold text-white leading-tight">
                    Olá, {{ explode(' ', auth()->user()->name)[0] }}
                    <span class="inline-block animate-wave">👋</span>
                </h1>
                <p class="mt-2 text-blue-200 text-base max-w-lg">
                    Seja bem-vindo ao Sistema de Chamados Printbag. O que você precisa fazer hoje?
                </p>
            </div>

            {{-- Status badges --}}
            <div class="flex flex-wrap gap-3">
                @if($urgentTickets > 0)
                <a href="{{ route('tickets.index', ['sla' => 'warning']) }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-amber-500/20 border border-amber-400/40 text-amber-300 rounded-xl text-sm font-medium hover:bg-amber-500/30 transition-all">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-400"></span>
                    </span>
                    {{ $urgentTickets }} urgente{{ $urgentTickets > 1 ? 's' : '' }}
                </a>
                @endif
                @if($overdueTickets > 0)
                <a href="{{ route('tickets.index', ['sla' => 'overdue']) }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-red-500/20 border border-red-400/40 text-red-300 rounded-xl text-sm font-medium hover:bg-red-500/30 transition-all">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-400"></span>
                    </span>
                    {{ $overdueTickets }} vencido{{ $overdueTickets > 1 ? 's' : '' }}
                </a>
                @endif
                @if($urgentTickets === 0 && $overdueTickets === 0)
                <div class="flex items-center gap-2 px-4 py-2.5 bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 rounded-xl text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Tudo em dia
                </div>
                @endif

                {{-- My tickets counter --}}
                <a href="{{ route('tickets.index', ['my_tickets' => '1']) }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-medium hover:bg-white/20 transition-all">
                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ $myTickets }} meus chamados
                </a>

                @if($assignedTickets > 0)
                <a href="{{ route('tickets.index', ['assigned_to_me' => '1']) }}"
                   class="flex items-center gap-2 px-4 py-2.5 bg-white/10 border border-white/20 text-white rounded-xl text-sm font-medium hover:bg-white/20 transition-all">
                    <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    {{ $assignedTickets }} atribuídos
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 space-y-10">

    {{-- Quick Actions --}}
    <section>
        <div class="flex items-center gap-3 mb-5">
            <div class="w-1 h-5 bg-blue-600 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800">Ações Rápidas</h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-{{ count($quickActions) > 3 ? '3' : count($quickActions) }} gap-4">
            @foreach($quickActions as $action)
            @php
                $colorMap = [
                    'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-100',   'hover' => 'hover:border-blue-300 hover:bg-blue-50',  'icon_bg' => 'bg-blue-600',   'text' => 'text-blue-700'],
                    'green'  => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-100','hover' => 'hover:border-emerald-300 hover:bg-emerald-50','icon_bg'=>'bg-emerald-600','text' => 'text-emerald-700'],
                    'purple' => ['bg' => 'bg-purple-50',  'border' => 'border-purple-100', 'hover' => 'hover:border-purple-300 hover:bg-purple-50','icon_bg' => 'bg-purple-600','text' => 'text-purple-700'],
                    'indigo' => ['bg' => 'bg-indigo-50',  'border' => 'border-indigo-100', 'hover' => 'hover:border-indigo-300 hover:bg-indigo-50','icon_bg' => 'bg-indigo-600','text' => 'text-indigo-700'],
                    'yellow' => ['bg' => 'bg-amber-50',   'border' => 'border-amber-100',  'hover' => 'hover:border-amber-300 hover:bg-amber-50', 'icon_bg' => 'bg-amber-500', 'text' => 'text-amber-700'],
                ];
                $c = $colorMap[$action['color']] ?? $colorMap['blue'];
            @endphp
            <a href="{{ route($action['route']) }}"
               class="group relative flex items-center gap-4 p-5 bg-white border {{ $c['border'] }} rounded-2xl shadow-sm {{ $c['hover'] }} transition-all duration-200 hover:shadow-md">
                <div class="{{ $c['icon_bg'] }} w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-110 transition-transform duration-200">
                    @if($action['icon'] === 'plus')
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    @elseif($action['icon'] === 'queue-list')
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                    @elseif($action['icon'] === 'ticket')
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    @elseif($action['icon'] === 'chart-bar')
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    @elseif($action['icon'] === 'star')
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 group-hover:{{ $c['text'] }} transition-colors">{{ $action['title'] }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $action['description'] }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            @endforeach
        </div>
    </section>

    {{-- Recent Tickets --}}
    <section>
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                <h2 class="text-lg font-semibold text-gray-800">Chamados Recentes</h2>
            </div>
            <a href="{{ route('tickets.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors">
                Ver todos
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            @forelse($recentTickets as $ticket)
            @php
                $statusConfig = [
                    'open'         => ['label' => 'Aberto',            'class' => 'bg-blue-100 text-blue-700',   'dot' => 'bg-blue-500'],
                    'in_progress'  => ['label' => 'Em Progresso',      'class' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500'],
                    'waiting_user' => ['label' => 'Aguardando',        'class' => 'bg-orange-100 text-orange-700','dot'=> 'bg-orange-500'],
                    'finalized'    => ['label' => 'Finalizado',        'class' => 'bg-emerald-100 text-emerald-700','dot'=>'bg-emerald-500'],
                ];
                $sc = $statusConfig[$ticket->status] ?? ['label' => ucfirst($ticket->status), 'class' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-400'];
            @endphp
            <div class="group flex items-center gap-4 px-6 py-4 border-b border-gray-50 last:border-b-0 hover:bg-gray-50/70 transition-colors">
                {{-- Status dot --}}
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium {{ $sc['class'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} inline-block"></span>
                        {{ $sc['label'] }}
                    </span>
                </div>

                {{-- Ticket info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono text-gray-400">#{{ $ticket->id }}</span>
                        <h3 class="text-sm font-medium text-gray-900 truncate group-hover:text-indigo-700 transition-colors">
                            {{ $ticket->title }}
                        </h3>
                    </div>
                    <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                        @if($ticket->area)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            {{ $ticket->area->name }}
                        </span>
                        @endif
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </span>
                        @if($ticket->assignee)
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $ticket->assignee->name }}
                        </span>
                        @endif
                    </div>
                </div>

                {{-- CTA --}}
                <a href="{{ route('tickets.show', $ticket) }}"
                   class="flex-shrink-0 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-900 opacity-0 group-hover:opacity-100 transition-opacity">
                    Ver detalhes
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @empty
            <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="w-14 h-14 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">Nenhum chamado encontrado</p>
                <a href="{{ route('request-areas.index') }}" class="mt-3 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    Abrir primeiro chamado →
                </a>
            </div>
            @endforelse
        </div>
    </section>
</div>

<style>
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        20% { transform: rotate(-15deg); }
        40% { transform: rotate(15deg); }
        60% { transform: rotate(-10deg); }
        80% { transform: rotate(10deg); }
    }
    .animate-wave {
        display: inline-block;
        animation: wave 1.5s ease-in-out 0.5s 1;
    }
</style>
@endsection
