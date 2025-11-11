@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Editar Chamado - {{ $ticket->code }}</h1>
                    <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                        Voltar
                    </a>
                </div>

                <form method="POST" action="{{ route('tickets.update', $ticket) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                            Título <span class="text-danger-500">*</span>
                        </label>
                        <input type="text" name="title" id="title" value="{{ old('title', $ticket->title) }}" required
                               class="form-input @error('title') border-danger-300 @enderror"
                               placeholder="Descreva brevemente o problema">
                        @error('title')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Categoria <span class="text-danger-500">*</span>
                        </label>
                        <select name="category_id" id="category_id" required
                                class="form-select @error('category_id') border-danger-300 @enderror">
                            <option value="">Selecione uma categoria</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $ticket->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">
                            Prioridade <span class="text-danger-500">*</span>
                        </label>
                        <select name="priority" id="priority" required
                                class="form-select @error('priority') border-danger-300 @enderror">
                            <option value="">Selecione uma prioridade</option>
                            <option value="low" {{ old('priority', $ticket->priority) === 'low' ? 'selected' : '' }}>Baixa</option>
                            <option value="medium" {{ old('priority', $ticket->priority) === 'medium' ? 'selected' : '' }}>Média</option>
                            <option value="high" {{ old('priority', $ticket->priority) === 'high' ? 'selected' : '' }}>Alta</option>
                            <option value="critical" {{ old('priority', $ticket->priority) === 'critical' ? 'selected' : '' }}>Crítica</option>
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                            Status <span class="text-danger-500">*</span>
                        </label>
                        <select name="status" id="status" required
                                class="form-select @error('status') border-danger-300 @enderror">
                            <option value="open" {{ old('status', $ticket->status) === 'open' ? 'selected' : '' }}>Aberto</option>
                            <option value="in_progress" {{ old('status', $ticket->status) === 'in_progress' ? 'selected' : '' }}>Em Andamento</option>
                            <option value="waiting_user" {{ old('status', $ticket->status) === 'waiting_user' ? 'selected' : '' }}>Aguardando Usuário</option>
                            <option value="resolved" {{ old('status', $ticket->status) === 'resolved' ? 'selected' : '' }}>Resolvido</option>
                            <option value="closed" {{ old('status', $ticket->status) === 'closed' ? 'selected' : '' }}>Fechado</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="assignee_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Atribuir a
                        </label>
                        <select name="assignee_id" id="assignee_id"
                                class="form-select @error('assignee_id') border-danger-300 @enderror">
                            <option value="">Selecionar atendente</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('assignee_id', $ticket->assignee_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('assignee_id')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Descrição <span class="text-danger-500">*</span>
                        </label>
                        <textarea name="description" id="description" rows="6" required
                                  class="form-textarea @error('description') border-danger-300 @enderror"
                                  placeholder="Descreva detalhadamente o problema, incluindo passos para reproduzir, se aplicável">{{ old('description', $ticket->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('tickets.show', $ticket) }}" class="btn btn-secondary">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Atualizar Chamado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection











