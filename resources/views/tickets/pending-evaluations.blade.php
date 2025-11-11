@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Avaliações Pendentes</h1>
                    <p class="mt-2 text-sm text-gray-600">Chamados finalizados que aguardam sua avaliação</p>
                </div>

                @if($pendingEvaluations->count() > 0)
                    <!-- Lista de Chamados -->
                    <div class="space-y-4">
                        @foreach($pendingEvaluations as $ticket)
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <h3 class="text-lg font-medium text-gray-900 truncate">
                                                    {{ $ticket->title }}
                                                </h3>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    {{ $ticket->code }}
                                                </span>
                                            </div>
                                            <div class="mt-1 text-sm text-gray-500">
                                                <span class="font-medium">{{ $ticket->area->name }}</span>
                                                @if($ticket->assignee)
                                                    • Atendido por {{ $ticket->assignee->name }}
                                                @endif
                                                • Finalizado em {{ $ticket->resolved_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <a href="{{ route('tickets.evaluate', $ticket) }}" 
                                       class="btn btn-primary">
                                        Avaliar
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Paginação -->
                    <div class="mt-6">
                        {{ $pendingEvaluations->links() }}
                    </div>
                @else
                    <!-- Estado vazio -->
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma avaliação pendente</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Todos os seus chamados finalizados já foram avaliados.
                        </p>
                        <div class="mt-6">
                            <a href="{{ route('tickets.index') }}" class="btn btn-primary">
                                Ver Meus Chamados
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection










