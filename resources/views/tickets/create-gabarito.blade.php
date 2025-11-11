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
                    <a href="{{ route('request-areas.show', 'pre-impressao') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
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
            <form method="POST" action="{{ route('tickets.store-gabarito') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Título -->
                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">
                        Título <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="titulo" 
                           name="titulo" 
                           value="{{ old('titulo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Ex: Printbag - Verão - Sacola"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Manter a seguinte ordem de nomenclatura NOME - PROJETO - PRODUTO e/ou NOME - PRODUTO exemplo: Printbag - Verão - Sacola e/ou Printbag - Sacola
                    </p>
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Solicitação Pré -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Solicitação Pré <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="gabarito"
                                   {{ is_array(old('solicitacao_pre')) && in_array('gabarito', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Gabarito</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="layout"
                                   {{ is_array(old('solicitacao_pre')) && in_array('layout', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Layout</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="mock-up"
                                   {{ is_array(old('solicitacao_pre')) && in_array('mock-up', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Mock up</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="mock-up-impresso"
                                   {{ is_array(old('solicitacao_pre')) && in_array('mock-up-impresso', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Mock up Impresso</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="puxada-cor"
                                   {{ is_array(old('solicitacao_pre')) && in_array('puxada-cor', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Puxada de Cor</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="3d-site"
                                   {{ is_array(old('solicitacao_pre')) && in_array('3d-site', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">3D/Site</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="prova-contratual"
                                   {{ is_array(old('solicitacao_pre')) && in_array('prova-contratual', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Prova Contratual</span>
                        </label>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" 
                                   name="solicitacao_pre[]" 
                                   value="desenvol-produto"
                                   {{ is_array(old('solicitacao_pre')) && in_array('desenvol-produto', old('solicitacao_pre')) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Desenvol. Produto</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecione todos os serviços necessário para essa demanda.
                    </p>
                    @error('solicitacao_pre')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Produto -->
                <div>
                    <label for="produto" class="block text-sm font-medium text-gray-700 mb-2">
                        Produto <span class="text-red-500">*</span>
                    </label>
                    <select id="produto" 
                            name="produto"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecione...</option>
                        <option value="SACOLA" {{ old('produto') == 'SACOLA' ? 'selected' : '' }}>SACOLA</option>
                        <option value="CAIXA" {{ old('produto') == 'CAIXA' ? 'selected' : '' }}>CAIXA</option>
                        <option value="SACHE" {{ old('produto') == 'SACHE' ? 'selected' : '' }}>SACHE</option>
                        <option value="OUTROS" {{ old('produto') == 'OUTROS' ? 'selected' : '' }}>OUTROS</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Tipo de Produto.
                    </p>
                    @error('produto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de campos opcionais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cálculo / Opção -->
                    <div>
                        <label for="calculo_opcao" class="block text-sm font-medium text-gray-700 mb-2">
                            Cálculo / Opção
                        </label>
                        <input type="text" 
                               id="calculo_opcao" 
                               name="calculo_opcao" 
                               value="{{ old('calculo_opcao') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Cálculo / Opção Cálculo
                        </p>
                        @error('calculo_opcao矩形')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Furo de Fita -->
                    <div>
                        <label for="furo_fita" class="block text-sm font-medium text-gray-700 mb-2">
                            Furo de Fita
                        </label>
                        <select id="furo_fita" 
                                name="furo_fita"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione...</option>
                            <option value="Opção 01" {{ old('furo_fita') == 'Opção 01' ? 'selected' : '' }}>Opção 01</option>
                            <option value="Opção 02" {{ old('furo_fita') == 'Opção 02' ? 'selected' : '' }}>Opção 02</option>
                            <option value="Opção 03" {{ old('furo_fita') == 'Opção 03' ? 'selected' : '' }}>Opção 03</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Opção de Furo da Fita
                        </p>
                        @error('furo_fita')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cor da Alça -->
                    <div>
                        <label for="cor_alca" class="block text-sm font-medium text-gray-700 mb-2">
                            Cor da Alça (opcional)
                        </label>
                        <input type="text" 
                               id="cor_alca" 
                               name="cor_alca" 
                               value="{{ old('cor_alca') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Cor da Alça
                        </p>
                        @error('cor_alca')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cores -->
                    <div>
                        <label for="cores" class="block text-sm font-medium text-gray-700 mb-2">
                            Cores
                        </label>
                        <input type="text" 
                               id="cores" 
                               name="cores" 
                               value="{{ old('cores') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Cor
                        </p>
                        @error('cores')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Data Prevista -->
                <div>
                    <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Prevista
                    </label>
                    <input type="date" 
                           id="data_prevista" 
                           name="data_prevista" 
                           value="{{ old('data_prevista') }}"
                           min="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">
                        Data Estimada para Ficar Pronto
                    </p>
                    @error('data_prevista')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Observação -->
                <div>
                    <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">
                        Observação (opcional)
                    </label>
                    <textarea id="observacao" 
                              name="observacao" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Observações a ser adicionada.
                    </p>
                    @error('observacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Anexos -->
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                        Anexos (opcional)
                    </label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Arraste e solte arquivos, cole capturas de tela ou</span>
                                    <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple accept="image/*,application/pdf,.doc,.docx">
                                    <span class="ml-1">navegar</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">
                                Arquivos, Fotos, Modelo, Exemplo etc...
                            </p>
                        </div>
                    </div>
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'pre-impressao') }}" 
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

