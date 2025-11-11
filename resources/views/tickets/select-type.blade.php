@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $requestArea->name }}</h1>
                    <p class="mt-2 text-gray-600">Selecione o tipo de solicitação</p>
                </div>
                <div>
                    <a href="{{ route('request-areas.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        ← Voltar às Áreas
                    </a>
                </div>
            </div>
        </div>

        <!-- Grid de Tipos de Solicitação -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($requestTypes as $type)
            <a href="@php
                $route = match($type->slug) {
                    'equipamentos-perifericos' => route('tickets.create-equipment'),
                    'sistemas-programas' => route('tickets.create-systems'),
                    'internet-comunicacao' => route('tickets.create-internet'),
                    'liberacao-acessos' => route('tickets.create-access'),
                    'novo-colaborador' => route('tickets.create-employee'),
                    'substituicao-aquisicao' => route('tickets.create-replacement'),
                    'solicitacao-compra' => route('tickets.create-purchase'),
                    'solicitacao-amostra' => route('tickets.create-sample'),
                    'cadastro-item' => route('tickets.create-item'),
                    'cadastro-fornecedor' => route('tickets.create-supplier'),
                    'abertura-vaga' => route('tickets.create-job-opening'),
                    'movimentacao-pessoal' => route('tickets.create-personnel-movement'),
                    'solicitacao-desligamento' => route('tickets.create-termination'),
                    'solicitacao-ferias' => route('tickets.create-vacation'),
                    'medidas-disciplinares' => route('tickets.create-disciplinary'),
                    'beneficios' => route('tickets.create-benefits'),
                    'solicitacao-treinamento' => route('tickets.create-training'),
                    'solicitacao-comunicados' => route('tickets.create-communication'),
                    'solicitacao-hora-extra' => route('tickets.create-overtime'),
                    'lancamentos-folha' => route('tickets.create-payroll'),
                    'atestados-declaracoes-medicas' => route('tickets.create-medical'),
                    'rrl-reclamacao-logistica' => route('tickets.create-rrl'),
                    'rri-reclamacao-interna' => route('tickets.create-rri'),
                    'rrq-reclamacao-qualidade' => route('tickets.create-rrq'),
                    'gabarito' => route('tickets.create-gabarito'),
                    'layout' => route('tickets.create-layout'),
                    'mockup' => route('tickets.create-mockup'),
                    'mockup-impresso' => route('tickets.create-mockup-impresso'),
                    'puxada-cor' => route('tickets.create-puxada-cor'),
                    '3d-site' => route('tickets.create-3d-site'),
                    'prova-contratual' => route('tickets.create-prova-contratual'),
                    'impressao' => route('tickets.create-impressao'),
                    'desenvol-produto' => route('tickets.create-desenvol-produto'),
                    default => route('tickets.create', ['type' => $type->slug]),
                };
                echo $route;
            @endphp" 
               class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6 border border-gray-200 hover:border-gray-300">
                <div class="flex items-center space-x-4">
                    <!-- Ícone -->
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center" 
                             style="background-color: {{ $type->color }}20; border: 2px solid {{ $type->color }}40;">
                            @if($type->icon === 'currency-dollar')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            @elseif($type->icon === 'calendar-days')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"></path>
                                </svg>
                            @elseif($type->icon === 'envelope')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"></path>
                                </svg>
                            @elseif($type->icon === 'user')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path>
                                </svg>
                            @elseif($type->icon === 'document-text')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @elseif($type->icon === 'user-group')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"></path>
                                </svg>
                            @elseif($type->icon === 'chart-bar')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"></path>
                                </svg>
                            @elseif($type->icon === 'computer-desktop')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            @elseif($type->icon === 'chat-bubble-left-right')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"></path>
                                </svg>
                            @elseif($type->icon === 'phone')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"></path>
                                </svg>
                            @elseif($type->icon === 'lock-closed')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"></path>
                                </svg>
                            @elseif($type->icon === 'user-plus')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a12.5 12.5 0 01-12.5 12.5A12.5 12.5 0 0112.5 7.5a12.5 12.5 0 0112.5 12.5zM12.5 7.5a12.5 12.5 0 00-12.5 12.5A12.5 12.5 0 0012.5 32.5a12.5 12.5 0 0012.5-12.5A12.5 12.5 0 0012.5 7.5z"></path>
                                </svg>
                            @elseif($type->icon === 'arrow-path')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                </svg>
                            @elseif($type->icon === 'shopping-cart')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a2.25 2.25 0 00-2.25-2.25H2.25m0 0h11.25m-5.25 0h5.25m-5.25 0a2.25 2.25 0 00-2.25 2.25v11.25m0 0h11.25m-11.25 0a2.25 2.25 0 01-2.25-2.25V17.25m0 0h11.25M19.5 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M15.75 14.25a2.25 2.25 0 00-2.25-2.25h-5.25m0 0h5.25m-5.25 0v11.25m0 0h11.25M11.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437m0 0a2.25 2.25 0 012.25-2.25h1.5m-1.5 0v11.25m0 0h-3m3 0v-3m0-3h-3m3 0h3"></path>
                                </svg>
                            @elseif($type->icon === 'plus-circle')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($type->icon === 'user-minus')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M22 10.5h-6m-2.25-4.125a12.5 12.5 0 01-12.5 12.5A12.5 12.5 0 0112.5 7.5a12.5 12.5 0 0112.5 12.5zM12.5 7.5a12.5 12.5 0 00-12.5 12.5A12.5 12.5 0 0012.5 32.5a12.5 12.5 0 0012.5-12.5A12.5 12.5 0 0012.5 7.5z"></path>
                                </svg>
                            @elseif($type->icon === 'home')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"></path>
                                </svg>
                            @elseif($type->icon === 'currency-dollar')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @elseif($type->icon === 'device-phone-mobile')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"></path>
                                </svg>
                            @elseif($type->icon === 'clock')
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-6 h-6" style="color: {{ $type->color }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Conteúdo -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $type->name }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ $type->description }}</p>
                    </div>
                    
                    <!-- Seta -->
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
