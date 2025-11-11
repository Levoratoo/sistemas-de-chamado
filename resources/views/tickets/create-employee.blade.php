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
            <form method="POST" action="{{ route('tickets.store-employee') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Perfil -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Perfil <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="contratacao" 
                                   name="perfil" 
                                   value="contratacao"
                                   {{ old('perfil', 'contratacao') == 'contratacao' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="contratacao" class="ml-2 text-sm text-gray-700">Contratação</label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" 
                                   id="realocacao" 
                                   name="perfil" 
                                   value="realocacao"
                                   {{ old('perfil') == 'realocacao' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                            <label for="realocacao" class="ml-2 text-sm text-gray-700">Realocação</label>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Informe qual o perfil desse novo colaborador.
                    </p>
                    @error('perfil')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nome Completo -->
                <div>
                    <label for="nome_completo" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nome_completo" 
                           name="nome_completo" 
                           value="{{ old('nome_completo') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Informe o nome completo do novo colaborador igual ao RG"
                           required>
                    @error('nome_completo')
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
                            <option value="">Nenhum</option>
                            <option value="administracao-vendas" {{ old('departamento') == 'administracao-vendas' ? 'selected' : '' }}>Administração de Vendas</option>
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

                    <!-- Cargo/Função -->
                    <div>
                        <label for="cargo_funcao" class="block text-sm font-medium text-gray-700 mb-2">
                            Cargo/Função <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select id="cargo_funcao" 
                                    name="cargo_funcao"
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
                            Selecione o cargo para o novo colaborador.
                        </p>
                        @error('cargo_funcao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Empresa -->
                    <div>
                        <label for="empresa" class="block text-sm font-medium text-gray-700 mb-2">
                            Empresa <span class="text-red-500">*</span>
                        </label>
                        <select id="empresa" 
                                name="empresa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Nenhum</option>
                            <option value="printbag-embalagens" {{ old('empresa') == 'printbag-embalagens' ? 'selected' : '' }}>Printbag Embalagens</option>
                            <option value="weisul-agricola" {{ old('empresa') == 'weisul-agricola' ? 'selected' : '' }}>Weisul Agrícola</option>
                            <option value="weisul-participacoes" {{ old('empresa') == 'weisul-participacoes' ? 'selected' : '' }}>Weisul Participações</option>
                            <option value="uw" {{ old('empresa') == 'uw' ? 'selected' : '' }}>UW</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Empresa que pertence.
                        </p>
                        @error('empresa')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data de Início -->
                    <div>
                        <label for="data_inicio" class="block text-sm font-medium text-gray-700 mb-2">
                            Data de Início <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_inicio" 
                                   name="data_inicio"
                                   value="{{ old('data_inicio') }}"
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
                            Estar pronto até a data.
                        </p>
                        @error('data_inicio')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Acessos e Liberações -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Acessos e Liberações (opcional)
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="email" 
                                   name="acessos_liberacoes[]" 
                                   value="email"
                                   {{ in_array('email', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="email" class="ml-2 text-sm text-gray-700">e-mail</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="teams" 
                                   name="acessos_liberacoes[]" 
                                   value="teams"
                                   {{ in_array('teams', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="teams" class="ml-2 text-sm text-gray-700">Teams</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="acesso-remoto" 
                                   name="acessos_liberacoes[]" 
                                   value="acesso-remoto"
                                   {{ in_array('acesso-remoto', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="acesso-remoto" class="ml-2 text-sm text-gray-700">Acesso Remoto</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="whatsapp" 
                                   name="acessos_liberacoes[]" 
                                   value="whatsapp"
                                   {{ in_array('whatsapp', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="whatsapp" class="ml-2 text-sm text-gray-700">Whatsapp</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="youtube-streaming" 
                                   name="acessos_liberacoes[]" 
                                   value="youtube-streaming"
                                   {{ in_array('youtube-streaming', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="youtube-streaming" class="ml-2 text-sm text-gray-700">Youtube/Streaming</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="skype" 
                                   name="acessos_liberacoes[]" 
                                   value="skype"
                                   {{ in_array('skype', old('acessos_liberacoes', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="skype" class="ml-2 text-sm text-gray-700">Skype</label>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Informe quais acessos devem ser liberados para esse perfil.
                    </p>
                </div>

                <!-- Acessos Metrics -->
                <div>
                    <label for="acessos_metrics" class="block text-sm font-medium text-gray-700 mb-2">
                        Acessos Metrics (opcional)
                    </label>
                    <textarea id="acessos_metrics" 
                              name="acessos_metrics" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Quais os acessos Metrics devem ser criados para o perfil. Ex: Estoque - Consulta de Estoque ... Contas a Receber - Emissão de Boletos ... Estoque - Requisições FAB.">{{ old('acessos_metrics') }}</textarea>
                </div>

                <!-- Acessos WK -->
                <div>
                    <label for="acessos_wk" class="block text-sm font-medium text-gray-700 mb-2">
                        Acessos WK (opcional)
                    </label>
                    <textarea id="acessos_wk" 
                              name="acessos_wk" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Quais os acessos WK devem ser criados para o perfil.">{{ old('acessos_wk') }}</textarea>
                </div>

                <!-- Acesso a Pastas da Rede -->
                <div>
                    <label for="acesso_pastas_rede" class="block text-sm font-medium text-gray-700 mb-2">
                        Acesso a Pastas da Rede (opcional)
                    </label>
                    <textarea id="acesso_pastas_rede" 
                              name="acesso_pastas_rede" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Informe o caminho das pastas de acesso a rede que devem ser disponibilizadas e o nível de acesso TOTAL, CONSULTA, EDITAR. Ex: \QUALIDADE - Consultar, \ARTE\CLICHES Total, \FINANCEIRO - Editar">{{ old('acesso_pastas_rede') }}</textarea>
                </div>

                <!-- Outras Necessidades e Acessos -->
                <div>
                    <label for="outras_necessidades" class="block text-sm font-medium text-gray-700 mb-2">
                        Outras Necessidades e Acessos (opcional)
                    </label>
                    <textarea id="outras_necessidades" 
                              name="outras_necessidades" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descreva quais sistemas, acessos e recursos serão necessários para que este colaborador realize suas atividades.">{{ old('outras_necessidades') }}</textarea>
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

<script>
// Busca simples no dropdown de Cargo
document.addEventListener('DOMContentLoaded', function() {
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
