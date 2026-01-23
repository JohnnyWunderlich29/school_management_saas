@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">

            <x-breadcrumbs :items="[
                [
                    'title' => 'Configurações',
                    'url' => route('settings.index', $currentSchoolId ? ['school_id' => $currentSchoolId] : []),
                ],
                ['title' => 'Central', 'url' => '#'],
            ]" />
            <!-- Conteúdo -->
            <x-card>
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Central de Configurações</h1>
                    <p class="mt-1 text-sm text-gray-600">Acesse configurações financeiras, educacionais e administrativas em
                        um único lugar.</p>
                </div>

                <!-- Navegação de Abas -->
                <div class="mb-6">
                    <!-- Mobile Tab Selector -->
                    <div class="sm:hidden mb-4">
                        <label for="settings-tabs-mobile" class="sr-only">Selecionar Aba</label>
                        <select id="settings-tabs-mobile"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="educacional" {{ $tab === 'educacional' ? 'selected' : '' }}>Educacional</option>
                            <option value="financeiro" {{ $tab === 'financeiro' ? 'selected' : '' }}>Financeiro</option>
                            <option value="importacao" {{ $tab === 'importacao' ? 'selected' : '' }}>Importação</option>
                        </select>
                    </div>

                    <!-- Desktop Tabs -->
                    <div class="hidden sm:block border-b border-gray-200">
                        <nav id="settings-tabs-nav" class="-mb-px flex space-x-8" aria-label="Tabs">
                            <a href="{{ route('settings.index', ['tab' => 'educacional'] + ($currentSchoolId ? ['school_id' => $currentSchoolId] : [])) }}"
                                data-tab="educacional"
                                class="settings-tab-link {{ $tab === 'educacional' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-graduation-cap mr-2"></i>
                                Educacional
                            </a>
                            <a href="{{ route('settings.index', ['tab' => 'financeiro'] + ($currentSchoolId ? ['school_id' => $currentSchoolId] : [])) }}"
                                data-tab="financeiro"
                                class="settings-tab-link {{ $tab === 'financeiro' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-coins mr-2"></i>
                                Financeiro
                            </a>
                            <a href="{{ route('settings.index', ['tab' => 'importacao'] + ($currentSchoolId ? ['school_id' => $currentSchoolId] : [])) }}"
                                data-tab="importacao"
                                class="settings-tab-link {{ $tab === 'importacao' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center">
                                <i class="fas fa-file-import mr-2"></i>
                                Importação
                            </a>
                        </nav>
                    </div>
                </div>


                <div id="settings-content">
                    <div id="settings-educacional" class="aspect-auto w-full {{ $tab === 'educacional' ? '' : 'hidden' }}">
                        @if ($tab === 'educacional')
                            @if ($currentSchoolId)
                                @permission('usuarios.editar')
                                    @include('settings.partials.educacional')
                                @else
                                    <div class="p-6 text-gray-600">
                                        Você não tem permissão para acessar as configurações educacionais. Solicite a permissão
                                        'usuarios.editar' ao administrador.
                                    </div>
                                @endpermission
                            @else
                                <div class="p-6 text-gray-600">Selecione uma escola para acessar as configurações
                                    educacionais.</div>
                            @endif
                        @endif
                    </div>
                    <div id="settings-financeiro" class="aspect-auto w-full {{ $tab === 'financeiro' ? '' : 'hidden' }}">
                        @if ($tab === 'financeiro')
                            @if ($currentSchoolId)
                                @php
                                    $canFinance =
                                        auth()->check() &&
                                        (auth()->user()->isSuperAdmin() ||
                                            auth()->user()->temCargo('Suporte') ||
                                            auth()->user()->temCargo('Suporte Técnico') ||
                                            auth()->user()->temPermissao('finance.admin') ||
                                            auth()->user()->temPermissao('financeiro.admin'));
                                @endphp
                                @if ($canFinance)
                                    @include('settings.partials.financeiro')
                                @else
                                    <div class="p-6 text-gray-600">
                                        Você não tem permissão para acessar as configurações financeiras. Solicite a
                                        permissão 'financeiro.admin' ao administrador.
                                    </div>
                                @endif
                            @else
                                <div class="p-6 text-gray-600">Selecione uma escola para acessar as configurações
                                    financeiras.</div>
                            @endif
                        @endif
                    </div>
                    <div id="settings-importacao" class="aspect-auto w-full {{ $tab === 'importacao' ? '' : 'hidden' }}">
                        @if ($tab === 'importacao')
                            @permission('usuarios.editar')
                                @include('settings.partials.importacao')
                            @else
                                <div class="p-6 text-gray-600">
                                    Você não tem permissão para acessar as ferramentas de importação.
                                </div>
                            @endpermission
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const nav = document.getElementById('settings-tabs-nav');
            if (!nav) return;
            const links = nav.querySelectorAll('.settings-tab-link');
            const contents = document.querySelectorAll('#settings-content > div');

            function animateSwitch(currentEl, nextEl) {
                if (!currentEl || !nextEl || currentEl === nextEl) return;
                // Prepare next element for fade-in
                nextEl.classList.remove('hidden');
                nextEl.classList.add('opacity-0', 'transform', '-translate-y-1');
                nextEl.classList.add('transition-opacity', 'duration-200', 'ease-out');

                // Fade-out current element
                currentEl.classList.add('transition-opacity', 'duration-200', 'ease-out');
                currentEl.classList.add('opacity-0', 'transform', 'translate-y-1');

                // After transition, hide current and show next
                setTimeout(() => {
                    currentEl.classList.add('hidden');
                    currentEl.classList.remove('opacity-0', 'transform', 'translate-y-1',
                        'transition-opacity', 'duration-200', 'ease-out');

                    // Trigger fade-in of next
                    requestAnimationFrame(() => {
                        nextEl.classList.remove('opacity-0', '-translate-y-1');
                        nextEl.classList.add('opacity-100');
                        // Cleanup classes after transition completes
                        setTimeout(() => {
                            nextEl.classList.remove('opacity-100', 'transform',
                                'transition-opacity', 'duration-200', 'ease-out');
                        }, 220);
                    });
                }, 200);
            }

            async function ensureLoaded(tab, href) {
                const targetId = `settings-${tab}`;
                const container = document.getElementById(targetId);
                if (!container) return;
                if (container.dataset.loaded === 'true') return;

                // Se já houver conteúdo dentro (renderizado pelo servidor na carga inicial), marcar como carregado
                if (container.children.length > 0 && container.querySelector('.p-6') === null) {
                    // Verificação simples: se tem filhos e não é apenas a mensagem de "Selecione uma escola" ou erro
                    // Mas para garantir, vamos carregar via AJAX se clicar. 
                    // No entanto, se o $tab inicial for este, ele já vem preenchido.
                }

                try {
                    const response = await fetch(href, {
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await response.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const remote = doc.getElementById(targetId);
                    if (remote) {
                        container.innerHTML = remote.innerHTML;
                        // Executar scripts internos
                        remote.querySelectorAll('script').forEach(s => {
                            const ns = document.createElement('script');
                            if (s.src) {
                                ns.src = s.src;
                            } else {
                                ns.textContent = s.textContent;
                            }
                            document.body.appendChild(ns);
                        });
                        container.dataset.loaded = 'true';
                    }
                } catch (e) {
                    console.error('Falha ao carregar conteúdo da aba', tab, e);
                }
            }

            links.forEach(a => {
                a.addEventListener('click', function(evt) {
                    evt.preventDefault();
                    const tab = a.dataset.tab || 'educacional';
                    setActive(tab, a.href);
                });
            });

            const mobileSelect = document.getElementById('settings-tabs-mobile');
            if (mobileSelect) {
                mobileSelect.addEventListener('change', function() {
                    const tab = this.value;
                    const link = Array.from(links).find(l => (l.dataset.tab || '') === tab);
                    if (link) {
                        setActive(tab, link.href);
                    }
                });
            }

            async function setActive(tab, href) {
                const next = document.getElementById(`settings-${tab}`);
                if (!next) return;

                const current = Array.from(contents).find(c => !c.classList.contains('hidden'));

                await ensureLoaded(tab, href);

                if (current && current !== next) {
                    animateSwitch(current, next);
                } else {
                    next.classList.remove('hidden');
                }

                links.forEach(a => {
                    const isActive = a.dataset.tab === tab;
                    a.classList.toggle('border-indigo-500', isActive);
                    a.classList.toggle('text-indigo-600', isActive);
                    a.classList.toggle('border-transparent', !isActive);
                    a.classList.toggle('text-gray-500', !isActive);
                });

                if (mobileSelect) {
                    mobileSelect.value = tab;
                }

                try {
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.pushState({
                        tab
                    }, '', url.toString());
                } catch (e) {}
            }

            window.addEventListener('popstate', function(event) {
                const tab = (event.state && event.state.tab) || new URL(window.location.href).searchParams
                    .get('tab') || 'educacional';
                const link = Array.from(links).find(l => (l.dataset.tab || '') === tab);
                setActive(tab, link ? link.href : window.location.href);
            });

            // Marcar a aba inicial como carregada
            const initialTab = new URL(window.location.href).searchParams.get('tab') || 'educacional';
            const initialContainer = document.getElementById(`settings-${initialTab}`);
            if (initialContainer && initialContainer.children.length > 0) {
                initialContainer.dataset.loaded = 'true';
            }
        });
    </script>
@endpush
