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
            <form method="POST" action="{{ route('tickets.store-rrq') }}" enctype="multipart/form-data" class="p-6 space-y-6">
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
                           placeholder="Resumo da reclamação de qualidade"
                           required>
                    @error('resumo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prioridade -->
                <div>
                    <label for="prioridade" class="block text-sm font-medium text-gray-700 mb-2">
                        Prioridade
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

    // Aplicar em todos os dropdowns
    ['prioridade'].forEach(selectId => {
        setupSearchableDropdown(selectId);
    });
});
</script>
@endsection

