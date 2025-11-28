@extends('corporativo.layout')

@section('title', 'Módulos - Sistema Corporativo')
@section('page-title', 'Módulos do Sistema')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Lista de Módulos -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center flex-wrap justify-between mb-4">
                        <div class="flex flex-row items-center">
                            <h3 class="text-lg font-medium text-gray-900">Lista de Módulos</h3>
                        </div>
                        @if (auth()->user() && auth()->user()->isSuperAdmin())
                            <button type="button" onclick="openCreateModuleModal();"
                                class="inline-flex items-center px-3 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Novo Módulo
                            </button>
                        @endif

                        <div class="flex space-x-3">
                            <!-- Filtro por Categoria -->
                            <select id="filter-category"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Todas as Categorias</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ strtolower($cat) }}">{{ $cat }}</option>
                                @endforeach
                            </select>

                            <!-- Filtro por Status -->
                            <select id="filter-status"
                                class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Todos os Status</option>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>

                            <!-- Campo de Busca -->
                            <div class="relative">
                                <input type="text" id="search-modules" placeholder="Buscar módulos..."
                                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Chave</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Core</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="modules-table">
                            @foreach ($modules as $module)
                                @php
                                    $statusText = $module->is_active ? 'ativo' : 'inativo';
                                    $categoryText = strtolower($module->category ?? '');
                                @endphp
                                <tr class="hover:bg-gray-50 module-row"
                                    data-name="{{ strtolower($module->display_name ?? $module->name) }}"
                                    data-key="{{ strtolower($module->name) }}"
                                    data-category="{{ $categoryText }}"
                                    data-status="{{ $statusText }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $module->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $module->display_name ?? $module->name }}</div>
                                        @if($module->description)
                                            <div class="text-xs text-gray-500">{{ $module->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $module->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($module->category)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">{{ $module->category }}</span>
                                        @else
                                            <span class="text-xs text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if(!is_null($module->price))
                                            R$ {{ number_format($module->price, 2, ',', '.') }}
                                        @else
                                            <span class="text-xs text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($module->is_core)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Core</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Opcional</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($module->is_active)
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Ativo</span>
                                        @else
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex items-center space-x-3">
                                        <a href="{{ route('corporativo.modules.edit', $module) }}"
                                           data-edit-url="{{ route('corporativo.modules.edit', $module) }}"
                                           onclick="openEditModuleModal(this.dataset.editUrl); return false;"
                                           class="text-blue-600 hover:text-blue-900 transition-colors" title="Editar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2m2 0h2m-4 4h2m-6 0h2m2 4h2m-6 0h2M5 7h2m-2 4h2m-2 4h2"></path>
                                            </svg>
                                        </a>
                                        @if (auth()->user() && auth()->user()->isSuperAdmin() && $module->is_active)
                                            <button
                                                class="text-red-600 hover:text-red-800 transition-colors"
                                                title="Inativar"
                                                data-deactivate-url="{{ route('corporativo.modules.deactivate', $module) }}"
                                                onclick="deactivateModule(this.dataset.deactivateUrl); return false;">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 01-8 0M12 14v7m0-7a7 7 0 100-14 7 7 0 000 14z" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="text-sm text-gray-600">
                        @if ($modules->total() > 0)
                            Mostrando
                            <span class="font-medium">{{ $modules->firstItem() }}</span>
                            –
                            <span class="font-medium">{{ $modules->lastItem() }}</span>
                            de
                            <span class="font-medium">{{ $modules->total() }}</span>
                            resultados
                        @else
                            Nenhum resultado encontrado
                        @endif
                    </div>
                    <div>
                        {{ $modules->onEachSide(1)->links('vendor.pagination.corporativo') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Total de Módulos</span>
                        <span class="text-sm font-medium text-gray-900">{{ $stats['total'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Ativos</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $stats['ativos'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Inativos</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $stats['inativos'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Core</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ $stats['cores'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Opcional</span>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $stats['nao_cores'] }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ações Rápidas</h3>
                <div class="space-y-3">
                    <a href="{{ route('corporativo.modules.index') }}"
                        class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Atualizar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Módulo -->
    <div id="editModuleModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white w-full max-w-2xl rounded-lg shadow-lg overflow-hidden" role="dialog" aria-modal="true">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 id="editModuleModalTitle" class="text-lg font-semibold text-gray-900">Editar Módulo</h3>
                <button type="button" onclick="closeEditModuleModal()" class="text-gray-500 hover:text-gray-700" aria-label="Fechar">&times;</button>
            </div>
            <div id="editModuleContent" class="p-6 max-h-[70vh] overflow-y-auto">
                <div class="text-sm text-gray-500">Carregando...</div>
            </div>
        </div>
        <span class="sr-only">Modal Overlay</span>
        <script>
            function openEditModuleModal(editUrl) {
                const modal = document.getElementById('editModuleModal');
                const content = document.getElementById('editModuleContent');
                const title = document.getElementById('editModuleModalTitle');
                content.innerHTML = '<div class="text-sm text-gray-500">Carregando...</div>';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                if (title) title.textContent = 'Editar Módulo';

                const url = editUrl + (editUrl.includes('?') ? '&' : '?') + 'partial=1';
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => { content.innerHTML = html; })
                    .catch(() => { content.innerHTML = '<div class="text-red-600">Erro ao carregar formulário.</div>'; });
            }

            function openCreateModuleModal() {
                const modal = document.getElementById('editModuleModal');
                const content = document.getElementById('editModuleContent');
                const title = document.getElementById('editModuleModalTitle');
                content.innerHTML = '<div class="text-sm text-gray-500">Carregando...</div>';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                if (title) title.textContent = 'Novo Módulo';

                const url = '{{ route('corporativo.modules.create') }}?partial=1';
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.text())
                    .then(html => { content.innerHTML = html; })
                    .catch(() => { content.innerHTML = '<div class="text-red-600">Erro ao carregar formulário.</div>'; });
            }

            function closeEditModuleModal() {
                const modal = document.getElementById('editModuleModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            // Fechar ao clicar fora do card
            document.addEventListener('click', function(e) {
                const modal = document.getElementById('editModuleModal');
                if (!modal.classList.contains('hidden')) {
                    if (e.target === modal) closeEditModuleModal();
                }
            });

            // Interceptar envio do formulário do modal
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.id === 'edit-module-form') {
                    e.preventDefault();
                    const form = e.target;
                    const url = form.action;
                    const formData = new FormData(form);
                    fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData })
                        .then(async (r) => {
                            if (!r.ok) {
                                let data = null;
                                try { data = await r.json(); } catch (e) {}
                                throw { status: r.status, data };
                            }
                            return r.text();
                        })
                        .then(() => { closeEditModuleModal(); window.location.reload(); })
                        .catch(err => {
                            const content = document.getElementById('editModuleContent');
                            let msg = 'Erro ao salvar.';
                            if (err && err.data && err.data.errors) {
                                const items = Object.values(err.data.errors).flat().map(m => `<li>${m}</li>`).join('');
                                msg = `<ul class=\"list-disc list-inside text-sm text-red-700\">${items}</ul>`;
                            }
                            const alert = `<div class=\"mb-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded\">${msg}</div>`;
                            content.insertAdjacentHTML('afterbegin', alert);
                        });
                }
            });

            function deactivateModule(url) {
                if (!confirm('Tem certeza que deseja inativar este módulo?')) return;
                fetch(url, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: new URLSearchParams({ _method: 'PUT', _token: '{{ csrf_token() }}' }) })
                    .then(() => window.location.reload())
                    .catch(() => alert('Erro ao inativar módulo.'));
            }

            function filterModules() {
                const searchTerm = (document.getElementById('search-modules').value || '').toLowerCase();
                const categoryFilter = (document.getElementById('filter-category').value || '').toLowerCase();
                const statusFilter = (document.getElementById('filter-status').value || '').toLowerCase();

                const rows = document.querySelectorAll('#modules-table tr.module-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name') || '';
                    const key = row.getAttribute('data-key') || '';
                    const category = row.getAttribute('data-category') || '';
                    const status = row.getAttribute('data-status') || '';

                    const matchesSearch = name.includes(searchTerm) || key.includes(searchTerm);
                    const matchesCategory = !categoryFilter || category === categoryFilter;
                    const matchesStatus = !statusFilter || status === statusFilter;

                    row.style.display = (matchesSearch && matchesCategory && matchesStatus) ? '' : 'none';
                });
            }

            document.getElementById('search-modules').addEventListener('input', filterModules);
            document.getElementById('filter-category').addEventListener('change', filterModules);
            document.getElementById('filter-status').addEventListener('change', filterModules);
        </script>
    </div>
@endsection