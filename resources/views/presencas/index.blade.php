@extends('layouts.app')

@section('title', 'Presenças')

@section('content')
<x-card>
    <div class="flex flex-col md:flex-row md:justify-between mb-6 space-y-3 sm:space-y-0">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Presenças</h1>
            <p class="mt-1 text-sm text-gray-600">Acompanhe registros por sala e período.</p>
        </div>
        <div class="flex flex-col w-full space-y-2 gap-2 self-center md:mt-4 sm:space-y-0 sm:space-x-2 md:flex-row">
            <x-button href="{{ route('presencas.registro-rapido') }}" color="primary" size="md" class="sm:justify-center whitespace-nowrap">
                <i class="fas fa-list-check mr-1"></i> Registro Rápido
            </x-button>
            <x-button href="{{ route('presencas.lancar') }}" color="primary" size="md" class="sm:justify-center whitespace-nowrap">
                <i class="fas fa-calendar-check mr-1"></i> Lançar Presenças
            </x-button>
        </div>
    </div>

    <x-collapsible-filter title="Filtros de Presenças" :action="route('presencas.index')" :clear-route="route('presencas.index')" target="presencas-list-wrapper">
        <x-filter-field name="sala_id" label="Sala" type="select" emptyOption="Todas as salas" :options="$todasSalas->pluck('nome_completo','id')" />
        <x-date-filter-with-arrows 
            name="data_inicio" 
            label="Data Início" 
            :value="$dataInicio"
            data-fim-name="data_fim"
            :data-fim-value="$dataFim"
        />
    </x-collapsible-filter>

    @php
        $totalAlunos = $salasComEstatisticas->sum('total_alunos');
        $presencasRegistradas = $salasComEstatisticas->sum('presencas_registradas');
        $presentes = $salasComEstatisticas->sum('presentes');
        $ausentes = $salasComEstatisticas->sum('ausentes');
        $naoRegistrados = $salasComEstatisticas->sum('nao_registrados');
        $taxaPresenca = $totalAlunos > 0 ? round(($presentes / max($totalAlunos,1)) * 100) : 0;
    @endphp

    <div id="presencas-list-wrapper" class="mt-4 relative">
        <x-loading-overlay message="Atualizando presenças..." />
        <div data-ajax-content>
            <!-- Desktop table -->
            <div class="hidden md:block">
                <x-table 
                    :headers="[
                        ['label' => 'Sala', 'sort' => 'nome'],
                        ['label' => 'Código', 'sort' => 'codigo'],
                        ['label' => 'Total Alunos', 'sort' => 'total_alunos'],
                        ['label' => 'Presenças Registradas', 'sort' => 'presencas_registradas'],
                        ['label' => 'Presentes', 'sort' => 'presentes'],
                        ['label' => 'Ausentes', 'sort' => 'ausentes'],
                        ['label' => 'Não Registrados', 'sort' => 'nao_registrados'],
                        'Ações'
                    ]"
                    striped
                    hover
                    responsive
                    sortable
                    :currentSort="request('sort')"
                    :currentDirection="request('direction', 'asc')"
                >
                    <x-table-body>
                        @forelse($salasComEstatisticas as $index => $sala)
                            <x-table-row :index="$index">
                                <x-table-cell>{{ $sala->nome ?? '-' }}</x-table-cell>
                                <x-table-cell>{{ $sala->codigo ?? '-' }}</x-table-cell>
                                <x-table-cell>{{ $sala->total_alunos ?? 0 }}</x-table-cell>
                                <x-table-cell>{{ $sala->presencas_registradas ?? 0 }}</x-table-cell>
                                <x-table-cell>{{ $sala->presentes ?? 0 }}</x-table-cell>
                                <x-table-cell>{{ $sala->ausentes ?? 0 }}</x-table-cell>
                                <x-table-cell>{{ $sala->nao_registrados ?? 0 }}</x-table-cell>
                                <x-table-cell>
                                    <div class="flex flex-row flex-wrap items-center w-full gap-2 sm:gap-3">
                                        <x-button href="{{ route('presencas.show', ['data' => $dataInicio, 'sala_id' => $sala->id]) }}" color="secondary" size="sm" class="flex-nowrap">Ver Dia</x-button>
                                        <x-button href="{{ route('presencas.lancar', ['sala_id' => $sala->id, 'data_inicio' => $dataInicio]) }}" color="primary" size="sm" class="flex-nowrap">Lançar</x-button>
                                    </div>
                                </x-table-cell>
                            </x-table-row>
                        @empty
                            <x-table-row :index="0">
                                <x-table-cell colspan="8" align="center">Nenhuma sala encontrada no período selecionado.</x-table-cell>
                            </x-table-row>
                        @endforelse
                    </x-table-body>
                </x-table>
            </div>

            <!-- Mobile cards -->
            <div class="md:hidden space-y-3">
                @forelse($salasComEstatisticas as $sala)
                    <x-card>
                        <div class="flex flex-col w-full justify-between items-start md:flex-row">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">{{ $sala->nome ?? '-' }}</h3>
                                <p class="text-xs text-gray-500">Cód. {{ $sala->codigo ?? '-' }}</p>
                            </div>
                            <div class="flex mt-2 w-full  gap-2 md:flex-row">
                                <x-button href="{{ route('presencas.show', ['data' => $dataInicio, 'sala_id' => $sala->id]) }}" color="secondary" size="sm" class="whitespace-nowrap">Ver Dia</x-button>
                                <x-button href="{{ route('presencas.lancar', ['sala_id' => $sala->id, 'data_inicio' => $dataInicio]) }}" color="primary" size="sm" class="whitespace-nowrap">Lançar</x-button>
                            </div>
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                            <div>Total alunos: <span class="font-semibold">{{ $sala->total_alunos ?? 0 }}</span></div>
                            <div>Registradas: <span class="font-semibold">{{ $sala->presencas_registradas ?? 0 }}</span></div>
                            <div>Presentes: <span class="font-semibold text-green-600">{{ $sala->presentes ?? 0 }}</span></div>
                            <div>Ausentes: <span class="font-semibold text-red-600">{{ $sala->ausentes ?? 0 }}</span></div>
                            <div>Não registrados: <span class="font-semibold text-gray-700">{{ $sala->nao_registrados ?? 0 }}</span></div>
                        </div>
                    </x-card>
                @empty
                    <x-card>
                        <p class="text-center text-sm">Nenhuma sala encontrada no período selecionado.</p>
                    </x-card>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $salasComEstatisticas->links('components.pagination') }}
            </div>
        </div>
    </div>
