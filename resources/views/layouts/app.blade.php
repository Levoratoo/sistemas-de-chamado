<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Printbag - Sistema de Chamados') }}</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='50' font-size='50'>🌱</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        // Atualizar contador de notificações
        function updateNotificationCount() {
            fetch('/api/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('notification-badge');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Erro ao atualizar contador:', error));
        }

        // Carregar notificações no dropdown
        function loadNotifications() {
            const notificationsList = document.getElementById('notifications-list');
            
            fetch('/api/notifications/recent')
                .then(response => response.json())
                .then(data => {
                    if (data.notifications && data.notifications.length > 0) {
                        notificationsList.innerHTML = data.notifications.map(notification => `
                            <div class="p-4 border-b border-gray-100 hover:bg-gray-50 ${notification.read_at ? 'bg-gray-50' : 'bg-white'}">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0">
                                        ${getNotificationIcon(notification.type)}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm text-gray-900">${notification.data.message || 'Nova notificação'}</p>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <span>${formatTime(notification.created_at)}</span>
                                            ${notification.data.ticket_type ? `<span class="ml-2 px-1 py-0.5 bg-gray-100 rounded text-gray-600">${notification.data.ticket_type}</span>` : ''}
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        ${!notification.read_at ? `
                                            <button onclick="markAsRead('${notification.id}')" class="text-blue-600 hover:text-blue-800 text-xs">
                                                Marcar como lida
                                            </button>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        notificationsList.innerHTML = `
                            <div class="p-4 text-center text-gray-500">
                                <svg class="h-8 w-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-4-4V9a6 6 0 10-12 0v4l-4 4h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                <p>Nenhuma notificação</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Erro ao carregar notificações:', error);
                    notificationsList.innerHTML = `
                        <div class="p-4 text-center text-red-500">
                            <p>Erro ao carregar notificações</p>
                        </div>
                    `;
                });
        }

        // Marcar notificação como lida
        function markAsRead(notificationId) {
            fetch(`/notifications/${notificationId}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    updateNotificationCount();
                }
            })
            .catch(error => console.error('Erro ao marcar como lida:', error));
        }

        // Marcar todas como lidas
        function markAllAsRead() {
            fetch('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    updateNotificationCount();
                }
            })
            .catch(error => console.error('Erro ao marcar todas como lidas:', error));
        }

        // Obter ícone da notificação
        function getNotificationIcon(type) {
            if (type.includes('TicketFinalized')) {
                return '<svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
            } else if (type.includes('TicketAssigned')) {
                return '<svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.146-1.283-.423-1.848M13 16H7m6 0H9m10 0a2 2 0 01-2-2V7a2 2 0 012-2h3a2 2 0 012 2v7a2 2 0 01-2 2h-3z"></path></svg>';
            } else if (type.includes('SlaWarning')) {
                return '<svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>';
            } else {
                return '<svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17H8l-4 4V5a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2z"></path></svg>';
            }
        }

        // Formatar tempo
        function formatTime(timeString) {
            const time = new Date(timeString);
            const now = new Date();
            const diff = now - time;
            
            if (diff < 60000) return 'Agora';
            if (diff < 3600000) return `${Math.floor(diff / 60000)}min atrás`;
            if (diff < 86400000) return `${Math.floor(diff / 3600000)}h atrás`;
            return time.toLocaleDateString('pt-BR');
        }

        // Obter classe CSS para prioridade
        function getPriorityClass(priority) {
            switch(priority) {
                case 'critical': return 'bg-red-100 text-red-800';
                case 'high': return 'bg-orange-100 text-orange-800';
                case 'medium': return 'bg-yellow-100 text-yellow-800';
                case 'low': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        }

        // Obter texto da prioridade
        function getPriorityText(priority) {
            switch(priority) {
                case 'critical': return 'Crítico';
                case 'high': return 'Alto';
                case 'medium': return 'Médio';
                case 'low': return 'Baixo';
                default: return 'Normal';
            }
        }

        // Atualizar contador quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            updateNotificationCount();

            // Atualizar a cada 30 segundos
            setInterval(updateNotificationCount, 30000);
        });
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 flex flex-col min-h-screen">
    <div class="flex-1">
        <!-- Navigation -->
        <nav class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="flex items-center">
                                <img src="{{ asset('logo-printbag.svg') }}" alt="Printbag Embalagens" class="h-14" style="max-height: 3.5rem;">
                            </a>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Início
                            </a>
                            @auth
                                @if(auth()->user()->isGestor() || auth()->user()->isAdmin())
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Dashboard
                                    </a>
                                @endif
                            @endauth
                            <a href="{{ route('tickets.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Meus Chamados
                            </a>

                            <a href="{{ route('tickets.kanban') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">

                                Kanban

                            </a>
                            <a href="{{ route('evaluations.pending') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                Avaliações
                            </a>
                            @auth
                                @if(auth()->user()->canManageTickets())
                                    <a href="{{ route('queue.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Fila
                                    </a>
                                @endif
                                @if(auth()->user()->canManageUsers())
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        Usuarios
                                    </a>
                                @endif
                            @endauth
                        </div>
                    </div>

                    <!-- Settings Dropdown -->
                    <div class="hidden sm:ml-6 sm:flex sm:items-center">
                        <div class="ml-3 relative">
                            <div class="flex items-center space-x-4">
                                <a href="{{ route('request-areas.index') }}" class="btn btn-primary">
                                    Abrir Chamado
                                </a>
                                
                                <!-- Notificações -->
                                <div class="relative" x-data="{ open: false }" @click.away="open = false">
                                    <button @click="open = !open; if(open) loadNotifications()" class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full">
                                        <span class="sr-only">Ver notificações</span>
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-4-4V9a6 6 0 10-12 0v4l-4 4h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                        </svg>
                                        <span id="notification-badge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                                    </button>

                                    <!-- Dropdown de Notificações -->
                                    <div x-show="open" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                                        <div class="p-4 border-b border-gray-200">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-medium text-gray-900">Notificações</h3>
                                                <button @click="markAllAsRead()" class="text-sm text-primary-600 hover:text-primary-800">Marcar todas como lidas</button>
                                            </div>
                                        </div>
                                        <div class="max-h-96 overflow-y-auto">
                                            <div id="notifications-list">
                                                <!-- Notificações serão carregadas aqui -->
                                                <div class="p-4 text-center text-gray-500">
                                                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-600 mx-auto"></div>
                                                    <p class="mt-2">Carregando notificações...</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-4 border-t border-gray-200 text-center">
                                            <a href="{{ route('notifications.index') }}" class="text-sm text-primary-600 hover:text-primary-800">Ver todas as notificações</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        <span class="sr-only">Abrir menu do usuário</span>
                                        @if(auth()->user()->profile_photo)
                                            <img src="{{ Storage::url(auth()->user()->profile_photo) }}" 
                                                 alt="{{ auth()->user()->name }}" 
                                                 class="h-8 w-8 rounded-full object-cover border-2 border-white shadow-md">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-primary-600 flex items-center justify-center text-white font-medium">
                                                {{ substr(auth()->user()->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </button>

                                    <div x-show="open" @click.away="open = false" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" style="display: none;">
                                        <div class="px-4 py-2 text-sm text-gray-700 border-b">
                                            <div class="font-medium">{{ auth()->user()->name }}</div>
                                            <div class="text-gray-500">{{ auth()->user()->email }}</div>
                                            <div class="text-xs text-gray-400">{{ ucfirst(auth()->user()->role->name) }}</div>
                                        </div>
                                        <a href="{{ route('profile.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Meu Perfil
                                        </a>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Sair
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            @if(session('success'))
                <div class="bg-success-100 border border-success-400 text-success-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-danger-100 border border-danger-400 text-danger-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="bg-danger-100 border border-danger-400 text-danger-700 px-4 py-3 rounded relative mx-4 mt-4" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    
    <!-- Rodapé -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                <div class="text-sm text-gray-600">
                    © {{ date('Y') }} Printbag Embalagens. Todos os direitos reservados.
                </div>
                <div class="text-sm text-gray-500">
                    Sistema desenvolvido por <span class="font-semibold text-gray-700">Pedro Levorato</span>
                </div>
            </div>
        </div>
    </footer>
    
    @stack('scripts')
</body>
</html>

