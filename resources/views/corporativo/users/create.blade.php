@extends('corporativo.layout')

@section('title', 'Criar Usuário - Sistema Corporativo')
@section('page-title', 'Criar Usuário')

@section('content')
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Novo Usuário</h3>
    @include('corporativo.users._form')
    <div class="mt-4">
        <a href="{{ route('corporativo.users') }}" class="text-blue-600 hover:text-blue-800">Voltar para lista</a>
    </div>
    @if(!auth()->user()->isSuperAdmin())
        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded">
            Você não tem permissão para criar usuários.
        </div>
    @endif
</div>
@endsection