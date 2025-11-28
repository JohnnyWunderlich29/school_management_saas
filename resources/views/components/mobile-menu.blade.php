<!-- Mobile sidebar toggle -->
<div class="md:hidden fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-gray-200 flex justify-around p-3">
    <a href="{{ route('dashboard') }}" class="text-center font-medium px-3 py-1 rounded {{ request()->routeIs('dashboard') ? 'bg-indigo-100 text-indigo-700' : 'text-indigo-600' }}">
        <i class="fas fa-home text-lg"></i>
        <span class="block text-xs mt-1">Dashboard</span>
    </a>
    <a href="{{ route('alunos.index') }}" class="text-center font-medium px-3 py-1 rounded {{ request()->routeIs('alunos.*') ? 'bg-indigo-100 text-indigo-700' : 'text-indigo-600' }}">
        <i class="fas fa-user-graduate text-lg"></i>
        <span class="block text-xs mt-1">Alunos</span>
    </a>
    <a href="{{ route('presencas.index') }}" class="text-center font-medium px-3 py-1 rounded {{ request()->routeIs('presencas.*') ? 'bg-indigo-100 text-indigo-700' : 'text-indigo-600' }}">
        <i class="fas fa-clipboard-check text-lg"></i>
        <span class="block text-xs mt-1">Presenças</span>
    </a>
    <button id="mobileMenuBtn" class="text-center text-indigo-600 font-medium px-3 py-1 rounded">
        <i class="fas fa-bars text-lg"></i>
        <span class="block text-xs mt-1">Menu</span>
    </button>
</div>

