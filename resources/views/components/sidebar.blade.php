<!-- Sidebar -->
<aside class="bg-indigo-700 text-white w-64 flex-shrink-0 hidden md:block shadow-lg">
    <div class="p-6">

    </div>
    <nav class="mt-6">
        <div class="px-4 py-2">
            @permission('dashboard.ver')
            <a href="{{ route('dashboard') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                <i class="fas fa-home mr-3"></i>
                <span>Dashboard</span>
            </a>
            @endpermission

            {{-- Pessoas --}}
            @if((canAccessModule('alunos_module') && (auth()->user()->temPermissao('alunos.ver') || auth()->user()->temPermissao('responsaveis.ver'))) || canAccessModule('funcionarios_module'))
                <div class="px-4 py-2 mt-4">
                    <h3 class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Pessoas</h3>
                    @if(canAccessModule('alunos_module') && auth()->user()->temPermissao('alunos.ver'))
                    <a href="{{ route('alunos.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('alunos.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                        <i class="fas fa-user-graduate mr-3"></i>
                        <span>Alunos</span>
                    </a>
                    @endif

                    @if(canAccessModule('alunos_module') && auth()->user()->temPermissao('responsaveis.ver'))
                    <a href="{{ route('responsaveis.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('responsaveis.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                        <i class="fas fa-users mr-3"></i>
                        <span>Responsáveis</span>
                    </a>
                    @endif

                    @if(canAccessModule('funcionarios_module'))
                    <div class="relative mt-2">
                        <button onclick="toggleSubmenu('funcionarios')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('funcionarios.*') || request()->routeIs('escalas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <div class="flex items-center">
                                <i class="fas fa-user-tie mr-3"></i>
                                <span>Funcionários</span>
                            </div>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="funcionarios-arrow"></i>
                        </button>
                        <div id="funcionarios-submenu" class="px-4 mt-1 ml-6 space-y-1 overflow-hidden {{ request()->routeIs('funcionarios.*') || request()->routeIs('escalas.*') ? '' : 'hidden' }}">
                            @permission('funcionarios.ver')
                            <a href="{{ route('funcionarios.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('funcionarios.*') && !request()->routeIs('escalas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-list mr-3 text-sm"></i>
                                <span>Lista de Equipe</span>
                            </a>
                            @endpermission
                            @permission('escalas.ver')
                            <a href="{{ route('escalas.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('escalas.*') && !request()->routeIs('templates.calendario-escalas') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-calendar-alt mr-3 text-sm"></i>
                                <span>Escalas</span>
                            </a>
                            <a href="{{ route('templates.calendario-escalas') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('templates.calendario-escalas') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-calendar-week mr-3 text-sm"></i>
                                <span>Calendário de Escalas</span>
                            </a>
                            @endpermission
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            {{-- Secretaria --}}
            @if(canAccessModule('alunos_module') || canAccessModule('eventos_module'))
                <div class="px-4 py-2 mt-4">
                    <h3 class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Secretaria</h3>
                    {{-- Calendário Escolar / Gestão de Eventos --}}
                    @if(canAccessModule('eventos_module') && (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte') || auth()->user()->temPermissao('eventos.ver')))
                    <a href="{{ route('calendario.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('calendario.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                        <i class="fas fa-calendar-alt mr-3"></i>
                        <span>Calendário Escolar</span>
                    </a>
                    @endif
                    {{-- Transferências --}}
                    @if(canAccessModule('alunos_module'))
                        @permission('salas.editar')
                        <a href="{{ route('transferencias.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('transferencias.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <i class="fas fa-exchange-alt mr-3"></i>
                            <span>Transferências</span>
                        </a>
                        @endpermission
                    @endif
                </div>
            @endif
            
            @if(canAccessModule('administracao_module'))
                @anypermission('usuarios.listar', 'cargos.listar')
                    <div class="px-4 py-2 mt-4">
                        <h3 class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Administração</h3>
                        @permission('usuarios.listar')
                            <a href="{{ route('usuarios.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('usuarios.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-user-cog mr-3"></i>
                                <span>Usuários</span>
                            </a>
                        @endpermission
                        @permission('cargos.listar')
                            <a href="{{ route('cargos.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('cargos.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-user-shield mr-3"></i>
                                <span>Cargos</span>
                            </a>
                        @endpermission
                        @permission('usuarios.editar')
                            <a href="{{ route('settings.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('settings.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-cogs mr-3"></i>
                                <span>Configurações</span>
                            </a>
                        @endpermission
                        @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
                        @if(canAccessModule('financeiro_module'))
                        @if($isAdminSupport || auth()->user()->temPermissao('despesas.ver') || auth()->user()->temPermissao('recebimentos.ver') || auth()->user()->temPermissao('recorrencias.ver'))
                            <div class="relative mt-2">
                                <button onclick="toggleSubmenu('financeiro')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ (request()->routeIs('finance.*') || request()->routeIs('admin.despesas.*')) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-coins mr-3"></i>
                                        <span>Financeiro</span>
                                    </div>
                                    <i class="fas fa-chevron-down transition-transform duration-200" id="financeiro-arrow"></i>
                                </button>
                                <div id="financeiro-submenu" class="px-4 mt-1 ml-6 space-y-1 overflow-hidden {{ (request()->routeIs('finance.*') || request()->routeIs('admin.despesas.*')) ? '' : 'hidden' }}">
                                    @if($isAdminSupport || auth()->user()->temPermissao('despesas.ver'))
                                        <a href="{{ route('admin.despesas.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('admin.despesas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                            <i class="fas fa-money-bill-wave mr-3 text-sm"></i>
                                            <span>Despesas</span>
                                        </a>
                                    @endif
                                    @if($isAdminSupport || auth()->user()->temPermissao('recebimentos.ver'))
                                        <a href="{{ route('admin.recebimentos.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('admin.recebimentos.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                            <i class="fas fa-hand-holding-usd mr-3 text-sm"></i>
                                            <span>Receitas</span>
                                        </a>
                                    @endif
                                    @if($isAdminSupport || auth()->user()->temPermissao('recorrencias.ver'))
                                        <a href="{{ route('admin.recorrencias.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('admin.recorrencias.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                            <i class="fas fa-redo mr-3 text-sm"></i>
                                            <span>Recorrências</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @endif
                        @if(canAccessModule('relatorios_module'))
                        <a href="{{ route('reports.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('reports.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <i class="fas fa-chart-bar mr-3"></i>
                            <span>Relatórios</span>
                        </a>
                        @endif
                    </div>
                @endanypermission
            @endif
            
            <!-- Seção Acadêmica -->
            @if(canAccessModule('academico_module'))
                @anypermission('salas.listar', 'usuarios.editar', 'turmas.ver', 'grade_aulas.visualizar', 'planejamentos.visualizar')
                    <div class="px-2 py-2 mt-4">
                        <h3 class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Acadêmico</h3>
                        @permission('turmas.ver')
                            <a href="{{ route('turmas.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('turmas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-graduation-cap mr-3"></i>
                                <span>Turmas</span>
                            </a>
                        @endpermission

                        @permission('salas.listar')
                            <a href="{{ route('salas.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('salas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-door-open mr-3"></i>
                                <span>Salas</span>
                            </a>
                        @endpermission

                        @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
                        @if($isAdminSupport || auth()->user()->temPermissao('grade_aulas.visualizar'))
                            <a href="{{ route('grade-aulas.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('grade-aulas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-calendar-alt mr-3"></i>
                                <span>Grade de Aulas</span>
                            </a>
                        @endif
                        @if(canAccessModule('academico_module') && auth()->user()->temPermissao('planejamentos.visualizar'))
                            <a href="{{ route('planejamentos.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('planejamentos.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-calendar-check mr-3"></i>
                                <span>Planejamentos</span>
                            </a>
                        @endif
                        @if(auth()->user()->temPermissao('presencas.ver'))
                            <a href="{{ route('presencas.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('presencas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                                <i class="fas fa-clipboard-check mr-3"></i>
                                <span>Presenças</span>
                            </a>
                        @endif
                        <!-- Referência a Salas dos Professores removida conforme solicitado -->
                    </div>
                @endanypermission
            @endif
            
            <!-- Seção de Aplicativos -->
            @php $isAdminSupport = auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte'); @endphp
            @if(moduleEnabled('comunicacao_module', Auth::user()->escola) || canAccessModule('biblioteca_module') || $isAdminSupport || auth()->user()->temPermissao('modulos.gerenciar'))
                <div class="px-4 py-2 mt-4">
                    <h3 class="text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Aplicativos</h3>
                    <!-- Comunicação -->
                    @if(moduleEnabled('comunicacao_module', Auth::user()->escola))
                    <div class="relative">
                        <button onclick="toggleSubmenu('comunicacao')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('conversas.*') || request()->routeIs('comunicados.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <div class="flex items-center">
                                <i class="fas fa-comments mr-3"></i>
                                <span>Comunicação</span>
                            </div>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="comunicacao-arrow"></i>
                        </button>
                        <div id="comunicacao-submenu" class="px-4 mt-1 ml-6 space-y-1 overflow-hidden {{ request()->routeIs('conversas.*') || request()->routeIs('comunicados.*') ? '' : 'hidden' }}">
                            <a href="{{ route('conversas.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('conversas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-comment-dots mr-3 text-sm"></i>
                                <span>Conversas</span>
                            </a>
                            <a href="{{ route('comunicados.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('comunicados.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-bullhorn mr-3 text-sm"></i>
                                <span>Comunicados</span>
                            </a>
                        </div>
                    </div>
                    @endif

                    <!-- Biblioteca -->
                    @if(canAccessModule('biblioteca_module'))
                    <div class="relative mt-2">
                        <button onclick="toggleSubmenu('biblioteca')" class="w-full flex items-center justify-between py-2.5 px-4 rounded transition duration-200 {{ (request()->routeIs('biblioteca.*') || request()->routeIs('biblioteca.emprestimos.*') || request()->routeIs('biblioteca.reservas.*') || request()->routeIs('biblioteca.relatorios.*')) ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                            <div class="flex items-center">
                                <i class="fas fa-book mr-3"></i>
                                <span>Biblioteca</span>
                            </div>
                            <i class="fas fa-chevron-down transition-transform duration-200" id="biblioteca-arrow"></i>
                        </button>
                        <div id="biblioteca-submenu" class="px-4 mt-1 ml-6 space-y-1 overflow-hidden {{ (request()->routeIs('biblioteca.*') || request()->routeIs('biblioteca.emprestimos.*') || request()->routeIs('biblioteca.reservas.*') || request()->routeIs('biblioteca.relatorios.*')) ? '' : 'hidden' }}">
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.ver'))
                            <a href="{{ route('biblioteca.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.index') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-books mr-3 text-sm"></i>
                                <span>Catálogo</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.emprestimos.ver'))
                            <a href="{{ route('biblioteca.emprestimos.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.emprestimos.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-external-link-alt mr-3 text-sm"></i>
                                <span>Empréstimos</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.reservas.ver'))
                            <a href="{{ route('biblioteca.reservas.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.reservas.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-bookmark mr-3 text-sm"></i>
                                <span>Reservas</span>
                            </a>
                            @endif
                            @if($isAdminSupport || auth()->user()->temPermissao('biblioteca.relatorios.ver'))
                            <a href="{{ route('biblioteca.relatorios.index') }}" class="sidebar-link flex items-center py-2 px-4 rounded transition duration-200 {{ request()->routeIs('biblioteca.relatorios.*') ? 'bg-indigo-800 text-white' : 'text-indigo-200 hover:bg-indigo-600' }}">
                                <i class="fas fa-chart-line mr-3 text-sm"></i>
                                <span>Relatórios</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Módulos -->
                    @if($isAdminSupport || auth()->user()->temPermissao('modulos.gerenciar'))
                    <a href="{{ route('modules.index') }}" class="sidebar-link flex items-center py-2.5 px-4 rounded transition duration-200 {{ request()->routeIs('modules.*') ? 'bg-indigo-800 text-white' : 'text-indigo-100 hover:bg-indigo-600' }}">
                        <i class="fas fa-th-large mr-3"></i>
                        <span>Módulos</span>
                    </a>
                    @endif
                </div>
            @endif
        </div>
    </nav>
</aside>

<script>
// Clique com animação sutil e expand/collapse suave de submenus
function animateSubmenu(submenu, expand) {
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

function toggleSubmenu(menuId) {
    const submenu = document.getElementById(menuId + '-submenu');
    const arrow = document.getElementById(menuId + '-arrow');
    if (!submenu || !arrow) return;
    const isHidden = submenu.classList.contains('hidden');
    animateSubmenu(submenu, isHidden);
    arrow.classList.toggle('rotate-180', isHidden);
}

// Inicialização: aplica clique animado e corrige estado inicial dos submenus
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona animação de clique a links e botões do sidebar
    document.querySelectorAll('.sidebar-link, button[onclick^="toggleSubmenu"]').forEach(el => {
        el.style.transition = (el.style.transition ? el.style.transition + ', ' : '') + 'transform 150ms ease';
        el.addEventListener('mousedown', () => el.style.transform = 'scale(0.98)');
        const reset = () => el.style.transform = '';
        el.addEventListener('mouseup', reset);
        el.addEventListener('mouseleave', reset);
    });

    const initSubmenu = (id) => {
        const submenu = document.getElementById(id + '-submenu');
        const arrow = document.getElementById(id + '-arrow');
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

    ['funcionarios','comunicacao','financeiro','biblioteca'].forEach(initSubmenu);
});
</script>