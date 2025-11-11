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
            <form method="POST" action="{{ route('tickets.store-payroll') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                           placeholder="Descreva resumidamente o motivo do lançamento"
                           required>
                    <p class="mt-1 text-xs text-gray-500">
                        Descreva resumidamente o motivo do lançamento.
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
                            Empresa para envio do lançamento.
                        </p>
                        @error('empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nome do Gestor Solicitante -->
                    <div>
                        <label for="nome_gestor_solicitante" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Gestor Solicitante (opcional)
                        </label>
                        <input type="text" 
                               id="nome_gestor_solicitante" 
                               name="nome_gestor_solicitante" 
                               value="{{ old('nome_gestor_solicitante') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Nome completo do gestor">
                        <p class="mt-1 text-xs text-gray-500">
                            Nome completo do gestor solicitante.
                        </p>
                        @error('nome_gestor_solicitante')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tipo de Lançamento -->
                    <div>
                        <label for="tipo_lancamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Lançamento <span class="text-red-500">*</span>
                        </label>
                        <select id="tipo_lancamento" 
                                name="tipo_lancamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="lancamento-pagar" {{ old('tipo_lancamento') == 'lancamento-pagar' ? 'selected' : '' }}>Lançamento na folha à pagar</option>
                            <option value="lancamento-descontar" {{ old('tipo_lancamento') == 'lancamento-descontar' ? 'selected' : '' }}>Lançamento na folha à descontar</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Tipo do lançamento na folha a ser pago ou descontado. O lançamento deve ser enviado até o dia 22 de cada mês.
                        </p>
                        @error('tipo_lancamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descrição do Lançamento -->
                    <div>
                        <label for="descricao_lancamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Descrição do Lançamento <span class="text-red-500">*</span>
                        </label>
                        <select id="descricao_lancamento" 
                                name="descricao_lancamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="bonus-produtividade" {{ old('descricao_lancamento') == 'bonus-produtividade' ? 'selected' : '' }}>Bônus de Produtividade</option>
                            <option value="comissao-vendas" {{ old('descricao_lancamento') == 'comissao-vendas' ? 'selected' : '' }}>Comissão de Vendas</option>
                            <option value="hora-extra" {{ old('descricao_lancamento') == 'hora-extra' ? 'selected' : '' }}>Hora Extra</option>
                            <option value="adicional-noturno" {{ old('descricao_lancamento') == 'adicional-noturno' ? 'selected' : '' }}>Adicional Noturno</option>
                            <option value="adicional-periculosidade" {{ old('descricao_lancamento') == 'adicional-periculosidade' ? 'selected' : '' }}>Adicional de Periculosidade</option>
                            <option value="vale-refeicao" {{ old('descricao_lancamento') == 'vale-refeicao' ? 'selected' : '' }}>Vale Refeição</option>
                            <option value="vale-transporte" {{ old('descricao_lancamento') == 'vale-transporte' ? 'selected' : '' }}>Vale Transporte</option>
                            <option value="plano-saude" {{ old('descricao_lancamento') == 'plano-saude' ? 'selected' : '' }}>Plano de Saúde</option>
                            <option value="emprestimo-consignado" {{ old('descricao_lancamento') == 'emprestimo-consignado' ? 'selected' : '' }}>Empréstimo Consignado</option>
                            <option value="faltas" {{ old('descricao_lancamento') == 'faltas' ? 'selected' : '' }}>Faltas</option>
                            <option value="atrasos" {{ old('descricao_lancamento') == 'atrasos' ? 'selected' : '' }}>Atrasos</option>
                            <option value="desconto-adiantamento" {{ old('descricao_lancamento') == 'desconto-adiantamento' ? 'selected' : '' }}>Desconto de Adiantamento</option>
                            <option value="outros" {{ old('descricao_lancamento') == 'outros' ? 'selected' : '' }}>Outros</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Descrição de lançamento.
                        </p>
                        @error('descricao_lancamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Período de Lançamento -->
                    <div>
                        <label for="periodo_lancamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Período de Lançamento <span class="text-red-500">*</span>
                        </label>
                        <select id="periodo_lancamento" 
                                name="periodo_lancamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="janeiro" {{ old('periodo_lancamento') == 'janeiro' ? 'selected' : '' }}>Janeiro - para pagamento ou desconto no último dia útil do mês de Janeiro</option>
                            <option value="fevereiro" {{ old('periodo_lancamento') == 'fevereiro' ? 'selected' : '' }}>Fevereiro - para pagamento ou desconto no último dia útil do mês de Fevereiro</option>
                            <option value="marco" {{ old('periodo_lancamento') == 'marco' ? 'selected' : '' }}>Março - para pagamento ou desconto no último dia útil do mês de Março</option>
                            <option value="abril" {{ old('periodo_lancamento') == 'abril' ? 'selected' : '' }}>Abril - para pagamento ou desconto no último dia útil do mês de Abril</option>
                            <option value="maio" {{ old('periodo_lancamento') == 'maio' ? 'selected' : '' }}>Maio - para pagamento ou desconto no último dia útil do mês de Maio</option>
                            <option value="junho" {{ old('periodo_lancamento') == 'junho' ? 'selected' : '' }}>Junho - para pagamento ou desconto no último dia útil do mês de Junho</option>
                            <option value="julho" {{ old('periodo_lancamento') == 'julho' ? 'selected' : '' }}>Julho - para pagamento ou desconto no último dia útil do mês de Julho</option>
                            <option value="agosto" {{ old('periodo_lancamento') == 'agosto' ? 'selected' : '' }}>Agosto - para pagamento ou desconto no último dia útil do mês de Agosto</option>
                            <option value="setembro" {{ old('periodo_lancamento') == 'setembro' ? 'selected' : '' }}>Setembro - para pagamento ou desconto no último dia útil do mês de Setembro</option>
                            <option value="outubro" {{ old('periodo_lancamento') == 'outubro' ? 'selected' : '' }}>Outubro - para pagamento ou desconto no último dia útil do mês de Outubro</option>
                            <option value="novembro" {{ old('periodo_lancamento') == 'novembro' ? 'selected' : '' }}>Novembro - para pagamento ou desconto no último dia útil do mês de Novembro</option>
                            <option value="dezembro" {{ old('periodo_lancamento') == 'dezembro' ? 'selected' : '' }}>Dezembro - para pagamento ou desconto no último dia útil do mês de Dezembro</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Data de início da solicitação.
                        </p>
                        @error('periodo_lancamento')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Anexos (opcional) -->
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
                        Incluir aqui o anexo a ser lançado a pagar ou descontar.
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

<script>
document.addEventListener('DOMContentLoaded', function() {
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
            // Delay para permitir o clique na opção
            setTimeout(() => {
                this.size = 1;
            }, 200);
        });

        // Fechar ao selecionar uma opção
        select.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });

        // Busca por teclado
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

    // Aplicar em todos os dropdowns do formulário
    ['tipo_lancamento', 'descricao_lancamento', 'periodo_lancamento'].forEach(selectId => {
        setupSearchableDropdown(selectId);
    });
});
</script>
@endsection
