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
            <form method="POST" action="{{ route('tickets.store-medical') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Documento Médico -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Documento Médico <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-6">
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_documento" value="atestado-medico" {{ old('tipo_documento', 'atestado-medico') == 'atestado-medico' ? 'checked' : '' }} class="form-radio text-blue-600" required>
                            <span class="ml-2 text-gray-700">Atestado Médico</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="tipo_documento" value="declaracao-medica" {{ old('tipo_documento') == 'declaracao-medica' ? 'checked' : '' }} class="form-radio text-blue-600">
                            <span class="ml-2 text-gray-700">Declaração Médica</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Selecione o tipo de documento médico.
                    </p>
                    @error('tipo_documento')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome completo do colaborador -->
                <div>
                    <label for="nome_colaborador" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome completo do colaborador <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nome_colaborador" 
                           name="nome_colaborador" 
                           value="{{ old('nome_colaborador') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nome completo"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        O chamado deve ser aberto em até 2 dias úteis da emissão do documento. O documento médico devem ser entregue ao SSMA em até 2 dias úteis da emissão do documento, após a abertura do chamado.
                    </p>
                    @error('nome_colaborador')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                            Selecione o departamento do colaborador.
                        </p>
                        @error('departamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Turno -->
                    <div>
                        <label for="turno" class="block text-sm font-medium text-gray-700 mb-2">
                            Turno
                        </label>
                        <select id="turno" 
                                name="turno"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Nenhum</option>
                            <option value="1-turno" {{ old('turno') == '1-turno' ? 'selected' : '' }}>1º turno</option>
                            <option value="2-turno" {{ old('turno') == '2-turno' ? 'selected' : '' }}>2º turno</option>
                            <option value="3-turno" {{ old('turno') == '3-turno' ? 'selected' : '' }}>3º turno</option>
                            <option value="comercial" {{ old('turno') == 'comercial' ? 'selected' : '' }}>Comercial</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione o turno do colaborador.
                        </p>
                        @error('turno')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Período em Dias -->
                    <div>
                        <label for="periodo_dias" class="block text-sm font-medium text-gray-700 mb-2">
                            Período em Dias (opcional)
                        </label>
                        <input type="number" 
                               id="periodo_dias" 
                               name="periodo_dias" 
                               value="{{ old('periodo_dias') }}"
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Quantidade de dias">
                        <p class="mt-1 text-xs text-gray-500">
                            Se o documento estiver em DIAS, informe a quantidade de dias do afastamento.
                        </p>
                        @error('periodo_dias')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Período em Horas -->
                    <div>
                        <label for="periodo_horas" class="block text-sm font-medium text-gray-700 mb-2">
                            Período em Horas (opcional)
                        </label>
                        <input type="number" 
                               id="periodo_horas" 
                               name="periodo_horas" 
                               value="{{ old('periodo_horas') }}"
                               min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Quantidade de horas">
                        <p class="mt-1 text-xs text-gray-500">
                            Se o documento estiver em HORAS, informe a quantidade de horas do afastamento.
                        </p>
                        @error('periodo_horas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data do documento -->
                    <div>
                        <label for="data_documento" class="block text-sm font-medium text-gray-700 mb-2">
                            Data do documento <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_documento" 
                                   name="data_documento"
                                   value="{{ old('data_documento') }}"
                                   max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Data da emissão do documento médico.
                        </p>
                        @error('data_documento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CID -->
                    <div>
                        <label for="cid" class="block text-sm font-medium text-gray-700 mb-2">
                            CID (opcional)
                        </label>
                        <input type="text" 
                               id="cid" 
                               name="cid" 
                               value="{{ old('cid') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ex: Z00.0">
                        <p class="mt-1 text-xs text-gray-500">
                            Informe a Classificação Internacional de Doença (CID) sempre que constar no documento médico.
                        </p>
                        @error('cid')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome do médico ou dentista -->
                    <div>
                        <label for="nome_medico" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do médico ou dentista (opcional)
                        </label>
                        <input type="text" 
                               id="nome_medico" 
                               name="nome_medico" 
                               value="{{ old('nome_medico') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome completo">
                        <p class="mt-1 text-xs text-gray-500">
                            Informe o nome do médico ou dentista.
                        </p>
                        @error('nome_medico')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Registro do médico ou dentista -->
                    <div>
                        <label for="registro_medico" class="block text-sm font-medium text-gray-700 mb-2">
                            Registro do médico ou dentista. (opcional)
                        </label>
                        <input type="text" 
                               id="registro_medico" 
                               name="registro_medico" 
                               value="{{ old('registro_medico') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="CRM ou CRO">
                        <p class="mt-1 text-xs text-gray-500">
                            Informe número do Conselho Regional de Medicina (CRM) ou Conselho Regional de Odontologia (CRO).
                        </p>
                        @error('registro_medico')
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
                              placeholder="Caso necessário, acrescente mais informações.">{{ old('observacao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Caso necessário, acrescente mais informações.
                    </p>
                    @error('observacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Anexos -->
                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">
                        Anexos
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
document.addEventListener('DOMContentLoaded', function() {
    // Função para configurar dropdown com busca e fechamento automático
    function setupSearchableDropdown(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        const maxSize = Math.min(select.options.length, 10);

        select.addEventListener('focus', function() {
            this.size = maxSize;
        });

        select.addEventListener('blur', function() {
            setTimeout(() => {
                this.size = 1;
            }, 200);
        });

        select.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });
    }

    // Aplicar em todos os dropdowns
    ['departamento', 'turno'].forEach(selectId => {
        setupSearchableDropdown(selectId);
    });
});
</script>
@endsection

