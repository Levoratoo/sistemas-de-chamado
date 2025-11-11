@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded-2xl p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Solicitações de Pagamento Geral</h1>
                <p class="mt-2 text-sm text-gray-600">Preencha os dados abaixo para solicitar pagamento ao financeiro.</p>
            </div>

            <form action="{{ route('tickets.store-general-payment') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <!-- Título -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título <span class="text-danger-500">*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        required
                        value="{{ old('title') }}"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Ex: Pagamento de recibo referente a serviço"
                    >
                    @error('title')
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

                <!-- Centro de Custo -->
                <div>
                    <label for="cost_center" class="block text-sm font-medium text-gray-700">Centro de Custo <span class="text-danger-500">*</span></label>
                    <select
                        id="cost_center"
                        name="cost_center"
                        required
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Selecione o centro de custo</option>
                        <option value="Printbag - Fábrica" {{ old('cost_center') === 'Printbag - Fábrica' ? 'selected' : '' }}>Printbag - Fábrica</option>
                        <option value="Printbag - Administração" {{ old('cost_center') === 'Printbag - Administração' ? 'selected' : '' }}>Printbag - Administração</option>
                        <option value="Weisul" {{ old('cost_center') === 'Weisul' ? 'selected' : '' }}>Weisul</option>
                    </select>
                    @error('cost_center')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Aprovadores -->
                <div>
                    <label for="approver_id" class="block text-sm font-medium text-gray-700">Aprovadores</label>
                    <select
                        id="approver_id"
                        name="approver_id"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        <option value="">Selecione o aprovador (opcional)</option>
                        @foreach(\App\Models\User::whereHas('role', function($q) { $q->whereIn('name', ['gestor', 'admin']); })->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}" {{ old('approver_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} - {{ $user->email }}</option>
                        @endforeach
                    </select>
                    @error('approver_id')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Justificativa / Descrição -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Justificativa / Descrição <span class="text-danger-500">*</span></label>
                    <textarea
                        id="description"
                        name="description"
                        required
                        rows="4"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Descreva a justificativa para o pagamento..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de pagamento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de pagamento <span class="text-danger-500">*</span></label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="transferencia"
                                {{ old('payment_type', 'transferencia') === 'transferencia' ? 'checked' : '' }}
                                class="mr-2 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm text-gray-700">Transferência Bancária</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="pix"
                                {{ old('payment_type') === 'pix' ? 'checked' : '' }}
                                class="mr-2 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm text-gray-700">PIX</span>
                        </label>
                        <label class="flex items-center">
                            <input
                                type="radio"
                                name="payment_type"
                                value="boleto"
                                {{ old('payment_type') === 'boleto' ? 'checked' : '' }}
                                class="mr-2 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm text-gray-700">Boleto</span>
                        </label>
                    </div>
                    @error('payment_type')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Dados Bancários -->
                <div>
                    <label for="bank_data" class="block text-sm font-medium text-gray-700">Dados Bancários (opcional)</label>
                    <textarea
                        id="bank_data"
                        name="bank_data"
                        rows="3"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Banco, agência, conta corrente e CPF/CNPJ ou PIX conforme tipo de pagamento selecionado"
                    >{{ old('bank_data') }}</textarea>
                    @error('bank_data')
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
                    <p class="mt-2 text-xs text-gray-500">Arraste e solte arquivos ou clique para navegar</p>
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











