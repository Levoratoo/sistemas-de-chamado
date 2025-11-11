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
            <form method="POST" action="{{ route('tickets.store-vacation') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Motivo -->
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <textarea id="motivo" 
                              name="motivo" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descreva resumidamente o motivo da solicitação"
                              required>{{ old('motivo') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Descreva resumidamente o motivo da solicitação.
                    </p>
                    @error('motivo')
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
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Nome completo do colaborador que terá movimentação.
                        </p>
                        @error('nome_colaborador')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Período Aquisitivo -->
                    <div>
                        <label for="periodo_aquisitivo" class="block text-sm font-medium text-gray-700 mb-2">
                            Período Aquisitivo <span class="text-red-500">*</span>
                        </label>
                        <select id="periodo_aquisitivo" 
                                name="periodo_aquisitivo"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            @php
                                $anoAtual = date('Y');
                                $anoInicio = $anoAtual - 2;
                                $anoFim = $anoAtual + 3;
                            @endphp
                            @for($ano = $anoInicio; $ano <= $anoFim; $ano++)
                                <option value="{{ $ano }} - {{ $ano + 1 }}" {{ old('periodo_aquisitivo', ($ano == $anoAtual - 1 ? ($ano . ' - ' . ($ano + 1)) : '')) == ($ano . ' - ' . ($ano + 1)) ? 'selected' : '' }}>{{ $ano }} - {{ $ano + 1 }}</option>
                            @endfor
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Escolher qual o período aquisitivo das férias do colaborador.
                        </p>
                        @error('periodo_aquisitivo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quantidade de Férias -->
                    <div>
                        <label for="quantidade_ferias" class="block text-sm font-medium text-gray-700 mb-2">
                            Quantidade de Férias <span class="text-red-500">*</span>
                        </label>
                        <select id="quantidade_ferias" 
                                name="quantidade_ferias"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="30 dias" {{ old('quantidade_ferias', '30 dias') == '30 dias' ? 'selected' : '' }}>30 dias</option>
                            <option value="20 dias" {{ old('quantidade_ferias') == '20 dias' ? 'selected' : '' }}>20 dias</option>
                            <option value="15 dias" {{ old('quantidade_ferias') == '15 dias' ? 'selected' : '' }}>15 dias</option>
                            <option value="10 dias" {{ old('quantidade_ferias') == '10 dias' ? 'selected' : '' }}>10 dias</option>
                            <option value="5 dias" {{ old('quantidade_ferias') == '5 dias' ? 'selected' : '' }}>5 dias</option>
                            <option value="Abono pecuniário" {{ old('quantidade_ferias') == 'Abono pecuniário' ? 'selected' : '' }}>Abono pecuniário</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Assinalar a quantidade de dias de férias considerando a legalidade: 20 dias + 10 dias, 15 dias + 15 dias, 15 dias + 10 dias + 5 dias. Abono pecuniário somente com aprovação da Diretoria.
                        </p>
                        @error('quantidade_ferias')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Início das férias -->
                    <div>
                        <label for="data_inicio_ferias" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Início das férias <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_inicio_ferias" 
                                   name="data_inicio_ferias"
                                   value="{{ old('data_inicio_ferias') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Descrever o período de férias no período. Obs: o início das férias deve ser considerado entre segunda e quinta-feira conforme escala de trabalho. Quando houver feriado o período de início devem anteceder pelo menos 2 dias.
                        </p>
                        @error('data_inicio_ferias')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data fim das férias -->
                    <div>
                        <label for="data_fim_ferias" class="block text-sm font-medium text-gray-700 mb-2">
                            Data fim das férias <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_fim_ferias" 
                                   name="data_fim_ferias"
                                   value="{{ old('data_fim_ferias') }}"
                                   min="{{ date('Y-m-d', strtotime('+2 days')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Descrever o período de férias no período.
                        </p>
                        @error('data_fim_ferias')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
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
    const dataInicio = document.getElementById('data_inicio_ferias');
    const dataFim = document.getElementById('data_fim_ferias');

    // Atualizar min da data fim quando a data início mudar
    dataInicio.addEventListener('change', function() {
        if (this.value) {
            const minDate = new Date(this.value);
            minDate.setDate(minDate.getDate() + 1);
            dataFim.min = minDate.toISOString().split('T')[0];
            
            // Se a data fim for menor que a nova mínima, limpar
            if (dataFim.value && dataFim.value <= this.value) {
                dataFim.value = '';
            }
        }
    });

    // Validar que a data início seja segunda ou quinta (opcional, apenas alerta)
    dataInicio.addEventListener('change', function() {
        if (this.value) {
            const date = new Date(this.value);
            const dayOfWeek = date.getDay(); // 0 = domingo, 1 = segunda, ..., 4 = quinta
            
            if (dayOfWeek !== 1 && dayOfWeek !== 2 && dayOfWeek !== 3 && dayOfWeek !== 4) {
                alert('Atenção: O início das férias deve ser considerado entre segunda e quinta-feira conforme escala de trabalho.');
            }
        }
    });

    // Função para configurar dropdown com busca e fechamento automático
    function setupSearchableDropdown(selectId) {
        const select = document.getElementById(selectId);
        if (!select) return;

        // Calcular tamanho máximo baseado no número de opções (máximo 10)
        const maxSize = Math.min(select.options.length, 10);

        select.addEventListener('focus', function() {
            this.size = maxSize;
        });

        select.addEventListener('blur', function() {
            setTimeout(() => {
                this.size = 1;
            }, 200);
        });

        // Fechar ao selecionar uma opção
        select.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });

        select.addEventListener('keypress', function(e) {
            const searchText = String.fromCharCode(e.which).toLowerCase();
            const options = Array.from(this.options);
            
            let foundIndex = -1;
            for (let i = this.selectedIndex + 1; i < options.length; i++) {
                if (options[i].text.toLowerCase().startsWith(searchText)) {
                    foundIndex = i;
                    break;
                }
            }
            
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
    }

    // Aplicar em todos os selects
    ['periodo_aquisitivo', 'quantidade_ferias'].forEach(selectId => {
        setupSearchableDropdown(selectId);
    });
});
</script>
@endsection