</x-card>

<script>
(function() {
    const wrapperId = 'presencas-list-wrapper';

    function showPresencasLoading() {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]');
        if (overlay) overlay.classList.remove('hidden');
    }

    function hidePresencasLoading() {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        const overlay = wrapper.querySelector('[data-loading-overlay]');
        if (overlay) overlay.classList.add('hidden');
    }

    async function updatePresencasContainer(url, pushState = true) {
        showPresencasLoading();
        try {
            const resp = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }});
            const html = await resp.text();
            const temp = document.createElement('div');
            temp.innerHTML = html;
            const newWrapper = temp.querySelector('#' + wrapperId);
            const newContent = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;

            const wrapper = document.getElementById(wrapperId);
            const content = wrapper ? wrapper.querySelector('[data-ajax-content]') : null;

            if (newContent && content) {
                content.innerHTML = newContent.innerHTML;
                if (pushState) history.pushState({}, '', url);
                initPresencasAjaxBindings();
            }
        } catch (e) {
            console.error('Erro ao atualizar presenças:', e);
        } finally {
            hidePresencasLoading();
        }
    }

    function initPresencasAjaxBindings() {
        const wrapper = document.getElementById(wrapperId);
        if (!wrapper) return;
        const content = wrapper.querySelector('[data-ajax-content]');
        if (!content) return;

        // Paginação
        content.querySelectorAll('nav[aria-label="Pagination Navigation"] a[href]').forEach(a => {
            a.addEventListener('click', (ev) => {
                ev.preventDefault();
                // Use a.href para obter a URL resolvida (sem &amp;)
                const url = a.href;
                if (url) updatePresencasContainer(url);
            }, { once: true });
        });

        // (Opcional) Sort em cabeçalhos, se houver
        content.querySelectorAll('thead a[href]').forEach(a => {
            a.addEventListener('click', (ev) => {
                ev.preventDefault();
                // Use a.href para obter a URL resolvida (sem &amp;)
                const url = a.href;
                if (url) updatePresencasContainer(url);
            }, { once: true });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initPresencasAjaxBindings();
    });

    window.addEventListener('popstate', () => {
        updatePresencasContainer(window.location.href, false);
    });
})();
</script>

@endsection