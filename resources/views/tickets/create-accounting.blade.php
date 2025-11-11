@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-2xl p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Contabilidade</h1>
                <p class="mt-2 text-sm text-gray-600">Solicitações da Contabilidade para o Financeiro</p>
            </div>

            <form action="{{ route('tickets.store-accounting') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Resumo (Título) -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Resumo <span class="text-danger-500">*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        required
                        value="{{ old('title') }}"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Resuma brevemente o título da solicitação"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <!-- Data de pagamento -->
                    <div>
                        <label for="payment_date" class="block text-sm font-medium text-gray-700">Data de pagamento</label>
                        <input
                            type="date"
                            id="payment_date"
                            name="payment_date"
                            value="{{ old('payment_date') }}"
                            class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('payment_date')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Valor a ser pago -->
                    <div>
                        <label for="payment_amount" class="block text-sm font-medium text-gray-700">Valor a ser pago</label>
                        <input
                            type="number"
                            id="payment_amount"
                            name="payment_amount"
                            step="0.01"
                            min="0"
                            value="{{ old('payment_amount') }}"
                            class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="0,00"
                        >
                        @error('payment_amount')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Descrição -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição <span class="text-danger-500">*</span></label>
                    <textarea
                        id="description"
                        name="description"
                        required
                        rows="4"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Descreva a solicitação..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Empresa -->
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Empresa <span class="text-danger-500">*</span></label>
                    <select
                        id="company"
                        name="company"
                        required
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Selecione a empresa</option>
                        <option value="Printbag Embalagens" {{ old('company') === 'Printbag Embalagens' ? 'selected' : '' }}>Printbag Embalagens</option>
                        <option value="Weisul Agrícola" {{ old('company') === 'Weisul Agrícola' ? 'selected' : '' }}>Weisul Agrícola</option>
                        <option value="Weisul Participações" {{ old('company') === 'Weisul Participações' ? 'selected' : '' }}>Weisul Participações</option>
                        <option value="UW" {{ old('company') === 'UW' ? 'selected' : '' }}>UW</option>
                    </select>
                    @error('company')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Anexos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Anexos (opcional)</label>
                    <div class="mt-2 flex items-center justify-center border-2 border-dashed border-gray-300 rounded-lg px-6 py-4 hover:border-primary-500 transition">
                        <input
                            type="file"
                            id="attachments"
                            name="attachments[]"
                            multiple
                            class="w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100"
                        >
                    </div>
                    <p class="mt-2 text-xs text-gray-500">Arraste e solte arquivos, cole capturas de tela ou clique para navegar</p>
                    @error('attachments.*')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex gap-4">
                    <button
                        type="submit"
                        class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        Criar
                    </button>
                    <a
                        href="{{ route('dashboard') }}"
                        class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 text-center"
                    >
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











