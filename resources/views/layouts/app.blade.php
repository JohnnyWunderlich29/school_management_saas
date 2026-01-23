<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @auth
        <!-- Meta tags para debug -->
        <meta name="user-data"
            content="{{ json_encode([
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
                'escola_id' => auth()->user()->escola_id,
                'is_super_admin' => auth()->user()->isSuperAdmin(),
                'has_suporte' => auth()->user()->temCargo('Suporte'),
                'session_escola' => session('escola_atual'),
                'current_url' => request()->url(),
            ]) }}">
    @endauth

    <title>{{ config('app.name', 'Escola SaaS') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Ziggy Routes -->
    @routes
    <script>
        // Disponibiliza Ziggy para módulos ESM
        window.Ziggy = Ziggy;
    </script>

    @auth
        <!-- Script de Debug para Escola -->
        <script src="{{ asset('js/escola-debug.js') }}"></script>
    @endauth
</head>

<body class="font-sans antialiased bg-gray-50">
    @auth
        <script>
            console.log('Debug Mini-Chat (Top of Body):');
            console.log('Module Access:', @json(function_exists('canAccessModule') && canAccessModule('comunicacao_module')));
            console.log('Permission:', @json(Auth::user()->temPermissao('conversas.ver')));
            console.log('Is Route Conversas:', @json(request()->routeIs('conversas.*')));
        </script>
        @if (function_exists('canAccessModule') &&
                canAccessModule('comunicacao_module') &&
                Auth::user()->temPermissao('conversas.ver') &&
                !request()->routeIs('conversas.*'))
            @include('partials.mini-chat')
        @endif
    @endauth

    <!-- Sistema de Alertas -->
    <x-alert-system />
    <!-- Barra de inadimplência fixa -->
    <x-delinquency-bar />

    <div class="min-h-screen flex">
        @auth
            @if (!request()->routeIs('login') && !request()->routeIs('register'))
                <x-sidebar />
            @endif
        @endauth

        <!-- Mobile menu component includes the bottom navigation -->
        <!-- Removed duplicate mobile sidebar toggle as it's handled by x-mobile-menu component -->

        @auth
            @if (!request()->routeIs('login') && !request()->routeIs('register'))
                <x-mobile-menu />
            @endif
        @endauth

        <!-- Main content -->
        <div
            class="@auth{{ !request()->routeIs('login') && !request()->routeIs('register') ? ' flex-1' : ' w-full' }}@else w-full @endauth flex flex-col overflow-hidden">
            <!-- Top navbar -->
            <header class="bg-white shadow-sm z-10">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-row justify-end items-center gap-2">
                    <div class="flex items-center">
                        @auth
                            <x-escola-switcher />
                        @else
                            <h1 class="text-xl font-semibold text-gray-900"></h1>
                        @endauth
                    </div>

                    <div class="flex items-center space-x-4">
                        @auth
                            <x-notification-dropdown />
                            <div class="relative">
                                <button id="userMenuBtn" class="flex items-center space-x-2 focus:outline-none">
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-white">
                                        <span
                                            class="text-sm font-medium">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    </div>
                                    <span
                                        class="hidden md:inline-block text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                                </button>
                                <div id="userMenu"
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden overflow-hidden">
                                    <!-- Atualizações (separado do restante do menu) -->
                                    <div class="px-2 py-2">
                                        <button id="openUpdatesModalBtn" type="button"
                                            class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded">
                                            <i class="fas fa-sync-alt mr-2 text-indigo-500"></i>
                                            Atualizações
                                        </button>
                                    </div>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="{{ route('profile.show') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <i class="fas fa-user mr-2 text-gray-400"></i>
                                        Perfil
                                    </a>
                                    <a href="{{ route('profile.settings') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <i class="fas fa-cog mr-2 text-gray-400"></i>
                                        Configurações
                                    </a>
                                    <a href="{{ route('profile.escola') }}"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                        <i class="fas fa-school mr-2 text-gray-400"></i>
                                        Dados da Escola
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex items-center">
                                            <i class="fas fa-sign-out-alt mr-2 text-gray-400"></i>
                                            Sair
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-gray-900">Login</a>
                                <a href="{{ route('register') }}"
                                    class="bg-indigo-600 text-white px-3 py-1 rounded-md text-sm hover:bg-indigo-700">Registrar</a>
                            </div>
                        @endauth
                    </div>
                    <x-right-menu />
                </div>
            </header>

            @auth
                @if (!request()->routeIs('login') && !request()->routeIs('register'))
                    <x-right-sidebar />
                @endif
            @endauth

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-6 pb-24 md:pb-6">
                <div id="page-content-container" class="md:max-w-full mx-auto">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>



    @auth
        <x-onboarding-bar />
    @endauth

    <!-- Modal de Atualizações -->
    <div id="updatesModal" class="fixed inset-0 z-[100] hidden">
        <div id="updatesModalOverlay" class="absolute inset-0 bg-black/40"></div>
        <div id="updatesModalPanel"
            class="relative mx-auto mt-8 w-[70vw] h-[90vh] max-w-none bg-white rounded-lg shadow-xl">
            <div class="relative px-4 py-3 border-b border-gray-200">
                <h1 id="updatesModalTitle"
                    class="text-base font-semibold text-gray-800 w-[80%] mx-auto text-center text-xl">Atualizações do
                    Sistema</h1>
                <button id="updatesModalCloseBtn" type="button"
                    class="p-2 rounded hover:bg-gray-100 absolute right-4 top-1/2 -translate-y-1/2">
                    <i class="fas fa-times text-gray-500"></i>
                </button>
            </div>
            <div id="updatesModalContent"
                class="px-4 py-4 max-h-[85vh] overflow-y-auto overflow-x-hidden text-sm text-gray-700">
                <img id="updatesModalImage" src="" alt="Imagem da atualização"
                    class="w-[80%] mx-auto mt-4 rounded hidden" />
                <div id="updatesModalBody" class="space-y-3 w-[90%] mx-auto">
                    <p class="text-gray-600">Sem novidades por enquanto.</p>
                </div>

                <div id="updatesModalTimestamp" class="mt-6 text-xs text-gray-500 w-[80%] mx-auto text-center"></div>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuContent = document.getElementById('mobileMenuContent');
            const closeMenu = document.getElementById('closeMenu');

            function openMobileMenu() {
                if (mobileMenu && mobileMenuContent) {
                    mobileMenu.classList.remove('hidden');
                    // Força o reflow para garantir que a transição funcione
                    mobileMenu.offsetHeight;
                    mobileMenuContent.classList.remove('-translate-x-full');
                }
            }

            function closeMobileMenu() {
                if (mobileMenu && mobileMenuContent) {
                    mobileMenuContent.classList.add('-translate-x-full');
                    setTimeout(() => {
                        if (mobileMenu) {
                            mobileMenu.classList.add('hidden');
                        }
                    }, 300); // Aguarda a animação terminar
                }
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    openMobileMenu();
                });
            }

            if (closeMenu) {
                closeMenu.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeMobileMenu();
                });
            }

            // Fechar menu ao clicar no overlay
            if (mobileMenu) {
                mobileMenu.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeMobileMenu();
                    }
                });
            }
        });

        // Animar dropdown do usuário (Profile)
        function animateUserMenu(menu, expand) {
            if (!menu) return;
            menu.style.transition = 'max-height 250ms ease, opacity 250ms ease';
            menu.style.willChange = 'max-height, opacity';
            if (expand) {
                menu.classList.remove('hidden');
                menu.style.opacity = '0';
                menu.style.maxHeight = '0px';
                requestAnimationFrame(() => {
                    menu.style.opacity = '1';
                    menu.style.maxHeight = menu.scrollHeight + 'px';
                });
            } else {
                menu.style.opacity = '1';
                menu.style.maxHeight = menu.scrollHeight + 'px';
                requestAnimationFrame(() => {
                    menu.style.opacity = '0';
                    menu.style.maxHeight = '0px';
                });
                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 250);
            }
        }

        // Toggle com animação
        document.getElementById('userMenuBtn')?.addEventListener('click', function() {
            const userMenu = document.getElementById('userMenu');
            if (!userMenu) return;
            const isHidden = userMenu.classList.contains('hidden');
            animateUserMenu(userMenu, isHidden);
        });

        // Modal de Atualizações
        let currentUpdateId = null;

        function openUpdatesModal() {
            const modal = document.getElementById('updatesModal');
            const overlay = document.getElementById('updatesModalOverlay');
            const panel = document.getElementById('updatesModalPanel');
            if (!modal || !overlay || !panel) return;
            modal.classList.remove('hidden');
            // Estado inicial
            overlay.style.opacity = '0';
            panel.style.opacity = '0';
            panel.style.transform = 'scale(0.95)';
            // Próximo frame aplica animação
            requestAnimationFrame(() => {
                overlay.style.transition = 'opacity 250ms ease';
                panel.style.transition = 'opacity 250ms ease, transform 250ms ease';
                overlay.style.opacity = '1';
                panel.style.opacity = '1';
                panel.style.transform = 'scale(1)';
            });
        }

        function closeUpdatesModal() {
            const modal = document.getElementById('updatesModal');
            const overlay = document.getElementById('updatesModalOverlay');
            const panel = document.getElementById('updatesModalPanel');
            if (!modal || !overlay || !panel) return;
            overlay.style.opacity = '0';
            panel.style.opacity = '0';
            panel.style.transform = 'scale(0.95)';
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 250);
        }

        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            // Check if click is outside user menu
            const userMenuBtn = document.getElementById('userMenuBtn');
            const userMenu = document.getElementById('userMenu');

            if (userMenuBtn && userMenu && !userMenuBtn.contains(e.target) && !userMenu.contains(e.target)) {
                if (!userMenu.classList.contains('hidden')) {
                    animateUserMenu(userMenu, false);
                }
            }
        });

        // Inicialização do estado e animação de clique
        document.addEventListener('DOMContentLoaded', function() {
            const userMenu = document.getElementById('userMenu');
            const userMenuBtn = document.getElementById('userMenuBtn');

            if (userMenu) {
                userMenu.style.overflow = 'hidden';
                userMenu.style.transition = 'max-height 250ms ease, opacity 250ms ease';
                if (userMenu.classList.contains('hidden')) {
                    userMenu.style.maxHeight = '0px';
                    userMenu.style.opacity = '0';
                } else {
                    userMenu.style.maxHeight = userMenu.scrollHeight + 'px';
                    userMenu.style.opacity = '1';
                }
            }

            // Animação de clique sutil
            if (userMenuBtn) {
                userMenuBtn.style.transition = (userMenuBtn.style.transition ? userMenuBtn.style.transition + ', ' :
                    '') + 'transform 150ms ease';
                userMenuBtn.addEventListener('mousedown', () => userMenuBtn.style.transform = 'scale(0.98)');
                const resetBtn = () => userMenuBtn.style.transform = '';
                userMenuBtn.addEventListener('mouseup', resetBtn);
                userMenuBtn.addEventListener('mouseleave', resetBtn);
            }

            document.querySelectorAll('#userMenu a, #userMenu button').forEach(el => {
                el.style.transition = (el.style.transition ? el.style.transition + ', ' : '') +
                    'transform 120ms ease';
                el.addEventListener('mousedown', () => el.style.transform = 'scale(0.98)');
                const reset = () => el.style.transform = '';
                el.addEventListener('mouseup', reset);
                el.addEventListener('mouseleave', reset);
            });

            // Ações do botão de Atualizações
            const updatesBtn = document.getElementById('openUpdatesModalBtn');
            const userMenuEl = document.getElementById('userMenu');
            if (updatesBtn) {
                updatesBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Fecha o dropdown com animação, busca lista e abre o modal
                    const openWithList = () => {
                        fetchUpdatesList();
                    };
                    if (userMenuEl && !userMenuEl.classList.contains('hidden')) {
                        animateUserMenu(userMenuEl, false);
                        setTimeout(openWithList, 220);
                    } else {
                        openWithList();
                    }
                });
            }

            // Fechar modal de Atualizações (overlay, botão fechar e tecla ESC)
            const updatesCloseBtn = document.getElementById('updatesModalCloseBtn');
            const updatesOverlay = document.getElementById('updatesModalOverlay');

            function handleCloseUpdateModal() {
                // Se houver atualização sendo exibida automaticamente, marca como vista
                if (currentUpdateId) {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
                    fetch(`/api/atualizacoes/${currentUpdateId}/mark-viewed`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf || ''
                        },
                        body: JSON.stringify({})
                    }).catch(err => console.warn('Falha ao marcar atualização como vista:', err));
                    currentUpdateId = null;
                }
                closeUpdatesModal();
            }
            if (updatesOverlay) updatesOverlay.addEventListener('click', handleCloseUpdateModal);
            if (updatesCloseBtn) updatesCloseBtn.addEventListener('click', handleCloseUpdateModal);
            window.addEventListener('keydown', (e) => {
                const isEscape = e.key === 'Escape' || e.key === 'Esc' || e.keyCode === 27;
                if (isEscape) {
                    // Evita que outros handlers cancelem ou capturem o ESC antes
                    e.preventDefault();
                    e.stopPropagation();
                    handleCloseUpdateModal();
                }
            });

            // Buscar a atualização mais recente não vista e renderizar no modal
            async function fetchLatestUnseenUpdate() {
                try {
                    const resp = await fetch('/api/atualizacoes/latest-unseen', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await resp.json();
                    if (data && data.update) {
                        renderUpdate(data.update);
                        currentUpdateId = data.update.id;
                        openUpdatesModal();
                    }
                } catch (err) {
                    console.warn('Erro ao buscar atualização não vista:', err);
                }
            }

            function renderUpdate(update) {
                const titleEl = document.getElementById('updatesModalTitle');
                const bodyEl = document.getElementById('updatesModalBody');
                const imageEl = document.getElementById('updatesModalImage');
                const tsEl = document.getElementById('updatesModalTimestamp');

                if (titleEl) titleEl.textContent = update.title || 'Atualizações do Sistema';
                if (bodyEl) {
                    bodyEl.innerHTML = '';
                    const p = document.createElement('p');
                    p.className = 'text-gray-700 whitespace-pre-line break-words';
                    p.textContent = update.body || '';
                    bodyEl.appendChild(p);
                }
                if (imageEl) {
                    if (update.image_path) {
                        imageEl.src = `/${update.image_path}`;
                        imageEl.classList.remove('hidden');
                    } else {
                        imageEl.src = '';
                        imageEl.classList.add('hidden');
                    }
                }
                if (tsEl && update.created_at) {
                    const dt = new Date(update.created_at);
                    tsEl.textContent = `Criado em: ${dt.toLocaleString('pt-BR')}`;
                }
            }

            async function fetchUpdatesList() {
                try {
                    const resp = await fetch('/api/atualizacoes', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const data = await resp.json();
                    if (data && Array.isArray(data.updates)) {
                        renderUpdateList(data.updates);
                        currentUpdateId = null; // Lista não marca visualização automática
                        openUpdatesModal();
                    }
                } catch (err) {
                    console.warn('Erro ao buscar lista de atualizações:', err);
                }
            }

            function renderUpdateList(updates) {
                const titleEl = document.getElementById('updatesModalTitle');
                const bodyEl = document.getElementById('updatesModalBody');
                const imageEl = document.getElementById('updatesModalImage');
                const tsEl = document.getElementById('updatesModalTimestamp');

                if (titleEl) titleEl.textContent = 'Atualizações do Sistema';
                if (imageEl) {
                    imageEl.src = '';
                    imageEl.classList.add('hidden');
                }
                if (tsEl) tsEl.textContent = '';

                if (bodyEl) {
                    bodyEl.innerHTML = '';
                    if (!updates.length) {
                        const p = document.createElement('p');
                        p.className = 'text-gray-600';
                        p.textContent = 'Sem novidades por enquanto.';
                        bodyEl.appendChild(p);
                        return;
                    }
                    updates.forEach((u, idx) => {
                        const item = document.createElement('div');
                        item.className = 'flex items-start';
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-check-circle mt-0.5 mr-2 text-green-500';
                        const textWrap = document.createElement('div');
                        const title = document.createElement('div');
                        title.className = 'font-medium text-gray-800 break-words';
                        title.textContent = u.title || 'Atualização';
                        const ts = document.createElement('div');
                        ts.className = 'text-xs text-gray-500';
                        if (u.created_at) {
                            const dt = new Date(u.created_at);
                            ts.textContent = `Criado em: ${dt.toLocaleString('pt-BR')}`;
                        }
                        textWrap.appendChild(title);
                        textWrap.appendChild(ts);
                        item.appendChild(icon);
                        item.appendChild(textWrap);
                        bodyEl.appendChild(item);

                        // Imagem entre título e texto com 80% da largura do modal
                        if (u.image_path) {
                            const img = document.createElement('img');
                            img.className = 'w-[80%] mx-auto mt-3 rounded border';
                            img.alt = 'Imagem da atualização';
                            img.src = `/${u.image_path}`;
                            bodyEl.appendChild(img);
                        }

                        const bodyText = document.createElement('div');
                        bodyText.className = 'text-gray-700 mt-3 whitespace-pre-line break-words';
                        bodyText.textContent = u.body || '';
                        bodyEl.appendChild(bodyText);

                        // Linha divisória entre atualizações
                        if (idx < updates.length - 1) {
                            const sep = document.createElement('div');
                            sep.className = 'border-t border-gray-200 my-4';
                            bodyEl.appendChild(sep);
                        }
                    });
                }
            }

            // Auto-open somente no Dashboard
            const path = window.location.pathname.replace(/\/+$/, '');
            if (path === '/dashboard') {
                fetchLatestUnseenUpdate();
            }
        });

        // AJAX Navigation for sidebar links
        document.addEventListener('DOMContentLoaded', function() {
            const contentContainer = document.getElementById('page-content-container');
            const sidebarLinks = document.querySelectorAll('.sidebar-link');

            function updateActiveMenuItem(url) {
                const currentPath = new URL(url).pathname;

                // Reset styling for all sidebar links
                sidebarLinks.forEach(link => {
                    const isMobile = !!link.closest('#mobileMenu');
                    if (isMobile) {
                        link.classList.remove('bg-indigo-600', 'text-white');
                        link.classList.add('text-indigo-600', 'hover:bg-indigo-100');
                    } else {
                        link.classList.remove('bg-indigo-800', 'text-white');
                        link.classList.add('text-indigo-100', 'hover:bg-indigo-600');
                    }
                });

                // Reset styling for submenu toggle buttons
                const submenuButtons = document.querySelectorAll(
                    'button[onclick*="toggleSubmenu"], button[onclick*="toggleMobileSubmenu"]');
                submenuButtons.forEach(button => {
                    const isMobile = !!button.closest('#mobileMenu');
                    if (isMobile) {
                        button.classList.remove('bg-indigo-600', 'text-white');
                        button.classList.add('text-indigo-600', 'hover:bg-indigo-100');
                    } else {
                        button.classList.remove('bg-indigo-800', 'text-white');
                        button.classList.add('text-indigo-100', 'hover:bg-indigo-600');
                    }
                });

                // Collapse all submenus and reset arrows
                const allSubmenus = document.querySelectorAll('[id$="-submenu"]');
                const allArrows = document.querySelectorAll('[id$="-arrow"]');
                allSubmenus.forEach(submenu => submenu.classList.add('hidden'));
                allArrows.forEach(arrow => arrow.classList.remove('rotate-180'));

                // Determine the most specific matching link (exact match or longest prefix)
                const candidates = Array.from(sidebarLinks)
                    .map(link => {
                        const linkPath = new URL(link.href).pathname;
                        const isExact = linkPath === currentPath;
                        const isPrefix = !isExact && linkPath !== '/' && currentPath.startsWith(linkPath);
                        const score = isExact ? (100000 + linkPath.length) : (isPrefix ? linkPath.length : -1);
                        return {
                            link,
                            linkPath,
                            score
                        };
                    })
                    .filter(c => c.score >= 0)
                    .sort((a, b) => b.score - a.score);

                const currentLink = candidates.length ? candidates[0].link : null;

                if (currentLink) {
                    const isMobile = !!currentLink.closest('#mobileMenu');
                    if (isMobile) {
                        currentLink.classList.remove('text-indigo-600', 'hover:bg-indigo-100');
                        currentLink.classList.add('bg-indigo-600', 'text-white');
                    } else {
                        currentLink.classList.remove('text-indigo-100', 'hover:bg-indigo-600');
                        currentLink.classList.add('bg-indigo-800', 'text-white');
                    }

                    // Expand the submenu containing the active link, if any
                    const parentSubmenu = currentLink.closest('[id$="-submenu"]');
                    if (parentSubmenu) {
                        parentSubmenu.classList.remove('hidden');
                        const submenuId = parentSubmenu.id.replace('-submenu', '').replace('mobile-', '');
                        const arrow = document.getElementById((isMobile ? 'mobile-' : '') + submenuId + '-arrow');
                        const button = document.querySelector(`button[onclick*="${submenuId}"]`);

                        if (arrow) arrow.classList.add('rotate-180');
                        if (button) {
                            if (isMobile) {
                                button.classList.remove('text-indigo-600', 'hover:bg-indigo-100');
                                button.classList.add('bg-indigo-600', 'text-white');
                            } else {
                                button.classList.remove('text-indigo-100', 'hover:bg-indigo-600');
                                button.classList.add('bg-indigo-800', 'text-white');
                            }
                        }
                    }
                }

                // Ensure Biblioteca submenu stays expanded for all /biblioteca/* routes
                if (currentPath.startsWith('/biblioteca')) {
                    ['', 'mobile-'].forEach(prefix => {
                        const bibliotecaSubmenu = document.getElementById(prefix + 'biblioteca-submenu');
                        const bibliotecaArrow = document.getElementById(prefix + 'biblioteca-arrow');
                        const bibliotecaButton = document.querySelector(
                            `button[onclick*="${prefix}biblioteca"], button[onclick*="toggleSubmenu('biblioteca')"]`
                            );

                        if (bibliotecaSubmenu) bibliotecaSubmenu.classList.remove('hidden');
                        if (bibliotecaArrow) bibliotecaArrow.classList.add('rotate-180');
                        if (bibliotecaButton) {
                            const isMobile = prefix === 'mobile-';
                            if (isMobile) {
                                bibliotecaButton.classList.remove('text-indigo-600', 'hover:bg-indigo-100');
                                bibliotecaButton.classList.add('bg-indigo-600', 'text-white');
                            } else {
                                bibliotecaButton.classList.remove('text-indigo-100', 'hover:bg-indigo-600');
                                bibliotecaButton.classList.add('bg-indigo-800', 'text-white');
                            }
                        }
                    });
                }

                // Handle special cases for submenu parent routes
                if (currentPath.includes('/funcionarios') || currentPath.includes('/escalas')) {
                    ['', 'mobile-'].forEach(prefix => {
                        const funcionariosSubmenu = document.getElementById(prefix +
                        'funcionarios-submenu');
                        const funcionariosArrow = document.getElementById(prefix + 'funcionarios-arrow');
                        const funcionariosButton = document.querySelector(
                            `button[onclick*="${prefix}funcionarios"], button[onclick*="toggleSubmenu('funcionarios')"]`
                            );

                        if (funcionariosSubmenu) funcionariosSubmenu.classList.remove('hidden');
                        if (funcionariosArrow) funcionariosArrow.classList.add('rotate-180');
                        if (funcionariosButton) {
                            const isMobile = prefix === 'mobile-';
                            if (isMobile) {
                                funcionariosButton.classList.remove('text-indigo-600',
                                    'hover:bg-indigo-100');
                                funcionariosButton.classList.add('bg-indigo-600', 'text-white');
                            } else {
                                funcionariosButton.classList.remove('text-indigo-100',
                                    'hover:bg-indigo-600');
                                funcionariosButton.classList.add('bg-indigo-800', 'text-white');
                            }
                        }
                    });
                }

                if (currentPath.includes('/conversas') || currentPath.includes('/comunicados')) {
                    ['', 'mobile-'].forEach(prefix => {
                        const comunicacaoSubmenu = document.getElementById(prefix + 'comunicacao-submenu');
                        const comunicacaoArrow = document.getElementById(prefix + 'comunicacao-arrow');
                        const comunicacaoButton = document.querySelector(
                            `button[onclick*="${prefix}comunicacao"], button[onclick*="toggleSubmenu('comunicacao')"]`
                            );

                        if (comunicacaoSubmenu) comunicacaoSubmenu.classList.remove('hidden');
                        if (comunicacaoArrow) comunicacaoArrow.classList.add('rotate-180');
                        if (comunicacaoButton) {
                            const isMobile = prefix === 'mobile-';
                            if (isMobile) {
                                comunicacaoButton.classList.remove('text-indigo-600',
                                'hover:bg-indigo-100');
                                comunicacaoButton.classList.add('bg-indigo-600', 'text-white');
                            } else {
                                comunicacaoButton.classList.remove('text-indigo-100',
                                'hover:bg-indigo-600');
                                comunicacaoButton.classList.add('bg-indigo-800', 'text-white');
                            }
                        }
                    });
                }
            }

            function loadContent(url, pushState = true) {
                // Simplifica navegação: faz reload completo para evitar duplicação de scripts
                window.location.href = url;
            }

            // Mantém navegação padrão dos links (sem AJAX)
            // Caso deseje voltar a navegação AJAX, remova este bloco e reabilite o anterior

            // Handle browser's back/forward buttons
            window.addEventListener('popstate', function(event) {
                if (event.state && event.state.path) {
                    loadContent(event.state.path, false); // Don't push state again
                } else {
                    // If no state, update menu for current URL
                    updateActiveMenuItem(window.location.href);
                }
            });

            // Initialize active menu item on page load
            updateActiveMenuItem(window.location.href);
        });

        // Interceptar requisições AJAX para detectar sessões expiradas
        document.addEventListener('DOMContentLoaded', function() {
            // Interceptar fetch requests
            const originalFetch = window.fetch;
            window.fetch = function(...args) {
                return originalFetch.apply(this, args)
                    .then(response => {
                        if (response.status === 401) {
                            // Sessão expirada, redirecionar para login
                            window.location.href = '{{ route('login') }}';
                            return Promise.reject(new Error('Session expired'));
                        }
                        return response;
                    })
                    .catch(error => {
                        throw error;
                    });
            };

            // Interceptar XMLHttpRequest
            const originalXHR = window.XMLHttpRequest;
            window.XMLHttpRequest = function() {
                const xhr = new originalXHR();
                const originalSend = xhr.send;

                xhr.send = function(...args) {
                    xhr.addEventListener('readystatechange', function() {
                        if (xhr.readyState === 4 && xhr.status === 401) {
                            // Sessão expirada, redirecionar para login
                            window.location.href = '{{ route('login') }}';
                        }
                    });
                    return originalSend.apply(xhr, args);
                };

                return xhr;
            };
        });
    </script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap JS (for modal functionality) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Input Masks Script -->
    <script src="{{ asset('js/input-masks.js') }}"></script>


    @stack('scripts')
</body>

</html>
