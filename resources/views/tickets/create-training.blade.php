@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $requestType->name }}</h1>
                    <p class="mt-2 text-gray-600">{{ $requestType->description }}</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('request-areas.show', 'gente-gestao') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar aos Tipos
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <div class="bg-white shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('tickets.store-training') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Justificativa -->
                <div>
                    <label for="justificativa" class="block text-sm font-medium text-gray-700 mb-2">
                        Justificativa <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="justificativa" 
                           name="justificativa" 
                           value="{{ old('justificativa') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Descreva resumidamente o motivo do treinamento"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Descreva resumidamente o motivo do treinamento.
                    </p>
                    @error('justificativa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Empresa -->
                    <div>
                        <label for="empresa" class="block text-sm font-medium text-gray-700 mb-2">
                            Empresa <span class="text-red-500">*</span>
                        </label>
                        <select id="empresa" 
                                name="empresa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="printbag-embalagens" {{ old('empresa', 'printbag-embalagens') == 'printbag-embalagens' ? 'selected' : '' }}>Printbag Embalagens</option>
                            <option value="weisul-agricola" {{ old('empresa') == 'weisul-agricola' ? 'selected' : '' }}>Weisul Agrícola</option>
                            <option value="weisul-participacoes" {{ old('empresa') == 'weisul-participacoes' ? 'selected' : '' }}>Weisul Participações</option>
                            <option value="uw" {{ old('empresa') == 'uw' ? 'selected' : '' }}>UW</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Empresa que o colaborador está.
                        </p>
                        @error('empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome do Gestor Solicitante -->
                    <div>
                        <label for="nome_gestor_solicitante" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Gestor Solicitante <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome_gestor_solicitante" 
                               name="nome_gestor_solicitante" 
                               value="{{ old('nome_gestor_solicitante') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome completo do gestor"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Nome completo do gestor solicitante.
                        </p>
                        @error('nome_gestor_solicitante')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome do Colaborador -->
                    <div>
                        <label for="nome_colaborador" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Colaborador <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome_colaborador" 
                               name="nome_colaborador" 
                               value="{{ old('nome_colaborador') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome completo do colaborador"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Nome completo do colaborador que terá movimentação.
                        </p>
                        @error('nome_colaborador')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Treinamento (opcional) -->
                    <div>
                        <label for="tipo_treinamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Treinamento (opcional)
                        </label>
                        <select id="tipo_treinamento" 
                                name="tipo_treinamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="nenhum" {{ old('tipo_treinamento', 'nenhum') == 'nenhum' ? 'selected' : '' }}>Nenhum</option>
                            <option value="atividades-cargo-funcao" {{ old('tipo_treinamento') == 'atividades-cargo-funcao' ? 'selected' : '' }}>Treinamento para a realização de atividades no cargo/função</option>
                            <option value="desenvolver-habilidades" {{ old('tipo_treinamento') == 'desenvolver-habilidades' ? 'selected' : '' }}>Treinamento para desenvolver habilidades</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Treinamento para a realização de atividades no cargo/função. Ou Treinamento para desenvolver habilidades.
                        </p>
                        @error('tipo_treinamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Local do Treinamento -->
                    <div>
                        <label for="local_treinamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Local do Treinamento
                        </label>
                        <select id="local_treinamento" 
                                name="local_treinamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="nenhum" {{ old('local_treinamento', 'nenhum') == 'nenhum' ? 'selected' : '' }}>Nenhum</option>
                            <option value="interno-grupo" {{ old('local_treinamento') == 'interno-grupo' ? 'selected' : '' }}>Interno - Grupo Weisul</option>
                            <option value="externo-consultoria" {{ old('local_treinamento') == 'externo-consultoria' ? 'selected' : '' }}>Externo - Consultoria/Fornecedores</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Para a realização dos treinamento feitos dentro do Grupo Weisul. Para a realização dos treinamentos feitos por consultoria, fornecedores, e outros. Se externo, descreva o contato e detalhes na Observação.
                        </p>
                        @error('local_treinamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Observação -->
                <div>
                    <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">
                        Observação (opcional)
                    </label>
                    <textarea id="observacao" 
                              name="observacao" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Observações a ser adicionada.">{{ old('observacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Observações a ser adicionada.
                    </p>
                    @error('observacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Data Prevista -->
                <div>
                    <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Prevista Para a Realização do Treinamento
                    </label>
                    <div class="relative">
                        <input type="date" 
                               id="data_prevista" 
                               name="data_prevista"
                               value="{{ old('data_prevista') }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Data de início da solicitação.
                    </p>
                    @error('data_prevista')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Anexos -->
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                        Anexos (opcional)
                    </label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="mt-4">
                            <label for="attachments" class="cursor-pointer">
                                <span class="mt-2 block text-sm font-medium text-gray-900">
                                    Arraste e solte arquivos, cole capturas de tela ou 
                                    <span class="text-blue-600 hover:text-blue-500">navegar</span>
                                </span>
                            </label>
                            <input type="file" 
                                   id="attachments" 
                                   name="attachments[]" 
                                   multiple 
                                   accept="image/*,.pdf,.doc,.docx,.txt"
                                   class="hidden">
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Para solicitar treinamentos para mais de um colaborador incluir anexo com a relação de nomes.
                    </p>
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'gente-gestao') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Criar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











