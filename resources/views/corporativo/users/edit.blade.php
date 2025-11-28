@extends('corporativo.layout')

@section('title', 'Editar Usuário - Corporativo')
@section('page-title', 'Editar Usuário')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Editar Usuário</h1>
                <p class="text-gray-600 mt-2">Atualize as informações de {{ $user->name }}</p>
            </div>
            <a href="{{ route('corporativo.users') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-medium transition-colors">Voltar</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('corporativo.users._form')
        </div>
    </div>
</div>
@endsection