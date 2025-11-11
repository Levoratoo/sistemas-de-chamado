@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-gray-900">{{ $requestType->name ?? 'Impressão' }}</h1>
                    <p class="mt-2 text-gray-600">Nominar a impressão, esse chamado pode ser reutilizado futuramente se for o caso de um chamado recorrente.</p>
                </div>
                <div class="flex-shrink-0">
                    <a href="{{ route('request-areas.show', 'pre-impressao') }}" class="inline-flex items-center bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors whitespace-nowrap">← Voltar aos Tipos</a>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <form method="POST" action="{{ route('tickets.store-impressao') }}" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="titulo" class="block text-sm font-medium text-gray-700 mb-2">Título <span class="text-red-500">*</span></label>
                    <input type="text" id="titulo" name="titulo" value="{{ old('titulo') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tipo_impressao" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Impressão</label>
                        <select id="tipo_impressao" name="tipo_impressao" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach(['A4','A3','A2','Customizado'] as $opt)
                                <option value="{{ $opt }}" {{ old('tipo_impressao')==$opt?'selected':'' }}>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Informar o tamanho da impressão.</p>
                    </div>
                    <div>
                        <label for="impressao_customizada" class="block text-sm font-medium text-gray-700 mb-2">Impressão Customizada (opcional)</label>
                        <input type="text" id="impressao_customizada" name="impressao_customizada" value="{{ old('impressao_customizada') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Preencha quando o tipo for Customizado">
                        <p class="mt-1 text-xs text-gray-500">Preencher este campo somente se a opção acima for "Customizado".</p>
                    </div>

                    <div>
                        <label for="quantidade" class="block text-sm font-medium text-gray-700 mb-2">Quantidade de Impressões</label>
                        <input type="number" min="1" id="quantidade" name="quantidade" value="{{ old('quantidade') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Número de impressões necessárias.</p>
                    </div>
                    <div>
                        <label for="data_prevista" class="block text-sm font-medium text-gray-700 mb-2">Data Prevista</label>
                        <input type="date" id="data_prevista" name="data_prevista" value="{{ old('data_prevista') }}" min="{{ date('Y-m-d') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Data estimada para entrega.</p>
                    </div>
                </div>

                <div>
                    <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
                    <textarea id="observacao" name="observacao" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao') }}</textarea>
                </div>

                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-2">Anexos</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="attachments" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Arraste e solte arquivos, cole capturas de tela ou</span>
                                    <input id="attachments" name="attachments[]" type="file" class="sr-only" multiple accept="application/pdf,image/*,.ai,.psd">
                                    <span class="ml-1">navegar</span>
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">Arquivos precisam estar em PDF para impressão.</p>
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











