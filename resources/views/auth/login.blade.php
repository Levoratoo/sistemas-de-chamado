<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Printbag - Sistema de Chamados') }} - Login</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='50' font-size='50'>🌱</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="text-center">
                <!-- Logo Printbag -->
                <div class="flex justify-center mb-8">
                    <img src="{{ asset('logo-printbag.svg') }}" alt="Printbag Embalagens" class="h-24 w-auto" style="max-height: 6rem;">
                </div>
                <h2 class="mt-6 text-2xl font-bold text-gray-900">Faça login em sua conta</h2>
            </div>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <form class="space-y-6" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div>
                        <label for="login" class="block text-sm font-medium text-gray-700">
                            Login
                        </label>
                        <div class="mt-1">
                            <input id="login" name="login" type="text" autocomplete="username" required
                                   value="{{ old('login') }}"
                                   class="form-input @error('login') border-danger-300 @enderror"
                                   placeholder="ex.: joao.silva">
                            @error('login')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Senha
                        </label>
                        <div class="mt-1">
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                   class="form-input @error('password') border-danger-300 @enderror"
                                   placeholder="Sua senha">
                            @error('password')
                                <p class="mt-1 text-sm text-danger-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <label for="remember" class="ml-2 block text-sm text-gray-900">
                                Lembrar de mim
                            </label>
                        </div>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary w-full">
                            Entrar
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500">Usuários de teste</span>
                        </div>
                    </div>

                    <div class="mt-6 text-sm text-gray-600">
                        <p class="font-medium mb-2">Credenciais de teste:</p>
                        <div class="space-y-1">
                            <p><strong>Admin:</strong> login <code>admin</code> / senha <code>password</code></p>
                            <p><strong>Gestor:</strong> login <code>gestor</code> / senha <code>password</code></p>
                            <p><strong>Atendente:</strong> login <code>atendente</code> / senha <code>password</code></p>
                            <p><strong>Usuário:</strong> login <code>usuario</code> / senha <code>password</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Rodapé Login -->
    <footer class="bg-white border-t border-gray-200 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="text-center text-sm text-gray-600">
                © {{ date('Y') }} Printbag Embalagens. Sistema desenvolvido por <span class="font-semibold text-gray-700">Pedro Levorato</span>
            </div>
        </div>
    </footer>
</body>
</html>
