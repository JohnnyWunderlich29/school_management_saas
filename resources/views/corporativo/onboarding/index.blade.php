@extends('corporativo.layout')

@section('title', 'Primeiros passos')
@section('page-title', 'Primeiros passos')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configuração inicial</h1>
                <p class="text-sm text-gray-600">Complete as etapas para finalizar o setup do sistema.</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-48 bg-gray-200 rounded-full h-2">
                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $progress }}%"></div>
                </div>
                <span class="text-sm text-gray-700">{{ $progress }}%</span>
            </div>
        </div>

        <ul class="space-y-3">
            @foreach($steps as $step)
                @php $done = in_array($step['slug'], $completed ?? [], true); @endphp
                <li class="flex items-start justify-between p-4 border rounded-lg {{ $done ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' }}">
                    <div class="flex items-start gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $done ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-700' }}">
                            @if($done)
                                <i class="fas fa-check text-xs"></i>
                            @else
                                <i class="fas fa-dot-circle text-xs"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $step['label'] }}</p>
                            <p class="text-sm text-gray-600">{{ $step['description'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ $step['url'] ?? '#' }}" class="px-3 py-1.5 text-sm bg-white border border-gray-300 rounded hover:bg-gray-100">Abrir</a>
                        <form method="POST" action="{{ route('onboarding.toggle', $step['slug']) }}">
                            @csrf
                            <button class="px-3 py-1.5 text-sm {{ $done ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} rounded">
                                {{ $done ? 'Concluído' : 'Marcar concluído' }}
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
    <p class="mt-3 text-xs text-gray-500">Você pode minimizar ou fechar o ajudante na barra inferior.</p>
 </div>
@endsection