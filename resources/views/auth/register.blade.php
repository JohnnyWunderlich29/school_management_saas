@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 flex">
    <!-- Left Panel - Welcome Section -->
    <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-teal-500 via-teal-600 to-cyan-600 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="w-full h-full" style="background-image: repeating-linear-gradient(0deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 36px), repeating-linear-gradient(90deg, transparent, transparent 35px, rgba(255,255,255,.1) 35px, rgba(255,255,255,.1) 36px);"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-10 flex flex-col justify-center items-center text-center px-12 text-white">
            <!-- Welcome Card -->
            <div class="bg-white bg-opacity-95 rounded-2xl shadow-2xl p-16 mx-[20%] text-center">
                <!-- User Plus Icon -->
                <div class="mb-8">
                    <svg class="w-16 h-16 text-teal-600 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                
                <!-- Welcome Text -->
                <h1 class="text-4xl font-bold mb-4 text-gray-800">Junte-se a nós!</h1>
                <p class="text-xl text-gray-600 max-w-md leading-relaxed mx-auto">
                    Crie sua conta e comece a transformar a gestão da sua escola hoje mesmo.
                </p>
            </div>
            
            <!-- Decorative Elements -->
            <div class="absolute top-10 right-10 w-20 h-20 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute bottom-20 left-10 w-16 h-16 bg-white bg-opacity-10 rounded-full"></div>
            <div class="absolute top-1/3 left-1/4 w-8 h-8 bg-white bg-opacity-10 rounded-full"></div>
        </div>
    </div>
    
    <!-- Right Panel - Register Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8">
        <div class="w-full max-w-md">
            <!-- Mobile Logo -->
            <div class="lg:hidden text-center mb-8">
                <div class="inline-flex w-16 h-16 bg-teal-500 rounded-2xl items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Criar conta</h2>
                <p class="text-gray-600 mt-2">Registre-se no sistema</p>
            </div>
            
            <!-- Desktop Header -->
            <div class="hidden lg:block mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Registrar</h2>
                <p class="text-gray-600">Crie sua conta para acessar o sistema.</p>
            </div>
            
            <!-- Register Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                
                <!-- Name Field -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nome completo</label>
                    <input id="name" name="name" type="text" autocomplete="name" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 bg-white" 
                           placeholder="Digite seu nome completo" value="{{ old('name') }}">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 bg-white" 
                           placeholder="Digite seu email" value="{{ old('email') }}">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 bg-white" 
                           placeholder="Digite sua senha">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Password Confirmation Field -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-colors duration-200 bg-white" 
                           placeholder="Confirme sua senha">
                    @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold py-3 px-4 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    SUBMIT
                </button>
                
                <!-- Login Link -->
                <div class="text-center">
                    <span class="text-gray-600">Já tem uma conta? </span>
                    <a href="{{ route('login') }}" class="text-teal-600 hover:text-teal-500 font-medium">
                        Faça login
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection