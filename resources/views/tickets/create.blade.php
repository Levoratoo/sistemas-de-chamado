@extends('layouts.app')

@section('content')
<div class="py-10">
    <div class="max-w-2xl mx-auto px-4">
        @if(session('success'))
            <div class="mb-6 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-success-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-2xl bg-white shadow">
            <div class="border-b border-gray-100 px-6 py-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">
                            @if($requestType)
                                {{ $requestType->name }}
                            @else
                                Abrir Chamado
                            @endif
                        </h1>
                        <p class="mt-1 text-sm text-gray-500">
                            @if($requestType)
                                {{ $requestType->description }}
                            @else
                                Preencha as informações abaixo para registrar um novo chamado.
                            @endif
                        </p>
                    </div>
                    @if($requestType)
                    <div>
                        <a href="{{ route('request-areas.show', $requestType->requestArea->slug) }}" 
                           class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                            ← Voltar aos Tipos
                        </a>
                    </div>
                    @endif
                </div>
            </div>

            <form id="ticket-form" method="POST" action="{{ route('tickets.store') }}" enctype="multipart/form-data" class="space-y-6 px-6 py-6">
                @csrf
                
                @if($requestType)
                    <input type="hidden" name="request_type" value="{{ $requestType->slug }}">
                @endif

                <div>
                    <label for="requester" class="block text-sm font-medium text-gray-700">Solicitante</label>
                    <input
                        id="requester"
                        type="text"
                        readonly
                        value="{{ auth()->user()->name }}"
                        class="mt-1 w-full rounded-lg border-gray-200 bg-gray-100 px-4 py-2 text-gray-700 shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                </div>

                <div>
                    <label for="area_id" class="block text-sm font-medium text-gray-700">Departamento <span class="text-danger-500">*</span></label>
                    <select
                        id="area_id"
                        name="area_id"
                        required
                        aria-invalid="@error('area_id') true @else false @enderror"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('area_id') border-danger-300 ring-danger-200 @enderror"
                    >
                        <option value="">Selecione o departamento</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ (string) old('area_id') === (string) $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('area_id')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria <span class="text-danger-500">*</span></label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        aria-invalid="@error('category_id') true @else false @enderror"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('category_id') border-danger-300 ring-danger-200 @enderror"
                    >
                        <option value="">Selecione a categoria</option>
                        @php
                            $categories = \App\Models\Category::where('active', true)->orderBy('name')->get();
                        @endphp
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Título <span class="text-danger-500">*</span></label>
                    <input
                        id="title"
                        type="text"
                        name="title"
                        required
                        maxlength="150"
                        value="{{ old('title') }}"
                        aria-invalid="@error('title') true @else false @enderror"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('title') border-danger-300 ring-danger-200 @enderror"
                        placeholder="Resuma o problema em poucas palavras"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Descrição <span class="text-danger-500">*</span></label>
                    <textarea
                        id="description"
                        name="description"
                        rows="6"
                        required
                        aria-invalid="@error('description') true @else false @enderror"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('description') border-danger-300 ring-danger-200 @enderror"
                        placeholder="Descreva o cenario, impactos e passos para reproducao"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="attachments" class="block text-sm font-medium text-gray-700">Anexos</label>
                    <input
                        id="attachments"
                        name="attachments[]"
                        type="file"
                        multiple
                        accept=".pdf,.png,.jpg,.jpeg,.txt,.zip"
                        aria-describedby="attachments-help"
                        class="mt-1 w-full rounded-lg border-gray-200 px-4 py-2 text-gray-700 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('attachments.*') border-danger-300 ring-danger-200 @enderror"
                    >
                    <p id="attachments-help" class="mt-1 text-sm text-gray-500">
                        Formatos permitidos: PDF, PNG, JPG/JPEG, TXT ou ZIP (Ate 10&nbsp;MB por arquivo).
                    </p>
                    <ul id="attachments-list" class="mt-2 space-y-1 text-sm text-gray-600"></ul>
                    @error('attachments.*')
                        <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ url()->previous() }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Cancelar
                    </a>
                    <button
                        id="submit-button"
                        type="submit"
                        class="btn btn-primary px-6 py-2 text-sm font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                        Enviar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener(\"DOMContentLoaded\", function () {
        const form = document.getElementById(\"ticket-form\");
        const submitButton = document.getElementById(\"submit-button\");
        const attachmentsInput = document.getElementById(\"attachments\");
        const attachmentsList = document.getElementById(\"attachments-list\");
        const originalLabel = submitButton ? submitButton.textContent : \"\";

        if (attachmentsInput) {
            attachmentsInput.addEventListener(\"change\", function () {
                attachmentsList.innerHTML = \"\";

                if (!attachmentsInput.files || attachmentsInput.files.length === 0) {
                    return;
                }

                Array.from(attachmentsInput.files).forEach(function (file) {
                    const item = document.createElement(\"li\");
                    item.className = \"flex items-center rounded-lg bg-gray-100 px-3 py-1\";
                    item.textContent = file.name;
                    attachmentsList.appendChild(item);
                });
            });
        }

        if (form && submitButton) {
            form.addEventListener(\"submit\", function () {
                submitButton.disabled = true;
                submitButton.classList.add(\"cursor-not-allowed\", \"opacity-70\");
                submitButton.dataset.originalText = originalLabel;
                submitButton.textContent = \"Carregando...\";
            });
        }
    });
</script>
@endsection



