@extends('layouts.app')

@section('content')
    <x-card>
        <div class="flex flex-col mb-6 space-y-4 md:flex-row justify-between md:space-y-0 md:items-center">
            <div>
                <h1 class="text-lg md:text-2xl font-semibold text-gray-900">Provas</h1>
                <p class="mt-1 text-sm text-gray-600">Gerenciamento de avaliações e exames</p>
            </div>
            <div class="flex flex-col gap-2 space-y-2 sm:space-y-0 sm:space-x-2 md:flex-row">
                @if (auth()->user()->temPermissao('provas.criar'))
                    <x-button href="{{ route('provas.create') }}" color="primary" class="w-auto sm:justify-center">
                        <i class="fas fa-plus mr-1"></i>
                        <span class="hidden md:inline">Nova Prova</span>
                        <span class="md:hidden">Nova</span>
                    </x-button>
                @endif
            </div>
        </div>

        <x-collapsible-filter title="Filtros de Provas" :action="route('provas.index')" :clear-route="route('provas.index')" target="provas-list-wrapper">
            <x-filter-field name="titulo" label="Título" placeholder="Buscar por título..." :value="request('titulo')" />

            <x-filter-field name="turma_id" label="Turma" type="select" :options="$turmas->pluck('nome', 'id')->toArray()" :value="request('turma_id')" />

            <x-filter-field name="disciplina_id" label="Disciplina" type="select" :options="$disciplinas->pluck('nome', 'id')->toArray()" :value="request('disciplina_id')" />

            <x-filter-field name="status" label="Status" type="select" :options="[
                'todos' => 'Todos',
                'rascunho' => 'Rascunho',
                'publicada' => 'Publicada',
                'finalizada' => 'Finalizada',
            ]" :value="request('status', 'todos')" />
        </x-collapsible-filter>

        <div id="provas-list-wrapper" class="relative">
            <x-loading-overlay message="Atualizando provas..." />
            <div data-ajax-content>
                <!-- Desktop Table - Hidden on mobile -->
                <div class="hidden md:block">
                    <x-table :headers="[
                        ['label' => 'Título', 'sort' => 'titulo'],
                        ['label' => 'Turma / Disciplina'],
                        ['label' => 'Data Aplicação', 'sort' => 'data_aplicacao'],
                        ['label' => 'Professor'],
                        ['label' => 'Status', 'sort' => 'status'],
                    ]" :actions="true" striped hover responsive sortable :currentSort="request('sort')"
                        :currentDirection="request('direction', 'desc')">
                        @forelse($provas as $index => $prova)
                            <x-table-row :striped="true" :index="$index">
                                <x-table-cell>
                                    <div class="font-medium text-gray-900">{{ $prova->titulo }}</div>
                                    <div class="text-xs text-gray-500">{{ Str::limit($prova->descricao, 50) }}</div>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="text-sm font-semibold text-indigo-600">{{ $prova->turma->nome }}</div>
                                    <div class="text-xs text-gray-600">{{ $prova->disciplina->nome }}</div>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="flex items-center">
                                        <i class="far fa-calendar-alt mr-2 text-gray-400"></i>
                                        {{ $prova->data_aplicacao->format('d/m/Y') }}
                                    </div>
                                </x-table-cell>
                                <x-table-cell>
                                    <div class="text-sm text-gray-700">
                                        {{ $prova->professor ? $prova->professor->nome_completo : 'N/A' }}</div>
                                </x-table-cell>
                                <x-table-cell>
                                    @php
                                        $statusClasses = [
                                            'rascunho' => 'bg-gray-100 text-gray-800',
                                            'publicada' => 'bg-green-100 text-green-800',
                                            'finalizada' => 'bg-blue-100 text-blue-800',
                                        ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$prova->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($prova->status) }}
                                    </span>
                                </x-table-cell>
                                <x-table-cell align="right">
                                    <div class="flex justify-end space-x-2">
                                        @if (auth()->user()->temPermissao('provas.exportar'))
                                            <a href="{{ route('provas.pdf-export', $prova->id) }}"
                                                class="text-green-600 hover:text-green-900" title="Gerar PDF"
                                                target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->temPermissao('provas.editar'))
                                            <a href="{{ route('provas.edit', $prova->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if (auth()->user()->temPermissao('provas.excluir'))
                                            <form action="{{ route('provas.destroy', $prova->id) }}" method="POST"
                                                class="inline-block"
                                                onsubmit="return confirm('Tem certeza que deseja excluir esta prova?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900"
                                                    title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </x-table-cell>
                            </x-table-row>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Nenhuma prova encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>

                <!-- Mobile Cards - Visible only on mobile -->
                <div class="md:hidden space-y-3">
                    @forelse($provas as $prova)
                        <div
                            class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 truncate leading-tight">
                                        {{ $prova->titulo }}</h3>
                                    <p class="text-sm text-indigo-600 font-medium">{{ $prova->turma->nome }} •
                                        {{ $prova->disciplina->nome }}</p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $statusClasses[$prova->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($prova->status) }}
                                </span>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-3 mb-4 grid grid-cols-2 gap-2">
                                <div>
                                    <span class="text-xs text-gray-500 block mb-1">Data</span>
                                    <span
                                        class="text-sm font-semibold text-gray-900">{{ $prova->data_aplicacao->format('d/m/Y') }}</span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500 block mb-1">Professor</span>
                                    <span
                                        class="text-sm font-semibold text-gray-900 truncate block">{{ $prova->professor ? $prova->professor->nome : 'N/A' }}</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-2">
                                @if (auth()->user()->temPermissao('provas.exportar'))
                                    <a href="{{ route('provas.pdf-export', $prova->id) }}"
                                        class="bg-green-600 hover:bg-green-700 text-white text-center py-2.5 px-3 rounded-lg font-medium text-sm flex items-center justify-center transition-all"
                                        target="_blank">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                @endif
                                @if (auth()->user()->temPermissao('provas.editar'))
                                    <a href="{{ route('provas.edit', $prova->id) }}"
                                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2.5 px-3 rounded-lg font-medium text-sm flex items-center justify-center transition-all">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                                @if (auth()->user()->temPermissao('provas.excluir'))
                                    <form action="{{ route('provas.destroy', $prova->id) }}" method="POST" class="w-full"
                                        onsubmit="return confirm('Tem certeza que deseja excluir esta prova?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="w-full bg-red-50 hover:bg-red-100 text-red-600 text-center py-2.5 px-3 rounded-lg font-medium text-sm flex items-center justify-center transition-all">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center text-gray-500">
                            Nenhuma prova encontrada.
                        </div>
                    @endforelse
                </div>

                <div class="mt-4">
                    {{ $provas->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </div>
    </x-card>

    @push('scripts')
        <script>
            function showProvasLoading() {
                const wrapper = document.getElementById('provas-list-wrapper');
                if (!wrapper) return;
                const overlay = wrapper.querySelector('[data-loading-overlay]');
                if (overlay) overlay.classList.remove('hidden');
                wrapper.style.pointerEvents = 'none';
            }

            function hideProvasLoading() {
                const wrapper = document.getElementById('provas-list-wrapper');
                if (!wrapper) return;
                const overlay = wrapper.querySelector('[data-loading-overlay]');
                if (overlay) overlay.classList.add('hidden');
                wrapper.style.pointerEvents = '';
            }

            function updateProvasContainer(url, pushState = true) {
                const wrapper = document.getElementById('provas-list-wrapper');
                if (!wrapper) {
                    window.location.href = url;
                    return;
                }
                const ajaxArea = wrapper.querySelector('[data-ajax-content]');
                if (!ajaxArea) {
                    window.location.href = url;
                    return;
                }

                showProvasLoading();
                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(resp => resp.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const newWrapper = doc.querySelector('#provas-list-wrapper');
                        const newAjaxArea = newWrapper ? newWrapper.querySelector('[data-ajax-content]') : null;

                        if (newAjaxArea) {
                            ajaxArea.innerHTML = newAjaxArea.innerHTML;
                            if (pushState) window.history.pushState(null, '', url);
                            initProvaAjaxBindings();
                        } else {
                            window.location.href = url;
                        }
                    })
                    .catch(() => {
                        window.location.href = url;
                    })
                    .finally(() => {
                        hideProvasLoading();
                    });
            }

            function initProvaAjaxBindings() {
                const wrapper = document.getElementById('provas-list-wrapper');
                if (!wrapper) return;
                const ajaxArea = wrapper.querySelector('[data-ajax-content]');
                if (!ajaxArea) return;

                // Ordenação
                const sortLinks = ajaxArea.querySelectorAll('thead a[href]');
                sortLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        updateProvasContainer(this.href);
                    });
                });

                // Paginação
                const paginationLinks = ajaxArea.querySelectorAll('nav a[href]');
                paginationLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        updateProvasContainer(this.href);
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', initProvaAjaxBindings);
            window.addEventListener('popstate', () => updateProvasContainer(window.location.href, false));
        </script>
    @endpush
@endsection
