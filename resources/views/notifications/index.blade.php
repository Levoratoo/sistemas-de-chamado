@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Todas as Notificações</h2>
                        <p class="text-gray-600 mt-1">Histórico completo com logs detalhados</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium">{{ $notifications->total() }}</span> notificações
                        </div>
                        <form action="{{ route('notifications.mark-all-as-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm">
                                Marcar todas como lidas
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="px-6 py-4">
                <form method="GET" action="{{ route('notifications.index') }}" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <select name="category" class="form-select">
                            <option value="">Todas as categorias</option>
                            <option value="ticket_finalized" {{ request('category') === 'ticket_finalized' ? 'selected' : '' }}>Ticket Finalizado</option>
                            <option value="ticket_assigned" {{ request('category') === 'ticket_assigned' ? 'selected' : '' }}>Ticket Atribuído</option>
                            <option value="sla_warning" {{ request('category') === 'sla_warning' ? 'selected' : '' }}>Alerta SLA</option>
                        </select>
                    </div>
                    <div>
                        <select name="priority" class="form-select">
                            <option value="">Todas as prioridades</option>
                            <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Crítico</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alto</option>
                            <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Médio</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Baixo</option>
                        </select>
                    </div>
                    <div>
                        <select name="status" class="form-select">
                            <option value="">Todas</option>
                            <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Não lidas</option>
                            <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Lidas</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if(request()->hasAny(['category', 'priority', 'status']))
                            <a href="{{ route('notifications.index') }}" class="btn btn-secondary btn-sm">Limpar</a>
                        @endif
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Notificações -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="divide-y divide-gray-200">
                @forelse ($notifications as $notification)
                    <div class="p-6 hover:bg-gray-50 {{ $notification->read_at ? 'bg-gray-50' : 'bg-white' }}">
                        <div class="flex items-start space-x-4">
                            <!-- Ícone -->
                            <div class="flex-shrink-0">
                                @if($notification->type === 'App\Notifications\TicketFinalizedNotification')
                                    <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->type === 'App\Notifications\TicketAssignedNotification')
                                    <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.146-1.283-.423-1.848M13 16H7m6 0H9m10 0a2 2 0 01-2-2V7a2 2 0 012-2h3a2 2 0 012 2v7a2 2 0 01-2 2h-3z"></path>
                                        </svg>
                                    </div>
                                @elseif($notification->type === 'App\Notifications\SlaWarningNotification')
                                    <div class="h-10 w-10 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div class="h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17H8l-4 4V5a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <!-- Conteúdo -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-lg font-medium text-gray-900">
                                            {{ $notification->data['message'] ?? 'Nova notificação' }}
                                        </h3>
                                        @if($notification->data['priority'] ?? false)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                                $notification->data['priority'] === 'critical' ? 'bg-red-100 text-red-800' :
                                                $notification->data['priority'] === 'high' ? 'bg-orange-100 text-orange-800' :
                                                $notification->data['priority'] === 'medium' ? 'bg-yellow-100 text-yellow-800' :
                                                $notification->data['priority'] === 'low' ? 'bg-green-100 text-green-800' :
                                                'bg-gray-100 text-gray-800'
                                            }}">
                                                {{ ucfirst($notification->data['priority']) }}
                                            </span>
                                        @endif
                                        @if(!$notification->read_at)
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Nova</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        @if (!$notification->read_at)
                                            <form action="{{ route('notifications.mark-as-read', $notification->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                    Marcar como lida
                                                </button>
                                            </form>
                                        @endif
                                        @if (isset($notification->data['url']))
                                            <a href="{{ $notification->data['url'] }}" class="btn btn-primary btn-sm">
                                                {{ $notification->data['action_text'] ?? 'Ver detalhes' }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Metadados -->
                                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                    <span>{{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                    @if($notification->data['ticket_type'] ?? false)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-gray-600">
                                            {{ $notification->data['ticket_type'] }}
                                        </span>
                                    @endif
                                    @if($notification->data['area_name'] ?? false)
                                        <span class="px-2 py-1 bg-gray-100 rounded text-gray-600">
                                            {{ $notification->data['area_name'] }}
                                        </span>
                                    @endif
                                    @if($notification->read_at)
                                        <span class="text-green-600">
                                            Lida em {{ $notification->read_at->format('d/m/Y H:i') }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Informações Adicionais -->
                                <div class="mt-3 text-sm text-gray-600">
                                    @if(isset($notification->data['ticket_id']))
                                        <p><strong>Ticket:</strong> #{{ $notification->data['ticket_id'] }}</p>
                                    @endif
                                    @if(isset($notification->data['requester_name']))
                                        <p><strong>Solicitante:</strong> {{ $notification->data['requester_name'] }}</p>
                                    @endif
                                    @if(isset($notification->data['assignee_name']))
                                        <p><strong>Responsável:</strong> {{ $notification->data['assignee_name'] }}</p>
                                    @endif
                                    @if(isset($notification->data['assigned_by']))
                                        <p><strong>Atribuído por:</strong> {{ $notification->data['assigned_by'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <svg class="h-12 w-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-4-4V9a6 6 0 10-12 0v4l-4 4h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-lg font-medium">Nenhuma notificação encontrada</p>
                        <p class="text-sm">Tente ajustar os filtros ou aguarde novas notificações.</p>
                    </div>
                @endforelse
            </div>

            <!-- Paginação -->
            @if($notifications->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
