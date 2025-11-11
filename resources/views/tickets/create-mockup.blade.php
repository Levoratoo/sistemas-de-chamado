@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $requestType->name ?? 'Mock up' }}</h1>
                    <p class="mt-2 text-gray-600">{{ $requestType->description ?? 'Solicitações relacionadas a mock up de pré‑impressão' }}</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('request-areas.show', 'pre-impressao') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Voltar aos Tipos
                    </a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('tickets.store-mockup') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título <span class="text-red-500">*</span></label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Ex: Printbag - Verão - Sacola" required>
                    <p class="mt-1 text-xs text-gray-500">Manter a seguinte ordem de nomenclatura NOME - PROJETO - PRODUTO e/ou NOME - PRODUTO</p>
                    @error('titulo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Solicitações Pré (opcional)</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                        @php($opts = ['gabarito'=>'Gabarito','layout'=>'Layout','mock-up'=>'Mock up','mock-up-impresso'=>'Mock up Impresso','puxada-cor'=>'Puxada de Cor','3d-site'=>'3D/Site','prova-contratual'=>'Prova Contratual','desenvol-produto'=>'Desenvol. Produto'])
                        @foreach($opts as $val=>$label)
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="solicitacao_pre[]" value="{{ $val }}" {{ is_array(old('solicitacao_pre')) && in_array($val, old('solicitacao_pre')) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="produto" class="block text-sm font-medium text-gray-700 mb-2">Produto <span class="text-red-500">*</span></label>
                    <select id="produto" name="produto" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        <option value="">Selecione...</option>
                        <option value="SACOLA" {{ old('produto')=='SACOLA'?'selected':'' }}>SACOLA</option>
                        <option value="SACO" {{ old('produto')=='SACO'?'selected':'' }}>SACO</option>
                        <option value="CAIXA" {{ old('produto')=='CAIXA'?'selected':'' }}>CAIXA</option>
                        <option value="ENVELOPE" {{ old('produto')=='ENVELOPE'?'selected':'' }}>ENVELOPE</option>
                        <option value="PRESENTE" {{ old('produto')=='PRESENTE'?'selected':'' }}>PRESENTE (Etiqueta, Tag, Seda, Fita, Papel de Seda)</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="calculo_opcao" class="block text-sm font-medium text-gray-700 mb-2">Cálculo / Opção</label>
                        <input type="text" id="calculo_opcao" name="calculo_opcao" value="{{ old('calculo_opcao') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="furo_fita" class="block text-sm font-medium text-gray-700 mb-2">Furo de Fita</label>
                        <select id="furo_fita" name="furo_fita" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Selecione...</option>
                            @foreach(['Opção 01','Opção 02','Opção 03','Opção 04','Opção 05','Opção 06','Sacoleira','Especial','N/A'] as $opt)
                                <option value="{{ $opt }}" {{ old('furo_fita')==$opt?'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Opção de Furo da Fita</p>
                    </div>
                    <div>
                        <label for="cor_alca" class="block text-sm font-medium text-gray-700 mb-2">Cor da Alça (opcional)</label>
                        <input type="text" id="cor_alca" name="cor_alca" value="{{ old('cor_alca') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="cores" class="block text-sm font-medium text-gray-700 mb-2">Cores</label>
                        <input type="text" id="cores" name="cores" value="{{ old('cores') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label for="embalagem_transporte" class="block text-sm font-medium text-gray-700 mb-2">Embalagem para Transporte</label>
                    <select id="embalagem_transporte" name="embalagem_transporte" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        @foreach(['Embalagem Rígida','Embalagem Simples','Sem Embalagem'] as $opt)
                            <option value="{{ $opt }}" {{ old('embalagem_transporte')==$opt?'selected':'' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">Data Prevista</label>
                    <input type="date" id="data_prevista" name="data_prevista" value="{{ old('data_prevista') }}" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação (opcional)</label>
                    <textarea id="observacao" name="observacao" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao') }}</textarea>
                </div>

                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Anexos (opcional)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Arraste e solte arquivos, cole capturas de tela ou</span>
                                    <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple accept="image/*,application/pdf,.doc,.docx">
                                    <span class="ml-1">navegar</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">Arquivos, Fotos, Modelo, Exemplo etc...</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('request-areas.show', 'pre-impressao') }}" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Criar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection











