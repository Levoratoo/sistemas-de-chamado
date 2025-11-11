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
            <form method="POST" action="{{ route('tickets.store-replacement') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                           placeholder="De forma reduzida, especifique seu problema"
                           required>
                    @error('titulo')
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
                        <p class="mt-1 text-xs text-gray-500">
                            Insira prints de tela, pdf ou arquivos que referenciem o problema que você está tendo.
                        </p>
                    </div>
                    @error('attachments')
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
                              placeholder="Descreva de forma detalhada a sua necessidade"
                              required>{{ old('descricao') }}</textarea>
                    @error('descricao')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Aprovadores -->
                    <div>
                        <label for="aprovadores" class="block text-sm font-medium text-gray-700 mb-2">
                            Aprovadores
                        </label>
                        <input type="text" 
                               id="aprovadores" 
                               name="aprovadores" 
                               value="{{ old('aprovadores') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Busque um usuário">
                        <p class="mt-1 text-xs text-gray-500">
                            Defina o superior de sua área que fará a aprovação desta solicitação.
                        </p>
                        @error('aprovadores')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Impacto -->
                    <div>
                        <label for="impacto" class="block text-sm font-medium text-gray-700 mb-2">
                            Impacto <span class="text-red-500">*</span>
                        </label>
                        <select id="impacto" 
                                name="impacto"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="baixo" {{ old('impacto', 'baixo') == 'baixo' ? 'selected' : '' }}>Baixo</option>
                            <option value="medio" {{ old('impacto') == 'medio' ? 'selected' : '' }}>Médio</option>
                            <option value="alto" {{ old('impacto') == 'alto' ? 'selected' : '' }}>Alto</option>
                            <option value="critico" {{ old('impacto') == 'critico' ? 'selected' : '' }}>Crítico</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Defina o impacto que este problema causa no seu dia a dia.
                        </p>
                        @error('impacto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Necessidade -->
                    <div>
                        <label for="necessidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Necessidade (opcional)
                        </label>
                        <select id="necessidade" 
                                name="necessidade"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Nenhum</option>
                            <option value="substituicao" {{ old('necessidade') == 'substituicao' ? 'selected' : '' }}>Substituição</option>
                            <option value="aquisicao" {{ old('necessidade') == 'aquisicao' ? 'selected' : '' }}>Aquisição</option>
                            <option value="adequacao" {{ old('necessidade') == 'adequacao' ? 'selected' : '' }}>Adequação</option>
                            <option value="manutencao" {{ old('necessidade') == 'manutencao' ? 'selected' : '' }}>Manutenção</option>
                            <option value="upgrade" {{ old('necessidade') == 'upgrade' ? 'selected' : '' }}>Upgrade</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Selecione o tipo de necessidade que gerou esta demanda.
                        </p>
                    </div>

                    <!-- Data para Ficar Pronto -->
                    <div>
                        <label for="data_para_ficar_pronto" class="block text-sm font-medium text-gray-700 mb-2">
                            Data para Ficar Pronto
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_para_ficar_pronto" 
                                   name="data_para_ficar_pronto"
                                   value="{{ old('data_para_ficar_pronto') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            Espero que essa requisição esteja finalizada até essa data.
                        </p>
                        @error('data_para_ficar_pronto')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Departamento -->
                    <div>
                        <label for="departamento" class="block text-sm font-medium text-gray-700 mb-2">
                            Departamento
                        </label>
                        <select id="departamento" 
                                name="departamento"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <option value="impressora-flexografica" {{ old('departamento') == 'impressora-flexografica' ? 'selected' : '' }}>Impressora Flexografica</option>
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
                            Para qual departamento é essa solicitação?
                        </p>
                    </div>

                    <!-- Cargo/Função -->
                    <div>
                        <label for="cargo_funcao" class="block text-sm font-medium text-gray-700 mb-2">
                            Cargo/Função
                        </label>
                        <div class="relative">
                            <select id="cargo_funcao" 
                                    name="cargo_funcao"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            Para qual cargo ou função é essa solicitação?
                        </p>
                    </div>

                    <!-- Empresa -->
                    <div>
                        <label for="empresa" class="block text-sm font-medium text-gray-700 mb-2">
                            Empresa (opcional)
                        </label>
                        <select id="empresa" 
                                name="empresa"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Nenhum</option>
                            <option value="printbag-embalagens" {{ old('empresa') == 'printbag-embalagens' ? 'selected' : '' }}>Printbag Embalagens</option>
                            <option value="weisul-agricola" {{ old('empresa') == 'weisul-agricola' ? 'selected' : '' }}>Weisul Agrícola</option>
                            <option value="weisul-participacoes" {{ old('empresa') == 'weisul-participacoes' ? 'selected' : '' }}>Weisul Participações</option>
                            <option value="uw" {{ old('empresa') == 'uw' ? 'selected' : '' }}>UW</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Empresa que pertence.
                        </p>
                    </div>
                </div>

                <!-- Equipamentos Novos -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Equipamentos Novos (opcional)
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="notebook" 
                                   name="equipamentos_novos[]" 
                                   value="notebook"
                                   {{ in_array('notebook', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="notebook" class="ml-2 text-sm text-gray-700">Notebook</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="computador-mesa-completo" 
                                   name="equipamentos_novos[]" 
                                   value="computador-mesa-completo"
                                   {{ in_array('computador-mesa-completo', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="computador-mesa-completo" class="ml-2 text-sm text-gray-700">Computador de Mesa Completo</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="ramal-aparelho" 
                                   name="equipamentos_novos[]" 
                                   value="ramal-aparelho"
                                   {{ in_array('ramal-aparelho', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="ramal-aparelho" class="ml-2 text-sm text-gray-700">Ramal/Aparelho</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="celular-com-chip" 
                                   name="equipamentos_novos[]" 
                                   value="celular-com-chip"
                                   {{ in_array('celular-com-chip', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="celular-com-chip" class="ml-2 text-sm text-gray-700">Celular com chip</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="celular-sem-chip" 
                                   name="equipamentos_novos[]" 
                                   value="celular-sem-chip"
                                   {{ in_array('celular-sem-chip', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="celular-sem-chip" class="ml-2 text-sm text-gray-700">Celular sem chip</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="chip-celular" 
                                   name="equipamentos_novos[]" 
                                   value="chip-celular"
                                   {{ in_array('chip-celular', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="chip-celular" class="ml-2 text-sm text-gray-700">Chip de celular</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="teclado" 
                                   name="equipamentos_novos[]" 
                                   value="teclado"
                                   {{ in_array('teclado', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="teclado" class="ml-2 text-sm text-gray-700">Teclado</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="mouse" 
                                   name="equipamentos_novos[]" 
                                   value="mouse"
                                   {{ in_array('mouse', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="mouse" class="ml-2 text-sm text-gray-700">Mouse</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="monitor" 
                                   name="equipamentos_novos[]" 
                                   value="monitor"
                                   {{ in_array('monitor', old('equipamentos_novos', [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="monitor" class="ml-2 text-sm text-gray-700">Monitor</label>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Quais os equipamentos necessito?
                    </p>
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

// Busca simples no dropdown de Cargo
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
</script>
@endsection
