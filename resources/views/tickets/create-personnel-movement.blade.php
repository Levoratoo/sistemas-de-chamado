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
            <form method="POST" action="{{ route('tickets.store-personnel-movement') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <!-- Motivo -->
                <div>
                    <label for="motivo" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo <span class="text-red-500">*</span>
                    </label>
                    <textarea id="motivo" 
                              name="motivo" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descreva resumidamente o motivo da movimentação de pessoal."
                              required>{{ old('motivo') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        Descreva resumidamente o motivo da movimentação de pessoal. (campo obrigatório)
                    </p>
                    @error('motivo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nome do Colaborador -->
                    <div>
                        <label for="nome_colaborador" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome do Colaborador (opcional)
                        </label>
                        <input type="text" 
                               id="nome_colaborador" 
                               name="nome_colaborador" 
                               value="{{ old('nome_colaborador') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            Nome completo do colaborador.
                        </p>
                        @error('nome_colaborador')
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
                            Nome do Gestor Solicitante.
                        </p>
                        @error('nome_gestor_solicitante')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Situação Atual -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Situação Atual</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cargo Atual -->
                        <div>
                            <label for="cargo_atual" class="block text-sm font-medium text-gray-700 mb-2">
                                Cargo Atual
                            </label>
                            <div class="relative">
                                <select id="cargo_atual" 
                                        name="cargo_atual"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Nenhum</option>
                                    @include('tickets.partials.cargo-options')
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o cargo atual do colaborador.
                            </p>
                            @error('cargo_atual')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nível Atual -->
                        <div>
                            <label for="nivel_atual" class="block text-sm font-medium text-gray-700 mb-2">
                                Nível Atual
                            </label>
                            <select id="nivel_atual" 
                                    name="nivel_atual"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Nenhum</option>
                                <option value="nivel-i" {{ old('nivel_atual') == 'nivel-i' ? 'selected' : '' }}>Nível I</option>
                                <option value="nivel-ii" {{ old('nivel_atual') == 'nivel-ii' ? 'selected' : '' }}>Nível II</option>
                                <option value="nivel-iii" {{ old('nivel_atual') == 'nivel-iii' ? 'selected' : '' }}>Nível III</option>
                                <option value="junior" {{ old('nivel_atual') == 'junior' ? 'selected' : '' }}>Nível Júnior</option>
                                <option value="pleno" {{ old('nivel_atual') == 'pleno' ? 'selected' : '' }}>Nível Pleno</option>
                                <option value="senior" {{ old('nivel_atual') == 'senior' ? 'selected' : '' }}>Nível Sênior</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Nível Atual.
                            </p>
                            @error('nivel_atual')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Escala de Trabalho Atual -->
                        <div>
                            <label for="escala_trabalho_atual" class="block text-sm font-medium text-gray-700 mb-2">
                                Escala de Trabalho Atual
                            </label>
                            <input type="text" 
                                   id="escala_trabalho_atual" 
                                   name="escala_trabalho_atual" 
                                   value="{{ old('escala_trabalho_atual') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Qual a escala de trabalho atual do colaborador.
                            </p>
                            @error('escala_trabalho_atual')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Departamento Atual -->
                        <div>
                            <label for="departamento_atual" class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento Atual
                            </label>
                            <select id="departamento_atual" 
                                    name="departamento_atual"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Nenhum</option>
                                <option value="administracao-vendas" {{ old('departamento_atual', 'administracao-vendas') == 'administracao-vendas' ? 'selected' : '' }}>Administração de Vendas</option>
                                <option value="administrativo-producao" {{ old('departamento_atual') == 'administrativo-producao' ? 'selected' : '' }}>Administrativo Produção</option>
                                <option value="alca" {{ old('departamento_atual') == 'alca' ? 'selected' : '' }}>Alça</option>
                                <option value="almoxarifado" {{ old('departamento_atual') == 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                                <option value="comercial-marketing" {{ old('departamento_atual') == 'comercial-marketing' ? 'selected' : '' }}>Comercial e Marketing</option>
                                <option value="compras" {{ old('departamento_atual') == 'compras' ? 'selected' : '' }}>Compras</option>
                                <option value="controladoria" {{ old('departamento_atual') == 'controladoria' ? 'selected' : '' }}>Controladoria</option>
                                <option value="corte-vinco-automatico" {{ old('departamento_atual') == 'corte-vinco-automatico' ? 'selected' : '' }}>Corte Vinco Automático</option>
                                <option value="desbobinadeira" {{ old('departamento_atual') == 'desbobinadeira' ? 'selected' : '' }}>Desbobinadeira</option>
                                <option value="desenvolvimento-tinta" {{ old('departamento_atual') == 'desenvolvimento-tinta' ? 'selected' : '' }}>Desenvolvimento de Tinta</option>
                                <option value="destaque" {{ old('departamento_atual') == 'destaque' ? 'selected' : '' }}>Destaque</option>
                                <option value="embalagem" {{ old('departamento_atual') == 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                                <option value="engenharia" {{ old('departamento_atual') == 'engenharia' ? 'selected' : '' }}>Engenharia</option>
                                <option value="expedicao" {{ old('departamento_atual') == 'expedicao' ? 'selected' : '' }}>Expedição</option>
                                <option value="faturamento" {{ old('departamento_atual') == 'faturamento' ? 'selected' : '' }}>Faturamento</option>
                                <option value="financeiro" {{ old('departamento_atual') == 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                                <option value="fiscal" {{ old('departamento_atual') == 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                                <option value="gofragem" {{ old('departamento_atual') == 'gofragem' ? 'selected' : '' }}>Gofragem</option>
                                <option value="guardanapo-sachet" {{ old('departamento_atual') == 'guardanapo-sachet' ? 'selected' : '' }}>Guardanapo Sachet</option>
                                <option value="guilhotina" {{ old('departamento_atual') == 'guilhotina' ? 'selected' : '' }}>Guilhotina</option>
                                <option value="impressora-5-cores" {{ old('departamento_atual') == 'impressora-5-cores' ? 'selected' : '' }}>Impressora 5 cores</option>
                                <option value="impressora-flexo-feva" {{ old('departamento_atual') == 'impressora-flexo-feva' ? 'selected' : '' }}>Impressora Flexo Feva</option>
                                <option value="impressora-flexografica" {{ old('departamento_atual') == 'impressora-flexografica' ? 'selected' : '' }}>Impressora Flexográfica</option>
                                <option value="impressora-miraflex" {{ old('departamento_atual') == 'impressora-miraflex' ? 'selected' : '' }}>Impressora Miraflex</option>
                                <option value="limpeza-administrativa" {{ old('departamento_atual') == 'limpeza-administrativa' ? 'selected' : '' }}>Limpeza administrativa</option>
                                <option value="manutencao" {{ old('departamento_atual') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                <option value="patch" {{ old('departamento_atual') == 'patch' ? 'selected' : '' }}>Patch</option>
                                <option value="pcp" {{ old('departamento_atual') == 'pcp' ? 'selected' : '' }}>PCP</option>
                                <option value="planejamento" {{ old('departamento_atual') == 'planejamento' ? 'selected' : '' }}>Planejamento</option>
                                <option value="pre-impressao" {{ old('departamento_atual') == 'pre-impressao' ? 'selected' : '' }}>Pré-Impressão</option>
                                <option value="qualidade" {{ old('departamento_atual') == 'qualidade' ? 'selected' : '' }}>Qualidade</option>
                                <option value="recepcao" {{ old('departamento_atual') == 'recepcao' ? 'selected' : '' }}>Recepção</option>
                                <option value="recursos-humanos" {{ old('departamento_atual') == 'recursos-humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                                <option value="saco-fundo-quadrado" {{ old('departamento_atual') == 'saco-fundo-quadrado' ? 'selected' : '' }}>Saco Fundo Quadrado</option>
                                <option value="sacoleira-newport" {{ old('departamento_atual') == 'sacoleira-newport' ? 'selected' : '' }}>Sacoleira Newport</option>
                                <option value="seguranca-trabalho" {{ old('departamento_atual') == 'seguranca-trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                                <option value="tecnologia-informacao" {{ old('departamento_atual') == 'tecnologia-informacao' ? 'selected' : '' }}>Tecnologia Informação</option>
                                <option value="transporte" {{ old('departamento_atual') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                                <option value="venda-varejo" {{ old('departamento_atual') == 'venda-varejo' ? 'selected' : '' }}>Venda Varejo</option>
                                <option value="vendas" {{ old('departamento_atual') == 'vendas' ? 'selected' : '' }}>Vendas</option>
                                <option value="zeladoria" {{ old('departamento_atual') == 'zeladoria' ? 'selected' : '' }}>Zeladoria</option>
                                <option value="weisul-administracao" {{ old('departamento_atual') == 'weisul-administracao' ? 'selected' : '' }}>Weisul - Administração</option>
                                <option value="weisul-direcao-geral" {{ old('departamento_atual') == 'weisul-direcao-geral' ? 'selected' : '' }}>Weisul - Direção Geral</option>
                                <option value="weisul-comercial" {{ old('departamento_atual') == 'weisul-comercial' ? 'selected' : '' }}>Weisul - Comercial</option>
                                <option value="weisul-agro-producao-agricola" {{ old('departamento_atual') == 'weisul-agro-producao-agricola' ? 'selected' : '' }}>Weisul Agro - Produção Agrícola</option>
                                <option value="weisul-agro-manutencao" {{ old('departamento_atual') == 'weisul-agro-manutencao' ? 'selected' : '' }}>Weisul Agro - Manutenção</option>
                                <option value="weisul-agro-administracao" {{ old('departamento_atual') == 'weisul-agro-administracao' ? 'selected' : '' }}>Weisul Agro - Administração</option>
                                <option value="weisul-agro-armazem-safrista-soja" {{ old('departamento_atual') == 'weisul-agro-armazem-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Armazém Safrista Soja</option>
                                <option value="weisul-agro-prod-agric-safrista-soja" {{ old('departamento_atual') == 'weisul-agro-prod-agric-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Prod. Agríc. Safrista Soja</option>
                                <option value="weisul-agro-refeitorio" {{ old('departamento_atual') == 'weisul-agro-refeitorio' ? 'selected' : '' }}>Weisul Agro - Refeitório</option>
                                <option value="weisul-agro-armazem" {{ old('departamento_atual') == 'weisul-agro-armazem' ? 'selected' : '' }}>Weisul Agro - Armazém</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o departamento de trabalho atual do colaborador.
                            </p>
                            @error('departamento_atual')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Salário Atual -->
                        <div>
                            <label for="salario_atual" class="block text-sm font-medium text-gray-700 mb-2">
                                Salário Atual
                            </label>
                            <input type="text" 
                                   id="salario_atual" 
                                   name="salario_atual" 
                                   value="{{ old('salario_atual') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="R$ 0,00">
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o salário atual do colaborador.
                            </p>
                            @error('salario_atual')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Situação Proposta -->
                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Situação Proposta</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Cargo Proposto -->
                        <div>
                            <label for="cargo_proposto" class="block text-sm font-medium text-gray-700 mb-2">
                                Cargo Proposto
                            </label>
                            <div class="relative">
                                <select id="cargo_proposto" 
                                        name="cargo_proposto"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Nenhum</option>
                                    @include('tickets.partials.cargo-options')
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o cargo novo para o colaborador.
                            </p>
                            @error('cargo_proposto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nível Proposto -->
                        <div>
                            <label for="nivel_proposto" class="block text-sm font-medium text-gray-700 mb-2">
                                Nível Proposto
                            </label>
                            <select id="nivel_proposto" 
                                    name="nivel_proposto"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Nenhum</option>
                                <option value="nivel-i" {{ old('nivel_proposto') == 'nivel-i' ? 'selected' : '' }}>Nível I</option>
                                <option value="nivel-ii" {{ old('nivel_proposto') == 'nivel-ii' ? 'selected' : '' }}>Nível II</option>
                                <option value="nivel-iii" {{ old('nivel_proposto') == 'nivel-iii' ? 'selected' : '' }}>Nível III</option>
                                <option value="junior" {{ old('nivel_proposto') == 'junior' ? 'selected' : '' }}>Nível Júnior</option>
                                <option value="pleno" {{ old('nivel_proposto') == 'pleno' ? 'selected' : '' }}>Nível Pleno</option>
                                <option value="senior" {{ old('nivel_proposto') == 'senior' ? 'selected' : '' }}>Nível Sênior</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Nível Proposto.
                            </p>
                            @error('nivel_proposto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Escala de Trabalho Proposto -->
                        <div>
                            <label for="escala_trabalho_proposto" class="block text-sm font-medium text-gray-700 mb-2">
                                Escala de Trabalho Proposto
                            </label>
                            <input type="text" 
                                   id="escala_trabalho_proposto" 
                                   name="escala_trabalho_proposto" 
                                   value="{{ old('escala_trabalho_proposto') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="mt-1 text-xs text-gray-500">
                                Qual a escala de trabalho proposta para o colaborador.
                            </p>
                            @error('escala_trabalho_proposto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Departamento Proposto -->
                        <div>
                            <label for="departamento_proposto" class="block text-sm font-medium text-gray-700 mb-2">
                                Departamento Proposto
                            </label>
                            <select id="departamento_proposto" 
                                    name="departamento_proposto"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Nenhum</option>
                                <option value="administracao-vendas" {{ old('departamento_proposto') == 'administracao-vendas' ? 'selected' : '' }}>Administração de Vendas</option>
                                <option value="administrativo-producao" {{ old('departamento_proposto') == 'administrativo-producao' ? 'selected' : '' }}>Administrativo Produção</option>
                                <option value="alca" {{ old('departamento_proposto') == 'alca' ? 'selected' : '' }}>Alça</option>
                                <option value="almoxarifado" {{ old('departamento_proposto') == 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                                <option value="comercial-marketing" {{ old('departamento_proposto') == 'comercial-marketing' ? 'selected' : '' }}>Comercial e Marketing</option>
                                <option value="compras" {{ old('departamento_proposto') == 'compras' ? 'selected' : '' }}>Compras</option>
                                <option value="controladoria" {{ old('departamento_proposto') == 'controladoria' ? 'selected' : '' }}>Controladoria</option>
                                <option value="corte-vinco-automatico" {{ old('departamento_proposto') == 'corte-vinco-automatico' ? 'selected' : '' }}>Corte Vinco Automático</option>
                                <option value="desbobinadeira" {{ old('departamento_proposto') == 'desbobinadeira' ? 'selected' : '' }}>Desbobinadeira</option>
                                <option value="desenvolvimento-tinta" {{ old('departamento_proposto') == 'desenvolvimento-tinta' ? 'selected' : '' }}>Desenvolvimento de Tinta</option>
                                <option value="destaque" {{ old('departamento_proposto') == 'destaque' ? 'selected' : '' }}>Destaque</option>
                                <option value="embalagem" {{ old('departamento_proposto') == 'embalagem' ? 'selected' : '' }}>Embalagem</option>
                                <option value="engenharia" {{ old('departamento_proposto') == 'engenharia' ? 'selected' : '' }}>Engenharia</option>
                                <option value="expedicao" {{ old('departamento_proposto') == 'expedicao' ? 'selected' : '' }}>Expedição</option>
                                <option value="faturamento" {{ old('departamento_proposto') == 'faturamento' ? 'selected' : '' }}>Faturamento</option>
                                <option value="financeiro" {{ old('departamento_proposto') == 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                                <option value="fiscal" {{ old('departamento_proposto') == 'fiscal' ? 'selected' : '' }}>Fiscal</option>
                                <option value="gofragem" {{ old('departamento_proposto') == 'gofragem' ? 'selected' : '' }}>Gofragem</option>
                                <option value="guardanapo-sachet" {{ old('departamento_proposto') == 'guardanapo-sachet' ? 'selected' : '' }}>Guardanapo Sachet</option>
                                <option value="guilhotina" {{ old('departamento_proposto') == 'guilhotina' ? 'selected' : '' }}>Guilhotina</option>
                                <option value="impressora-5-cores" {{ old('departamento_proposto') == 'impressora-5-cores' ? 'selected' : '' }}>Impressora 5 cores</option>
                                <option value="impressora-flexo-feva" {{ old('departamento_proposto') == 'impressora-flexo-feva' ? 'selected' : '' }}>Impressora Flexo Feva</option>
                                <option value="impressora-flexografica" {{ old('departamento_proposto') == 'impressora-flexografica' ? 'selected' : '' }}>Impressora Flexográfica</option>
                                <option value="impressora-miraflex" {{ old('departamento_proposto') == 'impressora-miraflex' ? 'selected' : '' }}>Impressora Miraflex</option>
                                <option value="limpeza-administrativa" {{ old('departamento_proposto') == 'limpeza-administrativa' ? 'selected' : '' }}>Limpeza administrativa</option>
                                <option value="manutencao" {{ old('departamento_proposto') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                                <option value="patch" {{ old('departamento_proposto') == 'patch' ? 'selected' : '' }}>Patch</option>
                                <option value="pcp" {{ old('departamento_proposto') == 'pcp' ? 'selected' : '' }}>PCP</option>
                                <option value="planejamento" {{ old('departamento_proposto') == 'planejamento' ? 'selected' : '' }}>Planejamento</option>
                                <option value="pre-impressao" {{ old('departamento_proposto') == 'pre-impressao' ? 'selected' : '' }}>Pré-Impressão</option>
                                <option value="qualidade" {{ old('departamento_proposto') == 'qualidade' ? 'selected' : '' }}>Qualidade</option>
                                <option value="recepcao" {{ old('departamento_proposto') == 'recepcao' ? 'selected' : '' }}>Recepção</option>
                                <option value="recursos-humanos" {{ old('departamento_proposto') == 'recursos-humanos' ? 'selected' : '' }}>Recursos Humanos</option>
                                <option value="saco-fundo-quadrado" {{ old('departamento_proposto') == 'saco-fundo-quadrado' ? 'selected' : '' }}>Saco Fundo Quadrado</option>
                                <option value="sacoleira-newport" {{ old('departamento_proposto') == 'sacoleira-newport' ? 'selected' : '' }}>Sacoleira Newport</option>
                                <option value="seguranca-trabalho" {{ old('departamento_proposto') == 'seguranca-trabalho' ? 'selected' : '' }}>Segurança do Trabalho</option>
                                <option value="tecnologia-informacao" {{ old('departamento_proposto') == 'tecnologia-informacao' ? 'selected' : '' }}>Tecnologia Informação</option>
                                <option value="transporte" {{ old('departamento_proposto') == 'transporte' ? 'selected' : '' }}>Transporte</option>
                                <option value="venda-varejo" {{ old('departamento_proposto') == 'venda-varejo' ? 'selected' : '' }}>Venda Varejo</option>
                                <option value="vendas" {{ old('departamento_proposto') == 'vendas' ? 'selected' : '' }}>Vendas</option>
                                <option value="zeladoria" {{ old('departamento_proposto') == 'zeladoria' ? 'selected' : '' }}>Zeladoria</option>
                                <option value="weisul-administracao" {{ old('departamento_proposto') == 'weisul-administracao' ? 'selected' : '' }}>Weisul - Administração</option>
                                <option value="weisul-direcao-geral" {{ old('departamento_proposto') == 'weisul-direcao-geral' ? 'selected' : '' }}>Weisul - Direção Geral</option>
                                <option value="weisul-comercial" {{ old('departamento_proposto') == 'weisul-comercial' ? 'selected' : '' }}>Weisul - Comercial</option>
                                <option value="weisul-agro-producao-agricola" {{ old('departamento_proposto') == 'weisul-agro-producao-agricola' ? 'selected' : '' }}>Weisul Agro - Produção Agrícola</option>
                                <option value="weisul-agro-manutencao" {{ old('departamento_proposto') == 'weisul-agro-manutencao' ? 'selected' : '' }}>Weisul Agro - Manutenção</option>
                                <option value="weisul-agro-administracao" {{ old('departamento_proposto') == 'weisul-agro-administracao' ? 'selected' : '' }}>Weisul Agro - Administração</option>
                                <option value="weisul-agro-armazem-safrista-soja" {{ old('departamento_proposto') == 'weisul-agro-armazem-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Armazém Safrista Soja</option>
                                <option value="weisul-agro-prod-agric-safrista-soja" {{ old('departamento_proposto') == 'weisul-agro-prod-agric-safrista-soja' ? 'selected' : '' }}>Weisul Agro - Prod. Agríc. Safrista Soja</option>
                                <option value="weisul-agro-refeitorio" {{ old('departamento_proposto') == 'weisul-agro-refeitorio' ? 'selected' : '' }}>Weisul Agro - Refeitório</option>
                                <option value="weisul-agro-armazem" {{ old('departamento_proposto') == 'weisul-agro-armazem' ? 'selected' : '' }}>Weisul Agro - Armazém</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o departamento de trabalho novo do colaborador.
                            </p>
                            @error('departamento_proposto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Salário Proposto -->
                        <div>
                            <label for="salario_proposto" class="block text-sm font-medium text-gray-700 mb-2">
                                Salário Proposto
                            </label>
                            <input type="text" 
                                   id="salario_proposto" 
                                   name="salario_proposto" 
                                   value="{{ old('salario_proposto') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="R$ 0,00">
                            <p class="mt-1 text-xs text-gray-500">
                                Qual o salário novo do colaborador.
                            </p>
                            @error('salario_proposto')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Data e Aprovações -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Data da Alteração -->
                    <div>
                        <label for="data_alteracao" class="block text-sm font-medium text-gray-700 mb-2">
                            Data da Alteração da Movimentação
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_alteracao" 
                                   name="data_alteracao"
                                   value="{{ old('data_alteracao') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Data prevista para a movimentação.
                        </p>
                        @error('data_alteracao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Aprovações -->
                    <div>
                        <label for="aprovacoes_search" class="block text-sm font-medium text-gray-700 mb-2">
                            Aprovações
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   id="aprovacoes_search" 
                                   placeholder="Busque um usuário"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <input type="hidden" name="aprovacoes" id="aprovacoes" value="{{ old('aprovacoes') ? json_encode(old('aprovacoes')) : '[]' }}">
                        <div id="aprovacoes_list" class="mt-2 space-y-2"></div>
                        <p class="mt-1 text-xs text-gray-500">
                            Primeira aprovação do RH, Segunda aprovação da Diretoria.
                        </p>
                        @error('aprovacoes')
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
    let usersList = @json($users);
    let selectedUsers = [];
    const aprovacoesSearch = document.getElementById('aprovacoes_search');
    const aprovacoesInput = document.getElementById('aprovacoes');
    const aprovacoesList = document.getElementById('aprovacoes_list');

    // Busca de usuários
    let searchTimeout;
    aprovacoesSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.toLowerCase();
        
        if (searchTerm.length < 2) {
            document.getElementById('users_dropdown')?.remove();
            return;
        }

        searchTimeout = setTimeout(() => {
            const filtered = usersList.filter(user => 
                user.name.toLowerCase().includes(searchTerm) ||
                user.email.toLowerCase().includes(searchTerm)
            ).slice(0, 10);

            showUsersDropdown(filtered);
        }, 300);
    });

    function showUsersDropdown(users) {
        // Remove dropdown anterior
        document.getElementById('users_dropdown')?.remove();

        if (users.length === 0) return;

        const dropdown = document.createElement('div');
        dropdown.id = 'users_dropdown';
        dropdown.className = 'absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto';
        
        users.forEach(user => {
            if (selectedUsers.find(u => u.id === user.id)) return;
            
            const item = document.createElement('div');
            item.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer';
            item.innerHTML = `
                <div class="font-medium">${user.name}</div>
                <div class="text-sm text-gray-500">${user.email}</div>
            `;
            item.addEventListener('click', () => {
                addUser(user);
                aprovacoesSearch.value = '';
                dropdown.remove();
            });
            dropdown.appendChild(item);
        });

        aprovacoesSearch.parentElement.appendChild(dropdown);
    }

    function addUser(user) {
        if (selectedUsers.find(u => u.id === user.id)) return;
        
        selectedUsers.push(user);
        updateAprovacoesList();
        updateHiddenInput();
    }

    function removeUser(userId) {
        selectedUsers = selectedUsers.filter(u => u.id !== userId);
        updateAprovacoesList();
        updateHiddenInput();
    }

    function updateAprovacoesList() {
        aprovacoesList.innerHTML = selectedUsers.map(user => `
            <div class="flex items-center justify-between bg-blue-50 px-3 py-2 rounded-md">
                <div>
                    <div class="font-medium text-sm">${user.name}</div>
                    <div class="text-xs text-gray-500">${user.email}</div>
                </div>
                <button type="button" onclick="removeUserById(${user.id})" class="text-red-500 hover:text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `).join('');
    }

    function updateHiddenInput() {
        aprovacoesInput.value = JSON.stringify(selectedUsers.map(u => u.id));
    }

    window.removeUserById = function(userId) {
        removeUser(userId);
    };

    // Busca no select de Cargo
    const cargoSelects = document.querySelectorAll('#cargo_atual, #cargo_proposto');
    cargoSelects.forEach(cargoSelect => {
        const maxSize = Math.min(cargoSelect.options.length, 10);
        
        cargoSelect.addEventListener('focus', function() {
            this.size = maxSize;
        });

        cargoSelect.addEventListener('blur', function() {
            setTimeout(() => {
                this.size = 1;
            }, 200);
        });

        cargoSelect.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });

        cargoSelect.addEventListener('keypress', function(e) {
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
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        if (!aprovacoesSearch.contains(e.target) && !document.getElementById('users_dropdown')?.contains(e.target)) {
            document.getElementById('users_dropdown')?.remove();
        }
    });
});
</script>
@endsection

