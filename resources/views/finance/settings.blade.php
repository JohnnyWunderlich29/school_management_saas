@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @php($currentSchoolId = optional(Auth::user())->escola_id ?? optional(Auth::user())->school_id ?? session('escola_atual'))
        <x-breadcrumbs :items="[
            ['title' => 'Financeiro', 'url' => route('finance.settings', $currentSchoolId ? ['school_id' => $currentSchoolId] : [])],
            ['title' => 'Configurações', 'url' => '#']
        ]" />

        <div class="mb-6 flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Configurações Financeiras</h1>
                <p class="mt-1 text-sm text-gray-600">Defina gateway padrão, multas/juros e métodos de pagamento</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $financeEnv === 'production' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                    Ambiente: {{ strtoupper($financeEnv) }}
                </span>
                @php($currentSchoolId = optional(Auth::user())->escola_id ?? optional(Auth::user())->school_id ?? session('escola_atual'))
                <x-button href="{{ route('finance.gateways', $currentSchoolId ? ['school_id' => $currentSchoolId] : []) }}" color="secondary">
                    <i class="fas fa-plug mr-1"></i> Gateways
                </x-button>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded text-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('finance.settings.save') }}">
            @csrf

            <x-card class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Gateway padrão</h2>
                </div>
                <label for="default_gateway_alias" class="block text-sm font-medium text-gray-700">Alias do gateway padrão</label>
                <select id="default_gateway_alias" name="default_gateway_alias" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Selecionar —</option>
                    @foreach ($gateways as $gw)
                        <option value="{{ $gw->alias }}" @selected($settings->default_gateway_alias === $gw->alias)>
                            {{ $gw->alias }} {{ $gw->name ? '('.$gw->name.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </x-card>

            <x-card class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Política de multa e juros</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fine_percent" class="block text-sm font-medium text-gray-700">Multa (% do valor)</label>
                        <input type="number" step="0.01" min="0" id="fine_percent" name="fine_percent" value="{{ old('fine_percent', $settings->penalty_policy['fine_percent'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="daily_interest_percent" class="block text-sm font-medium text-gray-700">Juros diário (% ao dia)</label>
                        <input type="number" step="0.001" min="0" id="daily_interest_percent" name="daily_interest_percent" value="{{ old('daily_interest_percent', $settings->penalty_policy['daily_interest_percent'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="grace_days" class="block text-sm font-medium text-gray-700">Dias de carência</label>
                        <input type="number" min="0" max="30" id="grace_days" name="grace_days" value="{{ old('grace_days', $settings->penalty_policy['grace_days'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div>
                        <label for="max_interest_percent" class="block text-sm font-medium text-gray-700">Juros máximo acumulado (%)</label>
                        <input type="number" step="0.01" min="0" id="max_interest_percent" name="max_interest_percent" value="{{ old('max_interest_percent', $settings->penalty_policy['max_interest_percent'] ?? '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </x-card>

            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Métodos de pagamento permitidos</h2>
                @php
                    $methods = ['boleto' => 'Boleto', 'pix' => 'Pix', 'card' => 'Cartão', 'cash' => 'Dinheiro', 'transfer' => 'Transferência'];
                    $allowed = $settings->allowed_payment_methods ?? [];
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach ($methods as $key => $label)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="allowed_payment_methods[]" value="{{ $key }}" @checked(in_array($key, $allowed)) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </x-card>

            <div class="mt-6 flex justify-end">
                <x-button type="submit" color="primary">
                    <i class="fas fa-save mr-1"></i> Salvar Configurações
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection