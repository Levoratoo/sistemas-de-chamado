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
            <form method="POST" action="{{ route('tickets.store-sample') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Grid de Campos Principais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe o motivo.
                        </p>
                        @error('motivo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cliente -->
                    <div>
                        <label for="cliente" class="block text-sm font-medium text-gray-700 mb-2">
                            Cliente <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cliente" 
                               name="cliente" 
                               value="{{ old('cliente') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe o cliente.
                        </p>
                        @error('cliente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Setor -->
                <div>
                    <label for="setor" class="block text-sm font-medium text-gray-700 mb-2">
                        Setor <span class="text-red-500">*</span>
                    </label>
                    <select id="setor" 
                            name="setor"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Selecione...</option>
                        <option value="almoxarifado" {{ old('setor') == 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                        <option value="comercial" {{ old('setor') == 'comercial' ? 'selected' : '' }}>Comercial</option>
                        <option value="compras" {{ old('setor') == 'compras' ? 'selected' : '' }}>Compras</option>
                        <option value="contabilidade" {{ old('setor') == 'contabilidade' ? 'selected' : '' }}>Contabilidade</option>
                        <option value="controladoria" {{ old('setor') == 'controladoria' ? 'selected' : '' }}>Controladoria</option>
                        <option value="diretoria" {{ old('setor') == 'diretoria' ? 'selected' : '' }}>Diretoria</option>
                        <option value="engenharia" {{ old('setor') == 'engenharia' ? 'selected' : '' }}>Engenharia</option>
                        <option value="expedicao" {{ old('setor') == 'expedicao' ? 'selected' : '' }}>Expedição</option>
                        <option value="faturamento" {{ old('setor') == 'faturamento' ? 'selected' : '' }}>Faturamento</option>
                        <option value="financeiro" {{ old('setor') == 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                        <option value="logistica" {{ old('setor') == 'logistica' ? 'selected' : '' }}>Logística</option>
                        <option value="manutencao" {{ old('setor') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                        <option value="marketing" {{ old('setor') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="orcamento" {{ old('setor') == 'orcamento' ? 'selected' : '' }}>Orçamento</option>
                        <option value="pcp" {{ old('setor') == 'pcp' ? 'selected' : '' }}>PCP</option>
                        <option value="pcm" {{ old('setor') == 'pcm' ? 'selected' : '' }}>PCM</option>
                        <option value="pre-impressao" {{ old('setor') == 'pre-impressao' ? 'selected' : '' }}>Pré-Impressão</option>
                        <option value="producao" {{ old('setor') == 'producao' ? 'selected' : '' }}>Produção</option>
                        <option value="qualidade" {{ old('setor') == 'qualidade' ? 'selected' : '' }}>Qualidade</option>
                        <option value="recepcao" {{ old('setor') == 'recepcao' ? 'selected' : '' }}>Recepção</option>
                        <option value="recursos-humanos" {{ old('setor') == 'recursos-humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                        <option value="seguranca-trabalho" {{ old('setor') == 'seguranca-trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                        <option value="ti" {{ old('setor') == 'ti' ? 'selected' : '' }}>TI</option>
                        <option value="vendas" {{ old('setor') == 'vendas' ? 'selected' : '' }}>Vendas</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        informe seu setor.
                    </p>
                    @error('setor')
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

                <!-- Grid de Campos Opcionais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Medidas -->
                    <div>
                        <label for="medidas" class="block text-sm font-medium text-gray-700 mb-2">
                            Medidas (opcional)
                        </label>
                        <input type="text" 
                               id="medidas" 
                               name="medidas" 
                               value="{{ old('medidas') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('medidas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cor/Impressão -->
                    <div>
                        <label for="cor_impressao" class="block text-sm font-medium text-gray-700 mb-2">
                            Cor/Impressão (opcional)
                        </label>
                        <input type="text" 
                               id="cor_impressao" 
                               name="cor_impressao" 
                               value="{{ old('cor_impressao') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('cor_impressao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acabamento -->
                    <div>
                        <label for="acabamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Acabamento (opcional)
                        </label>
                        <input type="text" 
                               id="acabamento" 
                               name="acabamento" 
                               value="{{ old('acabamento') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('acabamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acondicionamento -->
                    <div>
                        <label for="acondicionamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Acondicionamento (opcional)
                        </label>
                        <input type="text" 
                               id="acondicionamento" 
                               name="acondicionamento" 
                               value="{{ old('acondicionamento') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('acondicionamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Grid de Campos Finais -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Quantidade -->
                    <div>
                        <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="quantidade" 
                               name="quantidade" 
                               value="{{ old('quantidade') }}"
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe a quantidade desejada.
                        </p>
                        @error('quantidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Fornecedor -->
                    <div>
                        <label for="fornecedor" class="block text-sm font-medium text-gray-700 mb-2">
                            Fornecedor (opcional)
                        </label>
                        <input type="text" 
                               id="fornecedor" 
                               name="fornecedor" 
                               value="{{ old('fornecedor') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('fornecedor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Desejada -->
                    <div>
                        <label for="data_desejada" class="block text-sm font-medium text-gray-700 mb-2">
                            Data desejada <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_desejada" 
                                   name="data_desejada"
                                   value="{{ old('data_desejada') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione a data desejada.
                        </p>
                        @error('data_desejada')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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