<!-- Mobile menu -->
<div id="mobileMenu" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-[100000] hidden transition-opacity duration-300 ease-in-out">
    <div class="bg-white h-full w-64 shadow-xl transform transition-transform duration-300 ease-in-out -translate-x-full flex flex-col" id="mobileMenuContent">
        <div class="flex justify-between items-center p-5 border-b border-gray-200 flex-shrink-0">
            <h2 class="text-xl font-bold">{{ Auth::user()->escola->nome ?? 'Menu' }}</h2>
            <button id="closeMenu" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto p-5">
            @permission('dashboard.ver')
            <a href="{{ route('dashboard') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                <i class="fas fa-home mr-3"></i>
                <span>Dashboard</span>
            </a>
            @endpermission
            {{-- Pessoas --}}
            @if((canAccessModule('alunos_module') && (auth()->user()->temPermissao('alunos.ver') || auth()->user()->temPermissao('responsaveis.ver'))) || canAccessModule('funcionarios_module'))
            <div class="mt-4 pt-4 border-t border-gray-200">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-4">Pessoas</h3>
                @if(canAccessModule('alunos_module') && auth()->user()->temPermissao('alunos.ver'))
                <a href="{{ route('alunos.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('alunos.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                    <i class="fas fa-user-graduate mr-3"></i>
                    <span>Alunos</span>
                </a>
                @endif

                @if(canAccessModule('alunos_module') && auth()->user()->temPermissao('responsaveis.ver'))
                <a href="{{ route('responsaveis.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('responsaveis.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                    <i class="fas fa-users mr-3"></i>
                    <span>Responsáveis</span>
                </a>
                @endif
                <!-- Funcionários com submenu -->
                @if(canAccessModule('funcionarios_module'))
                <div class="relative">
                    <button onclick="toggleMobileSubmenu('funcionarios')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('funcionarios.*') || request()->routeIs('escalas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <div class="flex items-center">
                            <i class="fas fa-user-tie mr-3"></i>
                            <span>Funcionários</span>
                        </div>
                        <i class="fas fa-chevron-down transition-transform duration-200" id="mobile-funcionarios-arrow"></i>
                    </button>
                    <div id="mobile-funcionarios-submenu" class="ml-6 mt-1 space-y-1 overflow-hidden {{ request()->routeIs('funcionarios.*') || request()->routeIs('escalas.*') ? '' : 'hidden' }}">
                        @permission('funcionarios.ver')
                        <a href="{{ route('funcionarios.index') }}" class="block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('funcionarios.*') && !request()->routeIs('escalas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-list mr-3 text-sm"></i>
                            <span>Lista de Equipe</span>
                        </a>
                        @endpermission
                        @permission('escalas.ver')
                        <a href="{{ route('escalas.index') }}" class="block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('escalas.*') && !request()->routeIs('templates.calendario-escalas') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-calendar-alt mr-3 text-sm"></i>
                            <span>Escalas</span>
                        </a>
                        <a href="{{ route('templates.calendario-escalas') }}" class="block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('templates.calendario-escalas') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-calendar-week mr-3 text-sm"></i>
                            <span>Calendário de Escalas</span>
                        </a>
                        @endpermission
                    </div>
                </div>
                @endif
            </div>
            @endif
            
            
            
            {{-- Calendário removido (duplicado) --}}
            
            <!-- Comunicação removida (duplicada) -->
            
            
            
            <!-- Seção Acadêmica -->
            @if(canAccessModule('academico_module'))
                @anypermission('salas.listar', 'usuarios.editar', 'turmas.listar', 'grade_aulas.visualizar', 'planejamentos.visualizar')
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-4">Acadêmico</h3>
                        @permission('turmas.listar')
                            <a href="{{ route('turmas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('turmas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-graduation-cap mr-3"></i>
                                <span>Turmas</span>
                            </a>
                        @endpermission
                        @permission('salas.listar')
                            <a href="{{ route('salas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('salas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-door-open mr-3"></i>
                                <span>Salas</span>
                            </a>
                        @endpermission

                        @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
                        @if($isAdminSupport || auth()->user()->temPermissao('grade_aulas.visualizar'))
                            <a href="{{ route('grade-aulas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('grade-aulas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-calendar-alt mr-3"></i>
                                <span>Grade de Aulas</span>
                            </a>
                        @endif
                        @if(auth()->user()->temPermissao('presencas.ver'))
                            <a href="{{ route('presencas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('presencas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-clipboard-check mr-3"></i>
                                <span>Presenças</span>
                            </a>
                        @endif
                        @if(canAccessModule('academico_module') && auth()->user()->temPermissao('planejamentos.visualizar'))
                            <a href="{{ route('planejamentos.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('planejamentos.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-calendar-check mr-3"></i>
                                <span>Planejamentos</span>
                            </a>
                        @endif
                        @permission('usuarios.editar')
                            <a href="{{ route('disciplinas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('disciplinas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-book mr-3"></i>
                                <span>Disciplinas</span>
                            </a>
                        @endpermission
                    </div>
                @endanypermission
            @endif
            
            <!-- Seção de Módulos -->
            @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
            @if(moduleEnabled('comunicacao_module', Auth::user()->escola) || canAccessModule('biblioteca_module') || $isAdminSupport || auth()->user()->temPermissao('modulos.gerenciar'))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-4">Aplicativos</h3>
                    @if(moduleEnabled('comunicacao_module', Auth::user()->escola))
                    <div class="relative">
                        <button onclick="toggleMobileSubmenu('comunicacao')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('conversas.*') || request()->routeIs('comunicados.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <div class="flex items-center">
                                <i class="fas fa-comments mr-3"></i>
                                <span>Comunicação</span>
                            </div>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="mobile-comunicacao-arrow"></i>
                        </button>
                        <div id="mobile-comunicacao-submenu" class="ml-6 mt-1 space-y-1 overflow-hidden {{ request()->routeIs('conversas.*') || request()->routeIs('comunicados.*') ? '' : 'hidden' }}">
                            <a href="{{ route('conversas.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('conversas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-comment-dots mr-3 text-sm"></i>
                                <span>Conversas</span>
                            </a>
                            <a href="{{ route('comunicados.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('comunicados.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-bullhorn mr-3 text-sm"></i>
                                <span>Comunicados</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    @if(canAccessModule('biblioteca_module'))
                    <div class="relative mt-4">
                        <button onclick="toggleMobileSubmenu('biblioteca')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ (request()->routeIs('biblioteca.*') || request()->routeIs('biblioteca.emprestimos.*') || request()->routeIs('biblioteca.reservas.*') || request()->routeIs('biblioteca.relatorios.*')) ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <div class="flex items-center">
                                <i class="fas fa-book mr-3"></i>
                                <span>Biblioteca</span>
                            </div>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="mobile-biblioteca-arrow"></i>
                        </button>
                        <div id="mobile-biblioteca-submenu" class="ml-6 mt-1 space-y-1 overflow-hidden {{ (request()->routeIs('biblioteca.*') || request()->routeIs('biblioteca.emprestimos.*') || request()->routeIs('biblioteca.reservas.*') || request()->routeIs('biblioteca.relatorios.*')) ? '' : 'hidden' }}">
                            @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.ver'))
                            <a href="{{ route('biblioteca.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.index') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-books mr-3 text-sm"></i>
                                <span>Catálogo</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.emprestimos.ver'))
                            <a href="{{ route('biblioteca.emprestimos.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.emprestimos.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-external-link-alt mr-3 text-sm"></i>
                                <span>Empréstimos</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.reservas.ver'))
                            <a href="{{ route('biblioteca.reservas.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.reservas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-bookmark mr-3 text-sm"></i>
                                <span>Reservas</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.relatorios.ver'))
                            <a href="{{ route('biblioteca.relatorios.index') }}" class="sidebar-link block py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.relatorios.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                                <i class="fas fa-chart-line mr-3 text-sm"></i>
                                <span>Relatórios</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <a href="{{ route('notifications.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('notifications.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <i class="fas fa-bell mr-3"></i>
                        <span>Notificações</span>
                    </a>
                    @if($isAdminSupport || auth()->user()->temPermissao('modulos.gerenciar'))
                    <a href="{{ route('modules.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('modules.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <i class="fas fa-th-large mr-3"></i>
                        <span>Módulos</span>
                    </a>
                    @endif
                </div>
            @endif
            
            {{-- Secretaria --}}
            @if(canAccessModule('alunos_module') || canAccessModule('eventos_module'))
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-4">Secretaria</h3>
                    @if(canAccessModule('eventos_module') && (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') || auth()->user()->temPermissao('eventos.ver')))
                    <a href="{{ route('calendario.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('calendario.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        <span>Calendário</span>
                    </a>
                    @endif
                    @if(canAccessModule('alunos_module'))
                        @permission('salas.editar')
                        <a href="{{ route('transferencias.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('transferencias.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-exchange-alt mr-3"></i>
                            <span>Transferências</span>
                        </a>
                        @endpermission
                    @endif
                    @permission('escalas.listar')
                        <a href="{{ route('historico.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('historico.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-history mr-3"></i>
                            <span>Histórico</span>
                        </a>
                    @endpermission
                    <a href="{{ route('profile.escola') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('profile.escola') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <i class="fas fa-school mr-3"></i>
                        <span>Dados da Escola</span>
                    </a>
                    {{-- Removido: Modalidades de Ensino e Grupos Educacionais (apenas mobile) --}}
                </div>
            @endif

            <!-- Biblioteca -->
            
            {{-- Administração --}}
            @anypermission('usuarios.listar', 'cargos.listar')
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2 px-4">Administração</h3>
                    @permission('usuarios.listar')
                        <a href="{{ route('usuarios.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('usuarios.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-user-cog mr-3"></i>
                            <span>Usuários</span>
                        </a>
                    @endpermission
                    @permission('cargos.listar')
                        <a href="{{ route('cargos.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('cargos.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-user-shield mr-3"></i>
                            <span>Cargos</span>
                        </a>
                    @endpermission
                    @permission('usuarios.editar')
                        <a href="{{ route('settings.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-cogs mr-3"></i>
                            <span>Configurações</span>
                        </a>
                    @endpermission
                    @if(canAccessModule('financeiro_module'))
                    @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
                    @if($isAdminSupport || auth()->user()->temPermissao('despesas.ver'))
                        <a href="{{ route('admin.despesas.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('admin.despesas.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span>Despesas</span>
                        </a>
                    @endif
                    @endif
                    <a href="{{ route('reports.index') }}" class="sidebar-link block py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white' : 'text-indigo-600 hover:bg-indigo-100' }}">
                        <i class="fas fa-chart-bar mr-3"></i>
                        <span>Relatórios</span>
                    </a>
                </div>
            @endanypermission
        </nav>
    </div>
</div>

<script>
function animateMobileSubmenu(submenu, expand) {
    if (!submenu) return;
    submenu.style.transition = 'max-height 300ms ease, opacity 300ms ease';
    submenu.style.willChange = 'max-height, opacity';
    if (expand) {
        submenu.classList.remove('hidden');
        submenu.style.opacity = '0';
        submenu.style.maxHeight = '0px';
        requestAnimationFrame(() => {
            submenu.style.opacity = '1';
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
        });
    } else {
        submenu.style.opacity = '1';
        submenu.style.maxHeight = submenu.scrollHeight + 'px';
        requestAnimationFrame(() => {
            submenu.style.opacity = '0';
            submenu.style.maxHeight = '0px';
        });
        setTimeout(() => {
            submenu.classList.add('hidden');
        }, 300);
    }
}

function toggleMobileSubmenu(menuId) {
    const submenu = document.getElementById('mobile-' + menuId + '-submenu');
    const arrow = document.getElementById('mobile-' + menuId + '-arrow');
    if (!submenu) return;
    const isHidden = submenu.classList.contains('hidden');
    animateMobileSubmenu(submenu, isHidden);
    if (arrow) arrow.classList.toggle('rotate-180', isHidden);
}

// Inicialização: aplica animação de clique e estado inicial dos submenus
document.addEventListener('DOMContentLoaded', function() {
    // Clique com animação sutil
    document.querySelectorAll('#mobileMenu .sidebar-link, #mobileMenu button[onclick^="toggleMobileSubmenu"]').forEach(el => {
        el.style.transition = (el.style.transition ? el.style.transition + ', ' : '') + 'transform 150ms ease';
        el.addEventListener('mousedown', () => el.style.transform = 'scale(0.98)');
        const reset = () => el.style.transform = '';
        el.addEventListener('mouseup', reset);
        el.addEventListener('mouseleave', reset);
    });

    const initMobileSubmenu = (id) => {
        const submenu = document.getElementById('mobile-' + id + '-submenu');
        const arrow = document.getElementById('mobile-' + id + '-arrow');
        if (!submenu) return;
        submenu.style.overflow = 'hidden';
        submenu.style.transition = 'max-height 300ms ease, opacity 300ms ease';
        if (submenu.classList.contains('hidden')) {
            submenu.style.maxHeight = '0px';
            submenu.style.opacity = '0';
        } else {
            submenu.style.maxHeight = submenu.scrollHeight + 'px';
            submenu.style.opacity = '1';
            if (arrow) arrow.classList.add('rotate-180');
        }
    };

    ['funcionarios','comunicacao','biblioteca'].forEach(initMobileSubmenu);
});
</script>