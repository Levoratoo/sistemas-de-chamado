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
            <form method="POST" action="{{ route('tickets.store-job-opening') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                           placeholder="Título da solicitação."
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Título da solicitação.
                    </p>
                    @error('titulo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Motivo da Admissão -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo da Admissão <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-6">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="motivo_admissao" 
                                   value="aumento-quadro" 
                                   {{ old('motivo_admissao', 'aumento-quadro') == 'aumento-quadro' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">Aumento de quadro</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="motivo_admissao" 
                                   value="substituicao" 
                                   {{ old('motivo_admissao') == 'substituicao' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">Substituição</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Motivo da Admissão.
                    </p>
                    @error('motivo_admissao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome do Colaborador Substituído (condicional) -->
                <div id="colaborador-substituido" style="display: {{ old('motivo_admissao') == 'substituicao' ? 'block' : 'none' }};">
                    <label for="nome_colaborador_substituido" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome do Colaborador Substituido (opcional)
                    </label>
                    <input type="text" 
                           id="nome_colaborador_substituido" 
                           name="nome_colaborador_substituido" 
                           value="{{ old('nome_colaborador_substituido') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">
                        Em caso de substituição informa o colaborador.
                    </p>
                    @error('nome_colaborador_substituido')
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
                            Empresa que ira atuar.
                        </p>
                        @error('empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Recrutamento -->
                    <div>
                        <label for="tipo_recrutamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Recrutamento <span class="text-red-500">*</span>
                        </label>
                        <select id="tipo_recrutamento" 
                                name="tipo_recrutamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="externo" {{ old('tipo_recrutamento', 'externo') == 'externo' ? 'selected' : '' }}>Externo</option>
                            <option value="interno" {{ old('tipo_recrutamento') == 'interno' ? 'selected' : '' }}>Interno</option>
                            <option value="misto" {{ old('tipo_recrutamento') == 'misto' ? 'selected' : '' }}>Misto</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Tipo de Recrutamento.
                        </p>
                        @error('tipo_recrutamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Contrato -->
                    <div>
                        <label for="tipo_contrato" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Contrato <span class="text-red-500">*</span>
                        </label>
                        <select id="tipo_contrato" 
                                name="tipo_contrato"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="clt" {{ old('tipo_contrato', 'clt') == 'clt' ? 'selected' : '' }}>CLT</option>
                            <option value="pj" {{ old('tipo_contrato') == 'pj' ? 'selected' : '' }}>PJ</option>
                            <option value="estagio" {{ old('tipo_contrato') == 'estagio' ? 'selected' : '' }}>Estágio</option>
                            <option value="jovem-aprendiz" {{ old('tipo_contrato') == 'jovem-aprendiz' ? 'selected' : '' }}>Jovem Aprendiz</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Tipo de Contrato.
                        </p>
                        @error('tipo_contrato')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Departamento -->
                    <div>
                        <label for="departamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Departamento <span class="text-red-500">*</span>
                        </label>
                        <select id="departamento" 
                                name="departamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="administracao-vendas" {{ old('departamento', 'administracao-vendas') == 'administracao-vendas' ? 'selected' : '' }}>Administração de Vendas</option>
                            <option value="administrativo-producao" {{ old('departamento') == 'administrativo-producao' ? 'selected' : '' }}>Administrativo Produção</option>
                            <option value="alca" {{ old('departamento') == 'alca' ? 'selected' : '' }}>Alça</option>
                            <option value="almoxarifado" {{ old('departamento') == 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                            <option value="comercial-marketing" {{ old('departamento') == 'comercial-marketing' ? 'selected' : '' }}>Comercial e Marketing</option>
                            <option value="compras" {{ old('departamento') == 'compras' ? 'selected' : '' }}>Compras</option>
                            <option value="controladoria" {{ old('departamento') == 'controladoria' ? 'selected' : '' }}>Controladoria</option>
                            <option value="corte-vinco-automatico" {{ old('departamento') == 'corte-vinco-automatico' ? 'selected' : '' }}>Corte Vinco Automático</option>
                            <option value="desbobinadeira" {{ old('departamento') == 'desbobinadeira' ? 'selected' : '' }}>Desbobinadeira</option>
                            <option value="desenvolvimento-tinta" {{ old('departamento') == 'desenvolvimento-tinta' ? 'selected' : '' }}>Desenvolvimento de Tinta</option>
                            <option value="destaque" {{ old('departamento') == 'destaque' ? 'selected' : '' }}>Destaque</option>
                            <option value="embalagem" {{ old('departamento') == 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                            <option value="engenharia" {{ old('departamento') == 'engenharia' ? 'selected' : '' }}>Engenharia</option>
                            <option value="expedicao" {{ old('departamento') == 'expedicao' ? 'selected' : '' }}>Expedição</option>
                            <option value="faturamento" {{ old('departamento') == 'faturamento' ? 'selected' : '' }}>Faturamento</option>
                            <option value="financeiro" {{ old('departamento') == 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                            <option value="fiscal" {{ old('departamento') == 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                            <option value="gofragem" {{ old('departamento') == 'gofragem' ? 'selected' : '' }}>Gofragem</option>
                            <option value="guardanapo-sachet" {{ old('departamento') == 'guardanapo-sachet' ? 'selected' : '' }}>Guardanapo Sachet</option>
                            <option value="guilhotina" {{ old('departamento') == 'guilhotina' ? 'selected' : '' }}>Guilhotina</option>
                            <option value="impressora-5-cores" {{ old('departamento') == 'impressora-5-cores' ? 'selected' : '' }}>Impressora 5 cores</option>
                            <option value="impressora-flexo-feva" {{ old('departamento') == 'impressora-flexo-feva' ? 'selected' : '' }}>Impressora Flexo Feva</option>
                            <option value="impressora-flexografica" {{ old('departamento') == 'impressora-flexografica' ? 'selected' : '' }}>Impressora Flexográfica</option>
                            <option value="impressora-miraflex" {{ old('departamento') == 'impressora-miraflex' ? 'selected' : '' }}>Impressora Miraflex</option>
                            <option value="limpeza-administrativa" {{ old('departamento') == 'limpeza-administrativa' ? 'selected' : '' }}>Limpeza administrativa</option>
                            <option value="manutencao" {{ old('departamento') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            <option value="patch" {{ old('departamento') == 'patch' ? 'selected' : '' }}>Patch</option>
                            <option value="pcp" {{ old('departamento') == 'pcp' ? 'selected' : '' }}>PCP</option>
                            <option value="planejamento" {{ old('departamento') == 'planejamento' ? 'selected' : '' }}>Planejamento</option>
                            <option value="pre-impressao" {{ old('departamento') == 'pre-impressao' ? 'selected' : '' }}>Pré-Impressão</option>
                            <option value="qualidade" {{ old('departamento') == 'qualidade' ? 'selected' : '' }}>Qualidade</option>
                            <option value="recepcao" {{ old('departamento') == 'recepcao' ? 'selected' : '' }}>Recepção</option>
                            <option value="recursos-humanos" {{ old('departamento') == 'recursos-humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                            <option value="saco-fundo-quadrado" {{ old('departamento') == 'saco-fundo-quadrado' ? 'selected' : '' }}>Saco Fundo Quadrado</option>
                            <option value="sacoleira-newport" {{ old('departamento') == 'sacoleira-newport' ? 'selected' : '' }}>Sacoleira Newport</option>
                            <option value="seguranca-trabalho" {{ old('departamento') == 'seguranca-trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                            <option value="tecnologia-informacao" {{ old('departamento') == 'tecnologia-informacao' ? 'selected' : '' }}>Tecnologia Informação</option>
                            <option value="transporte" {{ old('departamento') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                            <option value="venda-varejo" {{ old('departamento') == 'venda-varejo' ? 'selected' : '' }}>Venda Varejo</option>
                            <option value="vendas" {{ old('departamento') == 'vendas' ? 'selected' : '' }}>Vendas</option>
                            <option value="zeladoria" {{ old('departamento') == 'zeladoria' ? 'selected' : '' }}>Zeladoria</option>
                            <option value="weisul-administracao" {{ old('departamento') == 'weisul-administracao' ? 'selected' : '' }}>Weisul - Administração</option>
                            <option value="weisul-direcao-geral" {{ old('departamento') == 'weisul-direcao-geral' ? 'selected' : '' }}>Weisul - Direção Geral</option>
                            <option value="weisul-comercial" {{ old('departamento') == 'weisul-comercial' ? 'selected' : '' }}>Weisul - Comercial</option>
                            <option value="weisul-agro-producao-agricola" {{ old('departamento') == 'weisul-agro-producao-agricola' ? 'selected' : '' }}>Weisul Agro - Produção Agrícola</option>
                            <option value="weisul-agro-manutencao" {{ old('departamento') == 'weisul-agro-manutencao' ? 'selected' : '' }}>Weisul Agro - Manutenção</option>
                            <option value="weisul-agro-administracao" {{ old('departamento') == 'weisul-agro-administracao' ? 'selected' : '' }}>Weisul Agro - Administração</option>
                            <option value="weisul-agro-armazem-safrista-soja" {{ old('departamento') == 'weisul-agro-armazem-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Armazém Safrista Soja</option>
                            <option value="weisul-agro-prod-agric-safrista-soja" {{ old('departamento') == 'weisul-agro-prod-agric-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Prod. Agríc. Safrista Soja</option>
                            <option value="weisul-agro-refeitorio" {{ old('departamento') == 'weisul-agro-refeitorio' ? 'selected' : '' }}>Weisul Agro - Refeitório</option>
                            <option value="weisul-agro-armazem" {{ old('departamento') == 'weisul-agro-armazem' ? 'selected' : '' }}>Weisul Agro - Armazém</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe em qual departamento o colaborador irá trabalhar.
                        </p>
                        @error('departamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cargo -->
                    <div>
                        <label for="cargo" class="block text-sm font-medium text-gray-700 mb-2">
                            Cargo <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="cargo" 
                                    name="cargo"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <option value="">Selecione o cargo...</option>
                                @include('tickets.partials.cargo-options')
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione o cargo para a vaga.
                        </p>
                        @error('cargo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Escalas de trabalho -->
                    <div>
                        <label for="escala_trabalho" class="block text-sm font-medium text-gray-700 mb-2">
                            Escalas de trabalho <span class="text-red-500">*</span>
                        </label>
                        <select id="escala_trabalho" 
                                name="escala_trabalho"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="seg-sex" {{ old('escala_trabalho') == 'seg-sex' ? 'selected' : '' }}>Segunda a Sexta</option>
                            <option value="seg-sab" {{ old('escala_trabalho') == 'seg-sab' ? 'selected' : '' }}>Segunda a Sábado</option>
                            <option value="2x1" {{ old('escala_trabalho') == '2x1' ? 'selected' : '' }}>2x1 (12h/36h)</option>
                            <option value="4x2" {{ old('escala_trabalho') == '4x2' ? 'selected' : '' }}>4x2</option>
                            <option value="6x1" {{ old('escala_trabalho') == '6x1' ? 'selected' : '' }}>6x1</option>
                            <option value="outro" {{ old('escala_trabalho', 'outro') == 'outro' ? 'selected' : '' }}>Outro</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Caso selecionar "Outro", Informar a escala de trabalho desejada na Observação.
                        </p>
                        @error('escala_trabalho')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Prevista da Contratação -->
                    <div>
                        <label for="data_prevista_contratacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Prevista da Contratação (opcional)
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_prevista_contratacao" 
                                   name="data_prevista_contratacao"
                                   value="{{ old('data_prevista_contratacao') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Data Prevista da Contração.
                        </p>
                        @error('data_prevista_contratacao')
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
                              placeholder="Em caso de mais informações informai aqui.">{{ old('observacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Em caso de mais informações informai aqui.
                    </p>
                    @error('observacao')
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

<script>
// Mostrar/ocultar campo de colaborador substituído baseado no motivo da admissão
document.querySelectorAll('input[name="motivo_admissao"]').forEach(radio => {
    radio.addEventListener('change', function() {
        const colaboradorField = document.getElementById('colaborador-substituido');
        if (this.value === 'substituicao') {
            colaboradorField.style.display = 'block';
        } else {
            colaboradorField.style.display = 'none';
            document.getElementById('nome_colaborador_substituido').value = '';
        }
    });
});

// Busca simples no dropdown de Cargo
document.addEventListener('DOMContentLoaded', function() {
    const motivoSelected = document.querySelector('input[name="motivo_admissao"]:checked');
    if (motivoSelected) {
        motivoSelected.dispatchEvent(new Event('change'));
    }

    // Busca no select de Cargo - funciona para todos os selects com id cargo ou cargo_funcao
    const cargoSelects = document.querySelectorAll('#cargo, #cargo_funcao');
    cargoSelects.forEach(cargoSelect => {
        const maxSize = Math.min(cargoSelect.options.length, 10);
        
        cargoSelect.addEventListener('focus', function() {
            this.size = maxSize; // Mostrar múltiplas opções ao focar
        });

        cargoSelect.addEventListener('blur', function() {
            setTimeout(() => {
                this.size = 1; // Voltar ao tamanho normal
            }, 200);
        });

        cargoSelect.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });

        cargoSelect.addEventListener('keypress', function(e) {
            // Permitir busca digitando
            const searchText = String.fromCharCode(e.which).toLowerCase();
            const options = Array.from(this.options);
            
            // Encontrar próxima opção que começa com a letra digitada
            let foundIndex = -1;
            for (let i = this.selectedIndex + 1; i < options.length; i++) {
                if (options[i].text.toLowerCase().startsWith(searchText)) {
                    foundIndex = i;
                    break;
                }
            }
            
            // Se não encontrou, procurar do início
            if (foundIndex === -1) {
                for (let i = 0; i < this.selectedIndex; i++) {
                    if (options[i].text.toLowerCase().startsWith(searchText)) {
                        foundIndex = i;
                        break;
                    }
                }
            }
            
            if (foundIndex !== -1) {
                this.selectedIndex = foundIndex;
                this.scrollTop = options[foundIndex].offsetTop - this.offsetTop;
            }
        });
    });
});
</script>
@endsection
