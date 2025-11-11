@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Abrir Chamado</h1>
            <p class="mt-2 text-sm text-gray-600">Selecione o tipo de solicitação</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {{-- Geral removido --}}

            <!-- Solicitação de Reembolso -->
            <a href="{{ route('tickets.create-reimbursement') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 group-hover:bg-purple-200 transition">
                        <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Solicitação de Reembolso</h3>
                        <p class="mt-1 text-sm text-gray-500">Solicitar reembolso para o Financeiro</p>
                    </div>
                </div>
            </a>

            <!-- Solicitação de Adiantamento -->
            <a href="{{ route('tickets.create-advance') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 group-hover:bg-orange-200 transition">
                        <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Solicitação de Adiantamento</h3>
                        <p class="mt-1 text-sm text-gray-500">Solicitação de adiantamento para o Financeiro</p>
                    </div>
                </div>
            </a>

            <!-- Solicitações de Pagamento Geral -->
            <a href="{{ route('tickets.create-general-payment') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-yellow-100 group-hover:bg-yellow-200 transition">
                        <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Solicitações de Pagamento Geral</h3>
                        <p class="mt-1 text-sm text-gray-500">Envio de Recibos/B Boletos para o Financeiro</p>
                    </div>
                </div>
            </a>

            <!-- Devolução de Clientes -->
            <a href="{{ route('tickets.create-customer-return') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 group-hover:bg-green-200 transition">
                        <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Devolução de Clientes</h3>
                        <p class="mt-1 text-sm text-gray-500">Devolução de Clientes</p>
                    </div>
                </div>
            </a>

            <!-- Solicitação de Pagamento de Importações -->
            <a href="{{ route('tickets.create-import-payment') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 group-hover:bg-blue-200 transition">
                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Solicitação de Pagamento de Importações</h3>
                        <p class="mt-1 text-sm text-gray-500">Solicitação de Pagamento de Importações</p>
                    </div>
                </div>
            </a>

            <!-- RH -->
            <a href="{{ route('tickets.create-rh') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 group-hover:bg-indigo-200 transition">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5m-5 4v2a1 1 0 001 1h4a1 1 0 001-1v-2" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">RH</h3>
                        <p class="mt-1 text-sm text-gray-500">Solicitações do RH para o Financeiro</p>
                    </div>
                </div>
            </a>

            <!-- Contabilidade -->
            <a href="{{ route('tickets.create-accounting') }}" class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-sm ring-1 ring-gray-200 hover:shadow-md hover:ring-primary-500 transition">
                <div class="flex items-center space-x-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 group-hover:bg-red-200 transition">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-primary-600 transition">Contabilidade</h3>
                        <p class="mt-1 text-sm text-gray-500">Solicitações da Contabilidade para o Financeiro</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection

