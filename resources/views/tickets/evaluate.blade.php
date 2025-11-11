@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Avaliar Atendimento</h1>
                    <p class="mt-2 text-sm text-gray-600">Avalie a qualidade do atendimento recebido</p>
                </div>

                <!-- Informações do Chamado -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ $ticket->title }}</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>
                            <span class="font-medium">Código:</span> {{ $ticket->code }}
                        </div>
                        <div>
                            <span class="font-medium">Área:</span> {{ $ticket->area->name }}
                        </div>
                        <div>
                            <span class="font-medium">Atendente:</span> {{ $ticket->assignee->name ?? 'Não atribuído' }}
                        </div>
                        <div>
                            <span class="font-medium">Finalizado em:</span> {{ $ticket->resolved_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>

                <!-- Formulário de Avaliação -->
                <form method="POST" action="{{ route('tickets.evaluate.store', $ticket) }}" class="space-y-6">
                    @csrf
                    
                    <!-- Rating -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Como você avalia o atendimento? <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center space-x-2" x-data="{ rating: 0 }">
                            @for($i = 1; $i <= 5; $i++)
                            <button type="button" 
                                    @click="rating = {{ $i }}"
                                    :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-gray-300'"
                                    class="text-4xl hover:text-yellow-400 transition-colors focus:outline-none">
                                ★
                            </button>
                            @endfor
                            <input type="hidden" name="rating" x-model="rating" required>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <span x-show="rating === 0">Selecione uma nota</span>
                            <span x-show="rating === 1">Muito insatisfeito</span>
                            <span x-show="rating === 2">Insatisfeito</span>
                            <span x-show="rating === 3">Neutro</span>
                            <span x-show="rating === 4">Satisfeito</span>
                            <span x-show="rating === 5">Muito satisfeito</span>
                        </div>
                        @error('rating')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Comentário -->
                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">
                            Comentários (opcional)
                        </label>
                        <textarea name="comment" 
                                  id="comment" 
                                  rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Deixe aqui seus comentários sobre o atendimento..."></textarea>
                        @error('comment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botões -->
                    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
                        <a href="{{ route('tickets.show', $ticket) }}" 
                           class="text-gray-600 hover:text-gray-800 font-medium">
                            Cancelar
                        </a>
                        <button type="submit" 
                                class="btn btn-primary">
                            Enviar Avaliação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('rating', () => ({
        rating: 0,
        init() {
            // Validação do formulário
            const form = this.$el.closest('form');
            const submitBtn = form.querySelector('button[type="submit"]');
            
            form.addEventListener('submit', (e) => {
                if (this.rating === 0) {
                    e.preventDefault();
                    alert('Por favor, selecione uma nota para o atendimento.');
                }
            });
        }
    }));
});
</script>
@endsection










