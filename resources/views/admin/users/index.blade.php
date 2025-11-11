@extends('layouts.app')

@section('content')
<div
    class="py-10"
    x-data="{
        modal: @js(old('form_origin')),
        close() { this.modal = null; document.body.classList.remove('overflow-hidden'); },
        open(name) { this.modal = name; document.body.classList.add('overflow-hidden'); },
    }"
    x-init="if(modal){ document.body.classList.add('overflow-hidden'); }"
    x-cloak
>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow-sm rounded-2xl">
            <div class="px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Usuarios</h1>
                    <p class="text-sm text-gray-500">Gerencie acessos e departamentos vinculados a cada usuario.</p>
                </div>
                <div class="flex items-center justify-end">
                    <button type="button" class="btn btn-primary" @click="open('create')">
                        Novo usuario
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm rounded-2xl">
            <!-- Campo de Busca -->
            <div class="px-6 py-4 border-b border-gray-100">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input
                                type="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Buscar por nome, email ou login..."
                                class="form-input pl-10 pr-4 w-full"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if(request('search'))
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                Limpar
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Usuarios cadastrados</h2>
                <span class="text-sm text-gray-500">{{ $users->total() }} registro(s)</span>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    @php
                        $isEditContext = old('form_origin') === 'edit-' . $user->id;
                        $selectedAreaIds = $isEditContext
                            ? collect(old('area_ids', []))->map(fn ($id) => (int) $id)->values()->all()
                            : $user->areas->pluck('id')->all();
                    @endphp
                    <div class="px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div class="flex-1 space-y-2">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-lg font-semibold text-gray-900">{{ $user->name }}</span>
                                <span class="badge bg-primary-100 text-primary-800">
                                    {{ ucfirst($user->role->name) }}
                                </span>
                                @if($user->areas->isNotEmpty())
                                    <span class="badge bg-emerald-100 text-emerald-800">
                                        {{ $user->areas->first()->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-500">
                                Login: <span class="font-medium text-gray-700">{{ $user->login }}</span> - Email: {{ $user->email }}
                            </div>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($user->areas as $area)
                                    <span class="badge bg-blue-100 text-blue-800">{{ $area->name }}</span>
                                @empty
                                    <span class="text-xs text-gray-400">Sem departamentos vinculados.</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="flex items-center gap-2 md:ml-6">
                            <button
                                type="button"
                                class="btn btn-secondary text-xs uppercase tracking-wide"
                                @click="open('edit-{{ $user->id }}')"
                            >
                                Editar
                            </button>
                            <form
                                method="POST"
                                action="{{ route('admin.users.destroy', $user) }}"
                                onsubmit="return confirm('Deseja realmente excluir este usuario?');"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger text-xs uppercase tracking-wide">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Edit modal -->
                    <div
                        class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 backdrop-blur-md"
                        x-show="modal === 'edit-{{ $user->id }}'"
                        x-transition.opacity
                        @keydown.escape.window="close()"
                        @click.self="close()"
                    >
                        <div
                            class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden"
                            x-transition.scale
                        >
                            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Editar usuario</h3>
                                <button type="button" class="text-gray-400 hover:text-gray-600" @click="close()">
                                    <span class="sr-only">Fechar</span>
                                    &times;
                                </button>
                            </div>
                            <form
                                method="POST"
                                action="{{ route('admin.users.update', $user) }}"
                                class="px-6 py-6 space-y-4"
                                x-data="userAreasSelect({
                                    areas: @js($areas->map(fn($area) => ['id' => $area->id, 'name' => $area->name])),
                                    selected: @js($selectedAreaIds),
                                })"
                            >
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="form_origin" value="edit-{{ $user->id }}">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $isEditContext ? old('name') : $user->name }}"
                                            required
                                            class="form-input"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ $isEditContext ? old('email') : $user->email }}"
                                            required
                                            class="form-input"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                                        <input
                                            type="text"
                                            name="login"
                                            value="{{ $isEditContext ? old('login') : $user->login }}"
                                            required
                                            class="form-input"
                                            placeholder="Ex.: joao.silva"
                                        >
                                        @error('login')
                                            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nova senha</label>
                                        <input
                                            type="password"
                                            name="password"
                                            class="form-input"
                                            placeholder="Opcional"
                                        >
                                        <p class="mt-1 text-xs text-gray-500">Preencha apenas se deseja redefinir a senha.</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                                        <select name="role_id" class="form-select" required>
                                            <option value="">Selecione</option>
                                            @foreach ($roles as $role)
                                                <option
                                                    value="{{ $role->id }}"
                                                    @selected(($isEditContext ? (int) old('role_id') : $user->role_id) === $role->id)
                                                >
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Departamentos vinculados</label>
                                    <div class="relative">
                                        <input
                                            type="search"
                                            x-model="search"
                                            class="form-input pr-10"
                                            placeholder="Buscar departamento"
                                        >
                                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto rounded-xl border border-gray-200 p-3 space-y-2 bg-gray-50">
                        <template x-for="area in filtered" :key="area.id">
                            <label
                                class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer transition border"
                                :class="selectedIds.includes(area.id) ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 hover:border-primary-300'"
                                @click.prevent="toggle(area.id)"
                            >
                                <span class="text-sm font-medium" x-text="area.name"></span>
                                <input type="checkbox" class="sr-only" :value="area.id" name="area_ids[]" x-model.number="selectedIds">
                                <span class="w-2.5 h-2.5 rounded-full border"
                                    :class="selectedIds.includes(area.id) ? 'bg-primary-500 border-primary-500' : 'border-gray-300'">
                                </span>
                                            </label>
                                        </template>
                                        <template x-if="filtered.length === 0">
                                            <p class="text-xs text-gray-400">Nenhum departamento encontrado.</p>
                                        </template>
                                    </div>
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        <template x-for="area in selectedBadges" :key="`badge-${area.id}`">
                                            <span class="badge bg-primary-100 text-primary-800">
                                                <span x-text="area.name"></span>
                                            </span>
                                        </template>
                                        <template x-if="selectedBadges.length === 0">
                                            <span class="text-xs text-gray-400">Nenhum departamento selecionado.</span>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                                    <button type="button" class="btn btn-secondary" @click="close()">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar alteracoes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-sm text-gray-500">
                        @if(request('search'))
                            Nenhum usuario encontrado para "{{ request('search') }}".
                        @else
                            Nenhum usuario cadastrado ate o momento.
                        @endif
                    </div>
                @endforelse
            </div>

            <div class="px-6 py-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Create modal -->
    <div
        class="fixed inset-0 z-40 flex items-center justify-center bg-gray-900/40 backdrop-blur-md"
        x-show="modal === 'create'"
        x-transition.opacity
        @keydown.escape.window="close()"
        @click.self="close()"
    >
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl mx-4 overflow-hidden" x-transition.scale>
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Novo usuario</h3>
                <button type="button" class="text-gray-400 hover:text-gray-600" @click="close()">
                    <span class="sr-only">Fechar</span>
                    &times;
                </button>
            </div>
            <form
                method="POST"
                action="{{ route('admin.users.store') }}"
                class="px-6 py-6 space-y-4"
                x-data="userAreasSelect({
                    areas: @js($areas->map(fn($area) => ['id' => $area->id, 'name' => $area->name])),
                    selected: @js(collect(old('area_ids', []))->map(fn($id) => (int) $id)->values()),
                })"
            >
                @csrf
                <input type="hidden" name="form_origin" value="create">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Ex.: Joana Pereira">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="email@empresa.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                        <input type="text" name="login" value="{{ old('login') }}" required class="form-input" placeholder="Ex.: joao.silva">
                        @error('login')
                            <p class="mt-1 text-xs text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Senha inicial</label>
                        <input type="password" name="password" required class="form-input" placeholder="Minimo 8 caracteres">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nivel</label>
                        <select name="role_id" class="form-select" required>
                            <option value="">Selecione</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((int) old('role_id') === $role->id)>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Departamentos vinculados</label>
                    <div class="relative">
                        <input
                            type="search"
                            x-model="search"
                            class="form-input pr-10"
                            placeholder="Buscar departamento"
                        >
                        <span class="absolute inset-y-0 right-3 flex items-center text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z" />
                            </svg>
                        </span>
                    </div>
                    <div class="max-h-48 overflow-y-auto rounded-xl border border-gray-200 p-3 space-y-2 bg-gray-50">
                        <template x-for="area in filtered" :key="area.id">
                            <label
                                class="flex items-center justify-between rounded-lg px-3 py-2 cursor-pointer transition border"
                                :class="selectedIds.includes(area.id) ? 'bg-primary-50 border-primary-300 text-primary-700' : 'bg-white border-gray-200 hover:border-primary-300'"
                                @click.prevent="toggle(area.id)"
                            >
                                <span class="text-sm font-medium" x-text="area.name"></span>
                                <input type="checkbox" class="sr-only" name="area_ids[]" :value="area.id" x-model.number="selectedIds">
                                <span class="w-2.5 h-2.5 rounded-full border"
                                    :class="selectedIds.includes(area.id) ? 'bg-primary-500 border-primary-500' : 'border-gray-300'">
                                </span>
                            </label>
                        </template>
                        <template x-if="filtered.length === 0">
                            <p class="text-xs text-gray-400">Nenhum departamento encontrado.</p>
                        </template>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-2">
                        <template x-for="area in selectedBadges" :key="`badge-${area.id}`">
                            <span class="badge bg-primary-100 text-primary-800">
                                <span x-text="area.name"></span>
                            </span>
                        </template>
                        <template x-if="selectedBadges.length === 0">
                            <span class="text-xs text-gray-400">Nenhum departamento selecionado.</span>
                        </template>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" class="btn btn-secondary" @click="close()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userAreasSelect', ({ areas, selected }) => ({
                search: '',
                all: areas,
                selectedIds: Array.from(new Set((selected || []).map(id => Number(id)))),
                get filtered() {
                    if (!this.search.trim()) {
                        return this.all;
                    }
                    return this.all.filter(area =>
                        area.name.toLowerCase().includes(this.search.toLowerCase())
                    );
                },
                get selectedBadges() {
                    return this.all.filter(area => this.selectedIds.includes(area.id));
                },
                toggle(id) {
                    const value = Number(id);
                    if (this.selectedIds.includes(value)) {
                        this.selectedIds = this.selectedIds.filter(item => item !== value);
                    } else {
                        this.selectedIds = [...this.selectedIds, value];
                    }
                }
            }));
        });
    </script>
@endpush
@endsection





