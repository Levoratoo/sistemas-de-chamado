@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Meu Perfil</h1>
            <p class="mt-2 text-gray-600">Gerencie sua foto de perfil</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-lg sm:rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-lg font-semibold text-gray-900">Foto de Perfil</h2>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
                    <!-- Preview da Foto -->
                    <div class="flex-shrink-0">
                        <div class="relative">
                            @if($user->profile_photo)
                                <img src="{{ Storage::url($user->profile_photo) }}" 
                                     alt="{{ $user->name }}" 
                                     class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg">
                            @else
                                <div class="w-32 h-32 rounded-full bg-primary-600 flex items-center justify-center border-4 border-gray-200 shadow-lg">
                                    <span class="text-4xl font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Formulário -->
                    <div class="flex-1 w-full">
                        <form action="{{ route('profile.update-photo') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                            @csrf
                            
                            <div>
                                <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">
                                    Selecionar Nova Foto
                                </label>
                                <div class="flex items-center space-x-4">
                                    <label for="profile_photo" class="cursor-pointer inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Escolher Arquivo
                                    </label>
                                    <input type="file" 
                                           id="profile_photo" 
                                           name="profile_photo" 
                                           accept="image/jpeg,image/jpg,image/png,image/gif"
                                           class="hidden"
                                           onchange="previewImage(this)">
                                    <span id="file-name" class="text-sm text-gray-500"></span>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 2MB. Dimensões máximas: 2000x2000px.
                                </p>
                                
                                @error('profile_photo')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex space-x-4">
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Salvar Foto
                                </button>
                                
                                @if($user->profile_photo)
                                    <form action="{{ route('profile.remove-photo') }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                onclick="return confirm('Tem certeza que deseja remover sua foto de perfil?')"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Remover Foto
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Informações do Usuário -->
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informações da Conta</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Login</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->login ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Perfil</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($user->role->name ?? 'N/A') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const fileNameSpan = document.getElementById('file-name');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        fileNameSpan.textContent = file.name;
        
        // Preview da imagem
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.w-32.h-32');
            if (preview && preview.tagName === 'IMG') {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(file);
    } else {
        fileNameSpan.textContent = '';
    }
}
</script>
@endsection





