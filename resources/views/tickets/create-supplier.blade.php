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
            <form method="POST" action="{{ route('tickets.store-supplier') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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

                <!-- Tipo de Cadastro -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo de cadastro <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-6">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="tipo_cadastro" 
                                   value="fornecedor" 
                                   {{ old('tipo_cadastro', 'fornecedor') == 'fornecedor' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">Fornecedor</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="tipo_cadastro" 
                                   value="transportadora" 
                                   {{ old('tipo_cadastro') == 'transportadora' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">Transportadora</span>
                        </label>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Fornecedor ou Transportadora.
                    </p>
                    @error('tipo_cadastro')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grid de Campos -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Física/Jurídica/Exportação -->
                    <div>
                        <label for="fisica_juridica_exportacao" class="block text-sm font-medium text-gray-700 mb-2">
                            Física/Jurídica/Exportação <span class="text-red-500">*</span>
                        </label>
                        <select id="fisica_juridica_exportacao" 
                                name="fisica_juridica_exportacao"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="fisica" {{ old('fisica_juridica_exportacao', 'fisica') == 'fisica' ? 'selected' : '' }}>Física</option>
                            <option value="juridica" {{ old('fisica_juridica_exportacao') == 'juridica' ? 'selected' : '' }}>Jurídica</option>
                            <option value="exportacao" {{ old('fisica_juridica_exportacao') == 'exportacao' ? 'selected' : '' }}>Exportação</option>
                        </select>
                        @error('fisica_juridica_exportacao')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Razão Social -->
                    <div>
                        <label for="razao_social" class="block text-sm font-medium text-gray-700 mb-2">
                            Razão social <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="razao_social" 
                               name="razao_social" 
                               value="{{ old('razao_social') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('razao_social')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CNPJ/CPF -->
                    <div>
                        <label for="cnpj_cpf" class="block text-sm font-medium text-gray-700 mb-2">
                            CNPJ/CPF <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cnpj_cpf" 
                               name="cnpj_cpf" 
                               value="{{ old('cnpj_cpf') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('cnpj_cpf')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefone -->
                    <div>
                        <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">
                            Telefone <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="telefone" 
                               name="telefone" 
                               value="{{ old('telefone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('telefone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- E-mail -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            E-mail <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Endereço -->
                    <div>
                        <label for="endereco" class="block text-sm font-medium text-gray-700 mb-2">
                            Endereço <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="endereco" 
                               name="endereco" 
                               value="{{ old('endereco') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('endereco')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logradouro, número -->
                    <div>
                        <label for="logradouro_numero" class="block text-sm font-medium text-gray-700 mb-2">
                            Logradouro, número <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="logradouro_numero" 
                               name="logradouro_numero" 
                               value="{{ old('logradouro_numero') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('logradouro_numero')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bairro -->
                    <div>
                        <label for="bairro" class="block text-sm font-medium text-gray-700 mb-2">
                            Bairro <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="bairro" 
                               name="bairro" 
                               value="{{ old('bairro') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('bairro')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CEP -->
                    <div>
                        <label for="cep" class="block text-sm font-medium text-gray-700 mb-2">
                            CEP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cep" 
                               name="cep" 
                               value="{{ old('cep') }}"
                               maxlength="8"
                               pattern="\d{8}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="88340344"
                               required>
                        <p class="mt-1 text-xs text-gray-500">
                            Informe o CEP sem ". " e sem " - " Exemplo: 88340344
                        </p>
                        @error('cep')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cidade -->
                    <div>
                        <label for="cidade" class="block text-sm font-medium text-gray-700 mb-2">
                            Cidade <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="cidade" 
                               name="cidade" 
                               value="{{ old('cidade') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               required>
                        @error('cidade')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Estado -->
                    <div>
                        <label for="estado" class="block text-sm font-medium text-gray-700 mb-2">
                            Estado <span class="text-red-500">*</span>
                        </label>
                        <select id="estado" 
                                name="estado"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="">Selecione...</option>
                            <option value="AC" {{ old('estado') == 'AC' ? 'selected' : '' }}>Acre</option>
                            <option value="AL" {{ old('estado') == 'AL' ? 'selected' : '' }}>Alagoas</option>
                            <option value="AP" {{ old('estado') == 'AP' ? 'selected' : '' }}>Amapá</option>
                            <option value="AM" {{ old('estado') == 'AM' ? 'selected' : '' }}>Amazonas</option>
                            <option value="BA" {{ old('estado') == 'BA' ? 'selected' : '' }}>Bahia</option>
                            <option value="CE" {{ old('estado') == 'CE' ? 'selected' : '' }}>Ceará</option>
                            <option value="DF" {{ old('estado') == 'DF' ? 'selected' : '' }}>Distrito Federal</option>
                            <option value="ES" {{ old('estado') == 'ES' ? 'selected' : '' }}>Espírito Santo</option>
                            <option value="GO" {{ old('estado') == 'GO' ? 'selected' : '' }}>Goiás</option>
                            <option value="MA" {{ old('estado') == 'MA' ? 'selected' : '' }}>Maranhão</option>
                            <option value="MT" {{ old('estado') == 'MT' ? 'selected' : '' }}>Mato Grosso</option>
                            <option value="MS" {{ old('estado') == 'MS' ? 'selected' : '' }}>Mato Grosso do Sul</option>
                            <option value="MG" {{ old('estado') == 'MG' ? 'selected' : '' }}>Minas Gerais</option>
                            <option value="PA" {{ old('estado') == 'PA' ? 'selected' : '' }}>Pará</option>
                            <option value="PB" {{ old('estado') == 'PB' ? 'selected' : '' }}>Paraíba</option>
                            <option value="PR" {{ old('estado') == 'PR' ? 'selected' : '' }}>Paraná</option>
                            <option value="PE" {{ old('estado') == 'PE' ? 'selected' : '' }}>Pernambuco</option>
                            <option value="PI" {{ old('estado') == 'PI' ? 'selected' : '' }}>Piauí</option>
                            <option value="RJ" {{ old('estado') == 'RJ' ? 'selected' : '' }}>Rio de Janeiro</option>
                            <option value="RN" {{ old('estado') == 'RN' ? 'selected' : '' }}>Rio Grande do Norte</option>
                            <option value="RS" {{ old('estado') == 'RS' ? 'selected' : '' }}>Rio Grande do Sul</option>
                            <option value="RO" {{ old('estado') == 'RO' ? 'selected' : '' }}>Rondônia</option>
                            <option value="RR" {{ old('estado') == 'RR' ? 'selected' : '' }}>Roraima</option>
                            <option value="SC" {{ old('estado') == 'SC' ? 'selected' : '' }}>Santa Catarina</option>
                            <option value="SP" {{ old('estado') == 'SP' ? 'selected' : '' }}>São Paulo</option>
                            <option value="SE" {{ old('estado') == 'SE' ? 'selected' : '' }}>Sergipe</option>
                            <option value="TO" {{ old('estado') == 'TO' ? 'selected' : '' }}>Tocantins</option>
                        </select>
                        @error('estado')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- País -->
                    <div>
                        <label for="pais" class="block text-sm font-medium text-gray-700 mb-2">
                            País <span class="text-red-500">*</span>
                        </label>
                        <select id="pais" 
                                name="pais"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <option value="Brasil" {{ old('pais', 'Brasil') == 'Brasil' ? 'selected' : '' }}>Brasil</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Chile">Chile</option>
                            <option value="Uruguai">Uruguai</option>
                            <option value="Paraguai">Paraguai</option>
                            <option value="Estados Unidos">Estados Unidos</option>
                            <option value="Outro">Outro</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Caso for de outro país, favor informe na observação.
                        </p>
                        @error('pais')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Inscrição Estadual -->
                    <div>
                        <label for="inscricao_estadual" class="block text-sm font-medium text-gray-700 mb-2">
                            Inscrição Estadual (opcional)
                        </label>
                        <input type="text" 
                               id="inscricao_estadual" 
                               name="inscricao_estadual" 
                               value="{{ old('inscricao_estadual') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('inscricao_estadual')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Inscrição Municipal -->
                    <div>
                        <label for="inscricao_municipal" class="block text-sm font-medium text-gray-700 mb-2">
                            Inscrição Municipal (opcional)
                        </label>
                        <input type="text" 
                               id="inscricao_municipal" 
                               name="inscricao_municipal" 
                               value="{{ old('inscricao_municipal') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('inscricao_municipal')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contato -->
                    <div>
                        <label for="contato" class="block text-sm font-medium text-gray-700 mb-2">
                            Contato (opcional)
                        </label>
                        <input type="text" 
                               id="contato" 
                               name="contato" 
                               value="{{ old('contato') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        @error('contato')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Data Prevista -->
                    <div>
                        <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">
                            Data Prevista (opcional)
                        </label>
                        <div class="relative">
                            <input type="date" 
                                   id="data_prevista" 
                                   name="data_prevista"
                                   value="{{ old('data_prevista') }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>
                        @error('data_prevista')
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

                <!-- Descrição -->
                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrição (opcional)
                    </label>
                    <textarea id="descricao" 
                              name="descricao" 
                              rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Observações a ser adicionada.">{{ old('descricao') }}</textarea>
                    @error('descricao')
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
// Máscara para CEP (apenas números)
document.getElementById('cep').addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '');
});

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











