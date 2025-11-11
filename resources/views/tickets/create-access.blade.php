@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $requestType->name }}</h1>
                    <p class="mt-2 text-gray-600">{{ $requestType->description }}</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('request-areas.show', 'ti') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar aos Tipos TI
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <div class="bg-white shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('tickets.store-access') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Motivo -->
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="motivo" 
                           name="motivo" 
                           value="{{ old('motivo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Descreva resumidamente o motivo desta necessidade"
                           required>
                    @error('motivo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Necessidades -->
                <div>
                    <label for="necessidades" class="block text-sm font-medium text-gray-700 mb-2">
                        Necessidades <span class="text-red-500">*</span>
                    </label>
                    <textarea id="necessidades" 
                              name="necessidades" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descreva os caminhos / sistemas nos quais será necessário liberar"
                              required>{{ old('necessidades') }}</textarea>
                    @error('necessidades')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Níveis de Acesso -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Níveis de Acesso <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="consulta" 
                                   name="niveis_acesso[]" 
                                   value="consulta"
                                   {{ in_array('consulta', old('niveis_acesso', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="consulta" class="ml-2 text-sm text-gray-700">Consulta</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="edicao" 
                                   name="niveis_acesso[]" 
                                   value="edicao"
                                   {{ in_array('edicao', old('niveis_acesso', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="edicao" class="ml-2 text-sm text-gray-700">Edição</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="exclusao" 
                                   name="niveis_acesso[]" 
                                   value="exclusao"
                                   {{ in_array('exclusao', old('niveis_acesso', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="exclusao" class="ml-2 text-sm text-gray-700">Exclusão</label>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecione quais níveis de acesso serão necessários liberar.
                    </p>
                    @error('niveis_acesso')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <!-- Grid de Campos Adicionais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Impacto -->
                    <div>
                        <label for="impacto" class="block text-sm font-medium text-gray-700 mb-2">
                            Impacto (opcional)
                        </label>
                        <select id="impacto" 
                                name="impacto"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="baixo" {{ old('impacto', 'baixo') == 'baixo' ? 'selected' : '' }}>Baixo</option>
                            <option value="medio" {{ old('impacto') == 'medio' ? 'selected' : '' }}>Médio</option>
                            <option value="alto" {{ old('impacto') == 'alto' ? 'selected' : '' }}>Alto</option>
                            <option value="critico" {{ old('impacto') == 'critico' ? 'selected' : '' }}>Crítico</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Impacto.
                        </p>
                        @error('impacto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data para Ficar Pronto -->
                    <div>
                        <label for="data_para_ficar_pronto" class="block text-sm font-medium text-gray-700 mb-2">
                            Data para Ficar Pronto
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_para_ficar_pronto" 
                                   name="data_para_ficar_pronto"
                                   value="{{ old('data_para_ficar_pronto') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Estimativa para Ficar Pronto.
                        </p>
                        @error('data_para_ficar_pronto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Empresa -->
                    <div>
                        <label for="empresa" class="block text-sm font-medium text-gray-700 mb-2">
                            Empresa
                        </label>
                        <select id="empresa" 
                                name="empresa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Nenhum</option>
                            <option value="printbag-embalagens" {{ old('empresa') == 'printbag-embalagens' ? 'selected' : '' }}>Printbag Embalagens</option>
                            <option value="weisul-agricola" {{ old('empresa') == 'weisul-agricola' ? 'selected' : '' }}>Weisul Agrícola</option>
                            <option value="weisul-participacoes" {{ old('empresa') == 'weisul-participacoes' ? 'selected' : '' }}>Weisul Participações</option>
                            <option value="uw" {{ old('empresa') == 'uw' ? 'selected' : '' }}>UW</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Empresa que pertence.
                        </p>
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'ti') }}" 
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
