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
                    <a href="{{ route('request-areas.show', 'registro-reclamacoes') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
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
            <form method="POST" action="{{ route('tickets.store-rrl') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Resumo -->
                <div>
                    <label for="resumo" class="block text-sm font-medium text-gray-700 mb-2">
                        Resumo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="resumo" 
                           name="resumo" 
                           value="{{ old('resumo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nome da marca reclamante / Código do cliente"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Nome da marca reclamante / Código do cliente (exemplo: Arezzo / 1881).
                    </p>
                    @error('resumo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ocorrência -->
                <div>
                    <label for="ocorrencia" class="block text-sm font-medium text-gray-700 mb-2">
                        Ocorrência <span class="text-red-500">*</span>
                    </label>
                    <select id="ocorrencia" 
                            name="ocorrencia"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            required>
                        <option value="">Nenhum</option>
                        <option value="extravio-total" {{ old('ocorrencia') == 'extravio-total' ? 'selected' : '' }}>Extravio Total</option>
                        <option value="extravio-parcial" {{ old('ocorrencia') == 'extravio-parcial' ? 'selected' : '' }}>Extravio Parcial</option>
                        <option value="avaria" {{ old('ocorrencia') == 'avaria' ? 'selected' : '' }}>Avaria</option>
                        <option value="troca-parcial" {{ old('ocorrencia') == 'troca-parcial' ? 'selected' : '' }}>Troca Parcial</option>
                        <option value="troca-total" {{ old('ocorrencia') == 'troca-total' ? 'selected' : '' }}>Troca Total</option>
                        <option value="mau-atendimento" {{ old('ocorrencia') == 'mau-atendimento' ? 'selected' : '' }}>Mau Atendimento</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        Informe o motivo da ocorrência. Obs: o motivo poderá ser alterado/ajustado pela logística ou qualidade, caso necessário.
                    </p>
                    @error('ocorrencia')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Produto -->
                <div>
                    <label for="produto" class="block text-sm font-medium text-gray-700 mb-2">
                        Produto <span class="text-red-500">*</span>
                    </label>
                    <textarea id="produto" 
                              name="produto" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Produto / Tamanho / Código de acabado"
                              required>{{ old('produto') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Produto / Tamanho / Código de acabado (exemplo: Sacola P Anacapri (80.05.000332). Mencionar o(s) item(ns) referente(s) a essa reclamação.
                    </p>
                    @error('produto')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome do Cliente -->
                    <div>
                        <label for="nome_cliente" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Cliente <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nome_cliente" 
                               name="nome_cliente" 
                               value="{{ old('nome_cliente') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome da marca reclamante"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informar o nome da marca reclamante.
                        </p>
                        @error('nome_cliente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Código do Cliente -->
                    <div>
                        <label for="codigo_cliente" class="block text-sm font-medium text-gray-700 mb-2">
                            Código do Cliente <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="codigo_cliente" 
                               name="codigo_cliente" 
                               value="{{ old('codigo_cliente') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Código do cliente"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informar o número do código do cliente cadastrado no metrics.
                        </p>
                        @error('codigo_cliente')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contato -->
                    <div>
                        <label for="contato" class="block text-sm font-medium text-gray-700 mb-2">
                            Contato
                        </label>
                        <input type="text" 
                               id="contato" 
                               name="contato" 
                               value="{{ old('contato') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome do contato">
                        <p class="mt-1 text-xs text-gray-500">
                            Informar nome do contato.
                        </p>
                        @error('contato')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone
                        </label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               value="{{ old('telefone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Número de telefone">
                        <p class="mt-1 text-xs text-gray-500">
                            Informar número de telefone do contato.
                        </p>
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nota Fiscal - Remessa -->
                    <div>
                        <label for="nota_fiscal_remessa" class="block text-sm font-medium text-gray-700 mb-2">
                            Nota Fiscal - Remessa
                        </label>
                        <input type="text" 
                               id="nota_fiscal_remessa" 
                               name="nota_fiscal_remessa" 
                               value="{{ old('nota_fiscal_remessa') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Número da NF de remessa">
                        <p class="mt-1 text-xs text-gray-500">
                            Informar número da nota fiscal de REMESSA.
                        </p>
                        @error('nota_fiscal_remessa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nota Fiscal - Fatura -->
                    <div>
                        <label for="nota_fiscal_fatura" class="block text-sm font-medium text-gray-700 mb-2">
                            Nota Fiscal - Fatura
                        </label>
                        <input type="text" 
                               id="nota_fiscal_fatura" 
                               name="nota_fiscal_fatura" 
                               value="{{ old('nota_fiscal_fatura') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Número da NF de fatura">
                        <p class="mt-1 text-xs text-gray-500">
                            Informar número da nota fiscal de FATURA.
                        </p>
                        @error('nota_fiscal_fatura')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tipo de Reclamação -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        RRL - Tipo de Reclamação
                    </label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="tipo_reclamacao[]" value="entrega" class="form-checkbox text-blue-600" {{ in_array('entrega', old('tipo_reclamacao', [])) ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700">Entrega</span>
                        </label>
                    </div>
                    @error('tipo_reclamacao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Descrição -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição <span class="text-red-500">*</span>
                    </label>
                    <textarea id="descricao" 
                              name="descricao" 
                              rows="6"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descreva a situação com os maiores detalhes possíveis"
                              required>{{ old('descricao') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Descreva a situação com os maiores detalhes possíveis, para que os próximos responsáveis consigam compreender o contexto do problema.
                    </p>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ação Imediata - Financeiro -->
                <div>
                    <label for="acao_imediata_financeiro" class="block text-sm font-medium text-gray-700 mb-2">
                        Ação imediata - Financeiro (opcional)
                    </label>
                    <textarea id="acao_imediata_financeiro" 
                              name="acao_imediata_financeiro" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Informar uma ação imediata ao Financeiro.">{{ old('acao_imediata_financeiro') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Informar uma ação imediata ao Financeiro.
                    </p>
                    @error('acao_imediata_financeiro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Ação Imediata - Logística -->
                <div>
                    <label for="acao_imediata_logistica" class="block text-sm font-medium text-gray-700 mb-2">
                        Ação imediata - Logística (opcional)
                    </label>
                    <textarea id="acao_imediata_logistica" 
                              name="acao_imediata_logistica" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Informar uma ação imediata para a Logística.">{{ old('acao_imediata_logistica') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Informar uma ação imediata para a Logística.
                    </p>
                    @error('acao_imediata_logistica')
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
                    <p class="mt-2 text-sm text-red-600 font-medium">
                        Atenção: Para "Troca Parcial" ou "Troca Total" o campo ANEXO É OBRIGATÓRIO!
                    </p>
                    @error('attachments')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid Campos Finais -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Transportadora -->
                    <div>
                        <label for="transportadora" class="block text-sm font-medium text-gray-700 mb-2">
                            Transportadora (opcional)
                        </label>
                        <input type="text" 
                               id="transportadora" 
                               name="transportadora" 
                               value="{{ old('transportadora') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome da transportadora">
                        <p class="mt-1 text-xs text-gray-500">
                            Informar o nome da transportadora.
                        </p>
                        @error('transportadora')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prioridade -->
                    <div>
                        <label for="prioridade" class="block text-sm font-medium text-gray-700 mb-2">
                            Prioridade (opcional)
                        </label>
                        <select id="prioridade" 
                                name="prioridade"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione...</option>
                            <option value="critica" {{ old('prioridade', 'critica') == 'critica' ? 'selected' : '' }}>Crítica</option>
                            <option value="planejado" {{ old('prioridade') == 'planejado' ? 'selected' : '' }}>Planejado</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Legenda: Planejado (48hs) Crítico (24hs)
                        </p>
                        @error('prioridade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data para Ficar Pronto -->
                    <div>
                        <label for="data_ficar_pronto" class="block text-sm font-medium text-gray-700 mb-2">
                            Data para Ficar Pronto (opcional)
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_ficar_pronto" 
                                   name="data_ficar_pronto"
                                   value="{{ old('data_ficar_pronto') }}"
                                   min="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione a data conforme classificação da Prioridade (Legenda).
                        </p>
                        @error('data_ficar_pronto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'registro-reclamacoes') }}" 
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
            
            // Se for o dropdown de ocorrência, verificar se Troca Parcial ou Total foi selecionada
            if (this.id === 'ocorrencia') {
                const ocorrencia = this.value;
                const anexoField = document.getElementById('attachments');
                const anexoContainer = anexoField.closest('div').parentElement;
                
                if (ocorrencia === 'troca-parcial' || ocorrencia === 'troca-total') {
                    anexoField.setAttribute('required', 'required');
                    // Adicionar aviso visual se ainda não existir
                    let existingWarning = anexoContainer.querySelector('.attachment-warning');
                    if (!existingWarning) {
                        const warningDiv = document.createElement('p');
                        warningDiv.className = 'mt-2 text-sm text-red-600 font-medium attachment-warning';
                        warningDiv.textContent = 'Atenção: O campo ANEXO é obrigatório para esta ocorrência!';
                        anexoContainer.appendChild(warningDiv);
                    }
                } else {
                    anexoField.removeAttribute('required');
                    // Remover aviso se existir
                    const existingWarning = anexoContainer.querySelector('.attachment-warning');
                    if (existingWarning) {
                        existingWarning.remove();
                    }
                }
            }
        });
    }

    // Aplicar em todos os dropdowns
    ['ocorrencia', 'prioridade'].forEach(selectId => {
        setupSearchableDropdown(selectId);
    });

    // Atualizar data mínima baseado na prioridade
    const prioridadeSelect = document.getElementById('prioridade');
    const dataFicarProntoInput = document.getElementById('data_ficar_pronto');
    
    prioridadeSelect?.addEventListener('change', function() {
        const hoje = new Date();
        if (this.value === 'critica') {
            // Crítica: mínimo 1 dia (24hs)
            const minDate = new Date(hoje);
            minDate.setDate(minDate.getDate() + 1);
            dataFicarProntoInput.min = minDate.toISOString().split('T')[0];
        } else if (this.value === 'planejado') {
            // Planejado: mínimo 2 dias (48hs)
            const minDate = new Date(hoje);
            minDate.setDate(minDate.getDate() + 2);
            dataFicarProntoInput.min = minDate.toISOString().split('T')[0];
        }
    });
});
</script>
@endsection

