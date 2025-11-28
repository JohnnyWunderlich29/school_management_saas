@extends('corporativo.layout')

@section('title', 'Novo Módulo')
@section('page-title', 'Criar Novo Módulo')

@section('content')
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Novo Módulo</h3>
        @include('corporativo.modules._form')
    </div>
@endsection