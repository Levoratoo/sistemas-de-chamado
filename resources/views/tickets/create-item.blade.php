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
                    <a href="{{ route('request-areas.show', 'compras') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
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
            <form method="POST" action="{{ route('tickets.store-item') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                           required>
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Centro de Custo -->
                <div>
                    <label for="centro_custo" class="block text-sm font-medium text-gray-700 mb-2">
                        Centro de Custo <span class="text-red-500">*</span>
                    </label>
                    <select id="centro_custo" 
                            name="centro_custo"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecione...</option>
                        <option value="printbag-fabrica" {{ old('centro_custo') == 'printbag-fabrica' ? 'selected' : '' }}>Printbag - Fábrica</option>
                        <option value="printbag-administracao" {{ old('centro_custo') == 'printbag-administracao' ? 'selected' : '' }}>Printbag - Administração</option>
                        <option value="weisul" {{ old('centro_custo') == 'weisul' ? 'selected' : '' }}>Weisul</option>
                    </select>
                    @error('centro_custo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="descricao" 
                           name="descricao" 
                           value="{{ old('descricao') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- NCM -->
                    <div>
                        <label for="ncm" class="block text-sm font-medium text-gray-700 mb-2">
                            NCM (opcional)
                        </label>
                        <input type="text" 
                               id="ncm" 
                               name="ncm" 
                               value="{{ old('ncm') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Código NCM">
                        @error('ncm')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Unidade de Medida -->
                    <div>
                        <label for="unidade_medida" class="block text-sm font-medium text-gray-700 mb-2">
                            Unidade de medida <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="unidade_medida" 
                               name="unidade_medida" 
                               value="{{ old('unidade_medida') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex: UN, KG, M, etc."
                               required>
                        @error('unidade_medida')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Data Prevista -->
                <div>
                    <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Prevista (opcional)
                    </label>
                    <div class="relative">
                        <input type="date" 
                               id="data_prevista" 
                               name="data_prevista"
                               value="{{ old('data_prevista') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
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
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Observações a ser adicionada.">{{ old('observacao') }}</textarea>
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
                        Anexar arquivo (opcional)
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
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'compras') }}" 
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

<script>
// Preview de arquivos
document.getElementById('attachments').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length > 0) {
        console.log(`${files.length} arquivo(s) selecionado(s)`);
    }
});

// Drag and drop
const dropZone = document.querySelector('.border-dashed');
dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    document.getElementById('attachments').files = files;
    console.log(`${files.length} arquivo(s) adicionado(s)`);
});
</script>
@endsection











