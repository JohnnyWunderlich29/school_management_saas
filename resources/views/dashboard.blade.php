@extends('layouts.app')

@section('content')
    @include('dashboard.partials._modals')

    <!-- Header Section -->
    <x-card class="mb-6 md:mb-8">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <h1 class="text-xl md:text-2xl lg:text-3xl font-bold text-gray-900">Dashboard Analítico</h1>
                <p class="mt-1 md:mt-2 text-sm md:text-base text-gray-600">Visão geral e análise de dados do sistema escolar
                </p>
            </div>
            <div class="flex items-center space-x-3 gap-2">
                <div class="text-right">
                    <p class="text-xs md:text-sm text-gray-500">Última atualização</p>
                    <p class="text-xs md:text-sm font-medium text-gray-900">{{ now()->format('d/m/Y H:i') }}</p>
                </div>
            </div>
        </div>
    </x-card>

    @include('dashboard.partials._stats')


    @include('dashboard.partials._finance')
    <!-- Quick Actions Section -->
    @include('dashboard.partials._quick_actions')
    <!-- Analytics Section -->
    @include('dashboard.partials._analytics')


@endsection

    @include('dashboard.partials._scripts')
