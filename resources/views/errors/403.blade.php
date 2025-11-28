@extends('layouts.app')

@section('content')
<x-breadcrumbs :items="[
    ['title' => 'Erro 403', 'url' => '#']
]" />

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-24 w-24 text-red-500">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Acesso Negado
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                {{ $message ?? 'Você não tem permissão para acessar esta página.' }}
            </p>
        </div>
        
        <div class="mt-8 space-y-4">
            <a href="{{ route('dashboard') }}" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Voltar ao Dashboard
            </a>
            
            <a href="javascript:history.back()" class="group relative w-full flex justify-center py-2 px-4 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Voltar à Página Anterior
            </a>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                Se você acredita que deveria ter acesso a esta funcionalidade, entre em contato com o administrador do sistema.
            </p>
        </div>
    </div>
</div>
@endsection