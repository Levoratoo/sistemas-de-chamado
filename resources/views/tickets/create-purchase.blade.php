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
            <form method="POST" action="{{ route('tickets.store-purchase') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            <option value="administracao-vendas" {{ old('centro_custo') == 'administracao-vendas' ? 'selected' : '' }}>Administração de Vendas</option>
                            <option value="administrativo-producao" {{ old('centro_custo') == 'administrativo-producao' ? 'selected' : '' }}>Administrativo Produção</option>
                            <option value="alca" {{ old('centro_custo') == 'alca' ? 'selected' : '' }}>Alça</option>
                            <option value="almoxarifado" {{ old('centro_custo') == 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                            <option value="comercial-marketing" {{ old('centro_custo') == 'comercial-marketing' ? 'selected' : '' }}>Comercial e Marketing</option>
                            <option value="compras" {{ old('centro_custo') == 'compras' ? 'selected' : '' }}>Compras</option>
                            <option value="controladoria" {{ old('centro_custo') == 'controladoria' ? 'selected' : '' }}>Controladoria</option>
                            <option value="corte-vinco-automatico" {{ old('centro_custo') == 'corte-vinco-automatico' ? 'selected' : '' }}>Corte Vinco Automático</option>
                            <option value="desbobinadeira" {{ old('centro_custo') == 'desbobinadeira' ? 'selected' : '' }}>Desbobinadeira</option>
                            <option value="desenvolvimento-tinta" {{ old('centro_custo') == 'desenvolvimento-tinta' ? 'selected' : '' }}>Desenvolvimento de Tinta</option>
                            <option value="destaque" {{ old('centro_custo') == 'destaque' ? 'selected' : '' }}>Destaque</option>
                            <option value="embalagem" {{ old('centro_custo') == 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                            <option value="engenharia" {{ old('centro_custo') == 'engenharia' ? 'selected' : '' }}>Engenharia</option>
                            <option value="expedicao" {{ old('centro_custo') == 'expedicao' ? 'selected' : '' }}>Expedição</option>
                            <option value="faturamento" {{ old('centro_custo') == 'faturamento' ? 'selected' : '' }}>Faturamento</option>
                            <option value="financeiro" {{ old('centro_custo') == 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                            <option value="fiscal" {{ old('centro_custo') == 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                            <option value="gofragem" {{ old('centro_custo') == 'gofragem' ? 'selected' : '' }}>Gofragem</option>
                            <option value="guardanapo-sachet" {{ old('centro_custo') == 'guardanapo-sachet' ? 'selected' : '' }}>Guardanapo Sachet</option>
                            <option value="guilhotina" {{ old('centro_custo') == 'guilhotina' ? 'selected' : '' }}>Guilhotina</option>
                            <option value="impressora-5-cores" {{ old('centro_custo') == 'impressora-5-cores' ? 'selected' : '' }}>Impressora 5 cores</option>
                            <option value="impressora-flexo-feva" {{ old('centro_custo') == 'impressora-flexo-feva' ? 'selected' : '' }}>Impressora Flexo Feva</option>
                            <option value="impressora-flexografica" {{ old('centro_custo') == 'impressora-flexografica' ? 'selected' : '' }}>Impressora Flexográfica</option>
                            <option value="impressora-miraflex" {{ old('centro_custo') == 'impressora-miraflex' ? 'selected' : '' }}>Impressora Miraflex</option>
                            <option value="limpeza-administrativa" {{ old('centro_custo') == 'limpeza-administrativa' ? 'selected' : '' }}>Limpeza administrativa</option>
                            <option value="manutencao" {{ old('centro_custo') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            <option value="patch" {{ old('centro_custo') == 'patch' ? 'selected' : '' }}>Patch</option>
                            <option value="pcp" {{ old('centro_custo') == 'pcp' ? 'selected' : '' }}>PCP</option>
                            <option value="planejamento" {{ old('centro_custo') == 'planejamento' ? 'selected' : '' }}>Planejamento</option>
                            <option value="pre-impressao" {{ old('centro_custo') == 'pre-impressao' ? 'selected' : '' }}>Pré-Impressão</option>
                            <option value="qualidade" {{ old('centro_custo') == 'qualidade' ? 'selected' : '' }}>Qualidade</option>
                            <option value="recepcao" {{ old('centro_custo') == 'recepcao' ? 'selected' : '' }}>Recepção</option>
                            <option value="recursos-humanos" {{ old('centro_custo') == 'recursos-humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                            <option value="saco-fundo-quadrado" {{ old('centro_custo') == 'saco-fundo-quadrado' ? 'selected' : '' }}>Saco Fundo Quadrado</option>
                            <option value="sacoleira-newport" {{ old('centro_custo') == 'sacoleira-newport' ? 'selected' : '' }}>Sacoleira Newport</option>
                            <option value="seguranca-trabalho" {{ old('centro_custo') == 'seguranca-trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                            <option value="tecnologia-informacao" {{ old('centro_custo') == 'tecnologia-informacao' ? 'selected' : '' }}>Tecnologia Informação</option>
                            <option value="transporte" {{ old('centro_custo') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                            <option value="venda-varejo" {{ old('centro_custo') == 'venda-varejo' ? 'selected' : '' }}>Venda Varejo</option>
                            <option value="vendas" {{ old('centro_custo') == 'vendas' ? 'selected' : '' }}>Vendas</option>
                            <option value="zeladoria" {{ old('centro_custo') == 'zeladoria' ? 'selected' : '' }}>Zeladoria</option>
                            <option value="weisul-administracao" {{ old('centro_custo') == 'weisul-administracao' ? 'selected' : '' }}>Weisul - Administração</option>
                            <option value="weisul-direcao-geral" {{ old('centro_custo') == 'weisul-direcao-geral' ? 'selected' : '' }}>Weisul - Direção Geral</option>
                            <option value="weisul-comercial" {{ old('centro_custo') == 'weisul-comercial' ? 'selected' : '' }}>Weisul - Comercial</option>
                            <option value="weisul-agro-producao-agricola" {{ old('centro_custo') == 'weisul-agro-producao-agricola' ? 'selected' : '' }}>Weisul Agro - Produção Agrícola</option>
                            <option value="weisul-agro-manutencao" {{ old('centro_custo') == 'weisul-agro-manutencao' ? 'selected' : '' }}>Weisul Agro - Manutenção</option>
                            <option value="weisul-agro-administracao" {{ old('centro_custo') == 'weisul-agro-administracao' ? 'selected' : '' }}>Weisul Agro - Administração</option>
                            <option value="weisul-agro-armazem-safrista-soja" {{ old('centro_custo') == 'weisul-agro-armazem-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Armazém Safrista Soja</option>
                            <option value="weisul-agro-prod-agric-safrista-soja" {{ old('centro_custo') == 'weisul-agro-prod-agric-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Prod. Agríc. Safrista Soja</option>
                            <option value="weisul-agro-refeitorio" {{ old('centro_custo') == 'weisul-agro-refeitorio' ? 'selected' : '' }}>Weisul Agro - Refeitório</option>
                            <option value="weisul-agro-armazem" {{ old('centro_custo') == 'weisul-agro-armazem' ? 'selected' : '' }}>Weisul Agro - Armazém</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione o centro de custo.
                        </p>
                        @error('centro_custo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                            Selecione o seu setor.
                        </p>
                        @error('setor')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Descrição Item -->
                <div>
                    <label for="descricao_item" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição Item <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="descricao_item" 
                           name="descricao_item" 
                           value="{{ old('descricao_item') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           required>
                    @error('descricao_item')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos Opcionais -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Código Item -->
                    <div>
                        <label for="codigo_item" class="block text-sm font-medium text-gray-700 mb-2">
                            Código item (opcional)
                        </label>
                        <input type="text" 
                               id="codigo_item" 
                               name="codigo_item" 
                               value="{{ old('codigo_item') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('codigo_item')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantidade -->
                    <div>
                        <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade (opcional)
                        </label>
                        <input type="number" 
                               id="quantidade" 
                               name="quantidade" 
                               value="{{ old('quantidade') }}"
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Informe a quantidade desejada.
                        </p>
                        @error('quantidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Desejada -->
                    <div>
                        <label for="data_desejada" class="block text-sm font-medium text-gray-700 mb-2">
                            Data desejada (opcional)
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_desejada" 
                                   name="data_desejada"
                                   value="{{ old('data_desejada') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe a data desejada.
                        </p>
                        @error('data_desejada')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Aprovador -->
                <div>
                    <label for="aprovador" class="block text-sm font-medium text-gray-700 mb-2">
                        Aprovador
                    </label>
                    <input type="text" 
                           id="aprovador" 
                           name="aprovador" 
                           value="{{ old('aprovador') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Busque um usuário">
                    <p class="mt-1 text-xs text-gray-500">
                        Coordenadores e gerentes.
                    </p>
                    @error('aprovador')
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

