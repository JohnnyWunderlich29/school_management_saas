<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\AlunoController;
use App\Http\Controllers\ResponsavelController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\FuncionarioTemplateController;
use App\Http\Controllers\EscalaController;
use App\Http\Controllers\PresencaController;
use App\Http\Controllers\HistoricoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SchoolRegisterController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BaseHtmlController;
use App\Http\Controllers\PlanejamentoController;
use App\Http\Controllers\ModalidadeEnsinoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\TempoSlotController;
use App\Http\Controllers\DisciplinaController;
use App\Http\Controllers\DespesaController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\Finance\FinanceAdminController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\BibliotecaController;
use App\Http\Controllers\DigitalAccessController;
use App\Http\Controllers\EmprestimoController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RelatoriosBibliotecaController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\Marketing\InstitutionalController;
use App\Http\Controllers\Marketing\LeadController;

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Cadastro de Escola/Empresa
Route::middleware('guest')->group(function () {
    Route::get('/register/escola', [SchoolRegisterController::class, 'showForm'])->name('register.escola');
    Route::post('/register/escola', [SchoolRegisterController::class, 'register'])->name('register.escola.submit');
});

Route::middleware('guest')->group(function () {
    Route::get('/institucional', [InstitutionalController::class, 'index'])->name('institucional.index');
    Route::get('/institucional/funcionalidades', [InstitutionalController::class, 'funcionalidades'])->name('institucional.funcionalidades');
    Route::get('/institucional/contato', [InstitutionalController::class, 'contato'])->name('institucional.contato');
    Route::post('/institucional/contato', [LeadController::class, 'store'])
        ->name('institucional.leads.store')
        ->middleware('throttle:10,1');
});



// Rotas protegidas por autenticação
Route::middleware(['auth', 'escola.context'])->group(function () {
    // Dashboard - requer permissão específica
    Route::middleware(['permission:dashboard.ver'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // Preferências do Dashboard (persistência por usuário)
        Route::prefix('dashboard')->group(function () {
            Route::get('/preferences', [\App\Http\Controllers\DashboardPreferenceController::class, 'index'])
                ->name('dashboard.preferences.index');
            Route::post('/preferences', [\App\Http\Controllers\DashboardPreferenceController::class, 'save'])
                ->name('dashboard.preferences.save');
            Route::delete('/preferences', [\App\Http\Controllers\DashboardPreferenceController::class, 'destroy'])
                ->name('dashboard.preferences.destroy');
        });
    });

    // Rotas do sistema de módulos
    Route::middleware(['permission:modulos.gerenciar'])->group(function () {
        Route::get('/modules', [\App\Http\Controllers\ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/{module}/contract', [\App\Http\Controllers\ModuleController::class, 'contract'])->name('modules.contract');
        Route::delete('/modules/{module}/cancel', [\App\Http\Controllers\ModuleController::class, 'cancel'])->name('modules.cancel');
        Route::patch('/modules/{module}/toggle', [\App\Http\Controllers\ModuleController::class, 'toggle'])->name('modules.toggle');
    });

    // Alunos - requer licença do módulo e permissões específicas
    Route::middleware(['module.license:alunos_module'])->group(function () {
        Route::middleware(['permission:alunos.ver'])->group(function () {
            Route::get('/alunos', [AlunoController::class, 'index'])->name('alunos.index');
        });

        Route::middleware(['permission:alunos.criar'])->group(function () {
            Route::get('/alunos/create', [AlunoController::class, 'create'])->name('alunos.create');
            Route::post('/alunos', [AlunoController::class, 'store'])->name('alunos.store');
            Route::get('/alunos/search-responsaveis', [AlunoController::class, 'searchResponsaveis'])->name('alunos.search-responsaveis');
            Route::post('/alunos/create-responsavel', [AlunoController::class, 'createResponsavel'])->name('alunos.create-responsavel');
        });

        Route::middleware(['permission:alunos.ver'])->group(function () {
            Route::get('/alunos/{aluno}', [AlunoController::class, 'show'])->name('alunos.show');
        });

        Route::middleware(['permission:alunos.editar'])->group(function () {
            Route::get('/alunos/{aluno}/edit', [AlunoController::class, 'edit'])->name('alunos.edit');
            Route::put('/alunos/{aluno}', [AlunoController::class, 'update'])->name('alunos.update');
        });

        Route::middleware(['permission:alunos.excluir'])->group(function () {
            Route::patch('/alunos/{aluno}/toggle-status', [AlunoController::class, 'toggleStatus'])->name('alunos.toggleStatus');
        });

        Route::middleware(['permission:alunos.editar'])->group(function () {
            Route::post('/alunos/{aluno}/transferir', [AlunoController::class, 'transferir'])->name('alunos.transferir');
        });
    });

    // Responsáveis - requer licença do módulo de alunos e permissões específicas
    Route::middleware(['module.license:alunos_module'])->group(function () {
        Route::middleware(['permission:responsaveis.listar'])->group(function () {
            Route::get('/responsaveis', [ResponsavelController::class, 'index'])->name('responsaveis.index');
        });

        Route::middleware(['permission:responsaveis.criar'])->group(function () {
            Route::get('/responsaveis/create', [ResponsavelController::class, 'create'])->name('responsaveis.create');
            Route::post('/responsaveis', [ResponsavelController::class, 'store'])->name('responsaveis.store');
        });

        Route::middleware(['permission:responsaveis.listar'])->group(function () {
            Route::get('/responsaveis/{responsavel}', [ResponsavelController::class, 'show'])->name('responsaveis.show');
        });

        Route::middleware(['permission:responsaveis.editar'])->group(function () {
            Route::get('/responsaveis/{responsavel}/edit', [ResponsavelController::class, 'edit'])->name('responsaveis.edit');
            Route::put('/responsaveis/{responsavel}', [ResponsavelController::class, 'update'])->name('responsaveis.update');
        });

        Route::middleware(['permission:responsaveis.excluir'])->group(function () {
            Route::patch('/responsaveis/{responsavel}/toggle-status', [ResponsavelController::class, 'toggleStatus'])->name('responsaveis.toggle-status');
        });
    });

    // Funcionários - requer licença do módulo e permissões específicas
    Route::middleware(['module.license:funcionarios_module'])->group(function () {
        // 🔹 Criar funcionário (precisa vir ANTES do {funcionario})
        Route::middleware(['permission:funcionarios.criar'])->group(function () {
            Route::get('/funcionarios/create', [FuncionarioController::class, 'create'])->name('funcionarios.create');
            Route::post('/funcionarios', [FuncionarioController::class, 'store'])->name('funcionarios.store');
        });

        // 🔹 Ver funcionários
        Route::middleware(['permission:funcionarios.ver'])->group(function () {
            Route::get('/funcionarios', [FuncionarioController::class, 'index'])->name('funcionarios.index');
            Route::get('/funcionarios/{funcionario}', [FuncionarioController::class, 'show'])
                ->whereNumber('funcionario')->name('funcionarios.show');
        });

        // 🔹 Editar funcionários
        Route::middleware(['permission:funcionarios.editar'])->group(function () {
            Route::get('/funcionarios/{funcionario}/edit', [FuncionarioController::class, 'edit'])
                ->whereNumber('funcionario')->name('funcionarios.edit');
            Route::put('/funcionarios/{funcionario}', [FuncionarioController::class, 'update'])
                ->whereNumber('funcionario')->name('funcionarios.update');
        });

        // 🔹 Excluir funcionários
        Route::middleware(['permission:funcionarios.excluir'])->group(function () {
            Route::delete('/funcionarios/{funcionario}', [FuncionarioController::class, 'destroy'])
                ->whereNumber('funcionario')->name('funcionarios.destroy');
            Route::patch('/funcionarios/{funcionario}/toggle-status', [FuncionarioController::class, 'toggleStatus'])
                ->whereNumber('funcionario')->name('funcionarios.toggle-status');
        });
    });

    // Grupos - requer permissões específicas
    Route::middleware(['permission:grupos.ver'])->group(function () {
        Route::resource('grupos', GrupoController::class);
    });

    // Turnos - requer permissões específicas
    Route::middleware(['permission:turnos.ver'])->group(function () {
        Route::resource('turnos', TurnoController::class);
    });

    // Disciplinas - requer permissões específicas
    Route::middleware(['permission:disciplinas.ver'])->group(function () {
        Route::resource('disciplinas', DisciplinaController::class);
    });

    // Rotas de planejamento que precisam apenas de autenticação
    Route::middleware(['auth', 'escola.context'])->group(function () {
        Route::get('/planejamentos/turnos-disponiveis', [PlanejamentoController::class, 'getTurnosDisponiveis'])->name('planejamentos.turnos-disponiveis');
        Route::get('/planejamentos/grupos-educacionais', [PlanejamentoController::class, 'getGruposEducacionais'])->name('planejamentos.grupos-educacionais');
        Route::get('/planejamentos/disciplinas-por-modalidade-turno-grupo', [PlanejamentoController::class, 'getDisciplinasPorModalidadeTurnoGrupo'])->name('planejamentos.disciplinas-por-modalidade-turno-grupo');
        Route::get('/planejamentos/turmas-por-disciplina', [PlanejamentoController::class, 'getTurmasPorDisciplina'])->name('planejamentos.turmas-por-disciplina');
        Route::get('/planejamentos/get-disciplinas-por-turma', [PlanejamentoController::class, 'getDisciplinasPorTurma'])->name('planejamentos.get-disciplinas-por-turma');
        Route::get('/planejamentos/get-professores-por-turma-disciplina', [PlanejamentoController::class, 'getProfessoresPorTurmaDisciplina'])->name('planejamentos.get-professores-por-turma-disciplina');
    });

    // Planejamentos - requer permissões específicas
    Route::middleware(['permission:planejamentos.visualizar'])->group(function () {
        Route::get('/planejamentos/tipos-professor-by-modalidade', [PlanejamentoController::class, 'getTiposProfessorByModalidade'])->name('planejamentos.tipos-professor-by-modalidade');
    });

    // Onboarding / Primeiros passos (acessível a qualquer autenticado)
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding/toggle/{slug}', [OnboardingController::class, 'toggle'])->name('onboarding.toggle');

    // API de Atualizações do Sistema (para modal automático)
    Route::prefix('api/atualizacoes')->name('updates.')->group(function () {
        Route::get('/', [SystemUpdateController::class, 'listRecent'])->name('list');
        Route::get('/latest-unseen', [SystemUpdateController::class, 'latestUnseen'])->name('latest');
        Route::post('/{update}/mark-viewed', [SystemUpdateController::class, 'markViewed'])
            ->whereNumber('update')->name('markViewed');
    });
    Route::post('/onboarding/minimize', [OnboardingController::class, 'minimize'])->name('onboarding.minimize');
    Route::post('/onboarding/close', [OnboardingController::class, 'close'])->name('onboarding.close');
    Route::post('/onboarding/reopen', [OnboardingController::class, 'reopen'])->name('onboarding.reopen');

    // Boas-vindas: marcar como visto (uma vez)
    Route::post('/welcome/dismiss', [\App\Http\Controllers\WelcomeController::class, 'dismiss'])->name('welcome.dismiss');


    // Calendário Escolar - protegido por licença do módulo de eventos e permissões granulares
    Route::middleware(['module.license:eventos_module'])->group(function () {
        // Visualização
        Route::middleware(['permission:eventos.ver'])->group(function () {
            Route::get('/calendario', [CalendarController::class, 'index'])->name('calendario.index');
            Route::get('/calendario/events', [CalendarController::class, 'events'])->name('calendario.events');
        });

        // Criação
        Route::middleware(['permission:eventos.criar'])->group(function () {
            Route::post('/calendario/events', [CalendarController::class, 'store'])->name('calendario.events.store');
        });

        // Edição
        Route::middleware(['permission:eventos.editar'])->group(function () {
            Route::put('/calendario/events/{event}', [CalendarController::class, 'update'])
                ->whereNumber('event')
                ->name('calendario.events.update');
        });

        // Exclusão
        Route::middleware(['permission:eventos.excluir'])->group(function () {
            Route::delete('/calendario/events/{event}', [CalendarController::class, 'destroy'])
                ->whereNumber('event')
                ->name('calendario.events.destroy');
        });
    });


    // Biblioteca Digital - protegido por licença do módulo, contexto e middleware customizado
    // Adiciona escola.scope para garantir escola_atual para Super Admin/Suporte
    Route::middleware(['module.license:biblioteca_module', 'biblioteca.access', 'escola.scope'])->group(function () {
        // Visualização do catálogo
        Route::middleware(['permission:biblioteca.ver'])->group(function () {
            Route::get('/biblioteca', [BibliotecaController::class, 'index'])->name('biblioteca.index');
            Route::get('/biblioteca/index', [BibliotecaController::class, 'index']);
        });

        // Cadastro de itens
        Route::middleware(['permission:biblioteca.criar'])->group(function () {
            Route::post('/biblioteca/item', [BibliotecaController::class, 'store'])->name('biblioteca.store');
        });

        // Upload e preview de arquivos digitais
        Route::middleware(['permission:biblioteca.editar'])->group(function () {
            Route::post('/biblioteca/item/{itemId}/upload', [DigitalAccessController::class, 'upload'])->name('biblioteca.upload');
            Route::delete('/biblioteca/digital/{digitalId}', [DigitalAccessController::class, 'destroy'])->name('biblioteca.digital.destroy');
            Route::patch('/biblioteca/item/{item}', [BibliotecaController::class, 'update'])->name('biblioteca.update');
        });
        Route::middleware(['permission:biblioteca.ver'])->group(function () {
            Route::get('/biblioteca/digital/{digitalId}/preview', [DigitalAccessController::class, 'preview'])->name('biblioteca.preview');
            // Preview inline específico para capa (imagem)
            Route::get('/biblioteca/digital/{digitalId}/cover', [DigitalAccessController::class, 'cover'])->name('biblioteca.cover');
            // Buscar uploads de um item específico (para atualizar coluna via AJAX)
            Route::get('/biblioteca/item/{itemId}/uploads', [BibliotecaController::class, 'getItemUploads'])->name('biblioteca.item.uploads');
        });

        // Rotas de Empréstimos
        Route::middleware(['permission:biblioteca.emprestar'])->group(function () {
            Route::get('/biblioteca/emprestimos', [EmprestimoController::class, 'index'])->name('biblioteca.emprestimos.index');
            // Formulário de novo empréstimo (usado pelo processamento de reservas)
            Route::get('/biblioteca/emprestimos/create', [EmprestimoController::class, 'create'])->name('biblioteca.emprestimos.create');
            Route::post('/biblioteca/emprestimos', [EmprestimoController::class, 'store'])->name('biblioteca.emprestimos.store');
            Route::get('/biblioteca/emprestimos/{emprestimo}', [EmprestimoController::class, 'show'])->name('biblioteca.emprestimos.show');
            Route::patch('/biblioteca/emprestimos/{emprestimo}/devolver', [EmprestimoController::class, 'devolver'])->name('biblioteca.emprestimos.devolver');
            Route::patch('/biblioteca/emprestimos/{emprestimo}/renovar', [EmprestimoController::class, 'renovar'])->name('biblioteca.emprestimos.renovar');

            // Políticas da Biblioteca (carregar/salvar)
            Route::get('/biblioteca/politicas', [\App\Http\Controllers\BibliotecaPoliticaController::class, 'show'])->name('biblioteca.politicas.show');
            Route::patch('/biblioteca/politicas', [\App\Http\Controllers\BibliotecaPoliticaController::class, 'update'])->name('biblioteca.politicas.update');
        });

        // Rotas de Reservas
        Route::middleware(['permission:biblioteca.reservar'])->group(function () {
            Route::get('/biblioteca/reservas', [ReservaController::class, 'index'])->name('biblioteca.reservas.index');
            Route::post('/biblioteca/reservas', [ReservaController::class, 'store'])->name('biblioteca.reservas.store');
            Route::get('/biblioteca/reservas/{reserva}', [ReservaController::class, 'show'])->name('biblioteca.reservas.show');
            Route::patch('/biblioteca/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->name('biblioteca.reservas.cancelar');
            Route::patch('/biblioteca/reservas/{reserva}/processar', [ReservaController::class, 'processar'])->name('biblioteca.reservas.processar');
        });

        // Rotas de Relatórios da Biblioteca
        Route::middleware(['permission:biblioteca.relatorios.ver'])->group(function () {
            Route::get('/biblioteca/relatorios', [RelatoriosBibliotecaController::class, 'index'])->name('biblioteca.relatorios.index');
            Route::post('/biblioteca/relatorios/gerar', [RelatoriosBibliotecaController::class, 'gerar'])->name('biblioteca.relatorios.gerar');
        });
        Route::middleware(['permission:biblioteca.relatorios.exportar'])->group(function () {
            Route::post('/biblioteca/relatorios/exportar', [RelatoriosBibliotecaController::class, 'exportar'])->name('biblioteca.relatorios.exportar');
        });
    });



    // Templates de Funcionários - requer permissões específicas
Route::middleware(['module.license:funcionarios_module'])->group(function () {

    // 🔹 Rotas de edição (create vem ANTES do {template})
    Route::middleware(['permission:funcionarios.editar'])->group(function () {
        Route::get('/funcionarios/{funcionario}/templates/create', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'create']
        )->whereNumber('funcionario')->name('funcionarios.templates.create');

        Route::post('/funcionarios/{funcionario}/templates', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'store']
        )->whereNumber('funcionario')->name('funcionarios.templates.store');

        Route::get('/funcionarios/{funcionario}/templates/{template}/edit', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'edit']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.edit');

        Route::put('/funcionarios/{funcionario}/templates/{template}', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'update']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.update');

        Route::patch('/funcionarios/{funcionario}/templates/{template}/toggle-ativo', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'toggleAtivo']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.toggle-ativo');
    });

    // 🔹 Rotas de visualização
    Route::middleware(['permission:funcionarios.ver'])->group(function () {
        Route::get('/funcionarios/{funcionario}/templates', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'index']
        )->whereNumber('funcionario')->name('funcionarios.templates.index');

        Route::get('/funcionarios/{funcionario}/templates/{template}', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'show']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.show');
    });

    // 🔹 Rotas de cópia de templates
    Route::middleware(['permission:funcionarios.criar'])->group(function () {
        Route::post('/funcionarios/{funcionario}/templates/{template}/copiar', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'copiar']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.copiar');
    });

    // 🔹 Rotas de exclusão
    Route::middleware(['permission:funcionarios.excluir'])->group(function () {
        Route::delete('/funcionarios/{funcionario}/templates/{template}', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'destroy']
        )->whereNumber('funcionario')->whereNumber('template')->name('funcionarios.templates.destroy');
    });

    // 🔹 Rotas para geração de escalas automáticas
    Route::middleware(['permission:escalas.criar'])->group(function () {
        Route::get('/funcionarios/{funcionario}/templates/gerar-escalas', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'formGerarEscalas']
        )->whereNumber('funcionario')->name('templates.gerar-escalas.form');

        Route::post('/funcionarios/{funcionario}/templates/gerar-escalas', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'gerarEscalas']
        )->whereNumber('funcionario')->name('templates.gerar-escalas');
    });

    // 🔹 Rota para visualização em calendário das escalas
    Route::middleware(['permission:escalas.ver'])->group(function () {
        Route::get('/templates/calendario-escalas', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'calendarioEscalas']
        )->name('templates.calendario-escalas');
        
        // Rota para escalas de funcionário específico
        Route::get('/funcionarios/{funcionario}/escalas', 
            [\App\Http\Controllers\FuncionarioTemplateController::class, 'calendarioEscalas']
        )->whereNumber('funcionario')->name('funcionarios.escalas.index');
    });
});


    // Escalas - requer permissões específicas
    Route::middleware(['permission:escalas.ver'])->group(function () {
        Route::get('/escalas', [EscalaController::class, 'index'])->name('escalas.index');
    });

    Route::middleware(['permission:escalas.criar'])->group(function () {
        Route::get('/escalas/create', [EscalaController::class, 'create'])->name('escalas.create');
        Route::post('/escalas', [EscalaController::class, 'store'])->name('escalas.store');
    });

    Route::middleware(['permission:escalas.ver'])->group(function () {
        Route::get('/escalas/{escala}', [EscalaController::class, 'show'])->name('escalas.show');
    });

    Route::middleware(['permission:escalas.editar'])->group(function () {
        Route::get('/escalas/{escala}/edit', [EscalaController::class, 'edit'])->name('escalas.edit');
        Route::put('/escalas/{escala}', [EscalaController::class, 'update'])->name('escalas.update');
    });

    Route::middleware(['permission:escalas.excluir'])->group(function () {
        Route::delete('/escalas/{escala}', [EscalaController::class, 'destroy'])->name('escalas.destroy');
    });

    // Histórico - requer permissões específicas e isolamento por escola
    Route::middleware(['escola.scope', 'permission:historico.visualizar'])->group(function () {
        Route::get('/historico', [HistoricoController::class, 'index'])->name('historico.index');
        Route::get('/historico/{historico}', [HistoricoController::class, 'show'])->name('historico.show');
        Route::get('/historico/modelo/{modelo}/{id}', [HistoricoController::class, 'porModelo'])->name('historico.modelo');
    });

    // Presenças - requer licença do módulo acadêmico, escopo da escola e permissões específicas
    Route::middleware(['module.license:academico_module', 'escola.scope'])->group(function () {
        Route::middleware(['permission:presencas.ver'])->group(function () {
            Route::get('/presencas', [PresencaController::class, 'index'])->name('presencas.index');
            Route::get('/presencas/visualizar', [PresencaController::class, 'show'])->name('presencas.show');
        });

        Route::middleware(['permission:presencas.criar'])->group(function () {
            Route::get('/presencas/create', [PresencaController::class, 'create'])->name('presencas.create');
            Route::post('/presencas', [PresencaController::class, 'store'])->name('presencas.store');
            Route::post('/presencas/individual', [PresencaController::class, 'storeIndividual'])->name('presencas.store.individual');
            Route::post('/presencas/registrar', [PresencaController::class, 'registrarPresenca'])->name('presencas.registrar');
            Route::get('/presencas/registro-rapido', [PresencaController::class, 'registroRapido'])->name('presencas.registro-rapido');
            Route::post('/presencas/registro-rapido', [PresencaController::class, 'registroRapidoStore'])->name('presencas.registro-rapido.store');
            Route::post('/presencas/saida-mais-cedo', [PresencaController::class, 'registrarSaidaMaisCedo'])->name('presencas.saida-mais-cedo');
            Route::get('/presencas/lancar', [PresencaController::class, 'lancar'])->name('presencas.lancar');
            Route::post('/presencas/lancar', [PresencaController::class, 'lancarStore'])->name('presencas.lancar.store');
        });

        Route::middleware(['permission:presencas.editar'])->group(function () {
            Route::get('/presencas/{presenca}/edit', [PresencaController::class, 'edit'])->name('presencas.edit');
            Route::put('/presencas/{presenca}', [PresencaController::class, 'update'])->name('presencas.update');
        });

        Route::middleware(['permission:presencas.excluir'])->group(function () {
            Route::delete('/presencas/{presenca}', [PresencaController::class, 'destroy'])->name('presencas.destroy');
        });
    });

    // Base HTML - Página de demonstração de componentes e padrões de design
    Route::get('/base_html', [BaseHtmlController::class, 'index'])->name('base-html.index');

    // Usuários - requer permissões específicas
    Route::middleware(['permission:usuarios.listar'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index'])->name('usuarios.index');
    });

    Route::middleware(['permission:usuarios.criar'])->group(function () {
        Route::get('/usuarios/create', [UserController::class, 'create'])->name('usuarios.create');
        Route::post('/usuarios', [UserController::class, 'store'])->name('usuarios.store');
    });

    // Planejamentos - requer permissões específicas
    Route::middleware(['permission:planejamentos.visualizar'])->group(function () {
        // Route::resource removido para evitar conflitos - rotas definidas manualmente abaixo
        Route::get('/planejamentos/get-turnos-por-modalidade', [PlanejamentoController::class, 'getTurnosPorModalidade'])->name('planejamentos.getTurnosPorModalidade');
        Route::get('/planejamentos/get-grupos-por-modalidade-turno', [PlanejamentoController::class, 'getGruposPorModalidadeTurno'])->name('planejamentos.getGruposPorModalidadeTurno');
        Route::get('/planejamentos/get-disciplinas-por-modalidade-turno-grupo', [PlanejamentoController::class, 'getDisciplinasPorModalidadeTurnoGrupo'])->name('planejamentos.getDisciplinasPorModalidadeTurnoGrupo');
        Route::get('/planejamentos/get-turmas-por-disciplina', [PlanejamentoController::class, 'getTurmasPorDisciplina'])->name('planejamentos.getTurmasPorDisciplina');
        Route::get('/planejamentos/get-turmas-por-grupo-turno', [PlanejamentoController::class, 'getTurmasPorGrupoTurno'])->name('planejamentos.getTurmasPorGrupoTurno');
        

        // Outras rotas para o fluxo de 7 etapas que requerem permissões
        Route::get('/planejamentos/turmas-por-grupo-turno', [PlanejamentoController::class, 'getTurmasPorGrupoTurno'])->name('planejamentos.turmas-por-grupo-turno');
        Route::get('/planejamentos/niveis-ensino', [PlanejamentoController::class, 'getNiveisEnsino'])->name('planejamentos.niveis-ensino');
        Route::get('/planejamentos/tipos-professor', [PlanejamentoController::class, 'getTiposProfessor'])->name('planejamentos.tipos-professor');
    });

    Route::middleware(['permission:usuarios.listar'])->group(function () {
        Route::get('/usuarios/{user}', [UserController::class, 'show'])->name('usuarios.show');
    });

    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/usuarios/{user}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
        Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
    });

    Route::middleware(['permission:usuarios.excluir'])->group(function () {
        Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
        Route::patch('/usuarios/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('usuarios.toggle-status');
    });

    // Cargos - requer permissões específicas
    Route::middleware(['permission:cargos.listar'])->group(function () {
        Route::get('/cargos', [CargoController::class, 'index'])->name('cargos.index');
    });

    Route::middleware(['permission:cargos.criar'])->group(function () {
        Route::get('/cargos/create', [CargoController::class, 'create'])->name('cargos.create');
        Route::post('/cargos', [CargoController::class, 'store'])->name('cargos.store');
    });

    Route::middleware(['permission:cargos.listar'])->group(function () {
        Route::get('/cargos/{cargo}', [CargoController::class, 'show'])->name('cargos.show');
    });

    Route::middleware(['permission:cargos.editar'])->group(function () {
        Route::get('/cargos/{cargo}/edit', [CargoController::class, 'edit'])->name('cargos.edit');
        Route::put('/cargos/{cargo}', [CargoController::class, 'update'])->name('cargos.update');
    });

    Route::middleware(['permission:cargos.excluir'])->group(function () {
        Route::delete('/cargos/{cargo}', [CargoController::class, 'destroy'])->name('cargos.destroy');
    });

    // Salas - requer licença do módulo de administração e permissões específicas
    Route::middleware(['module.license:administracao_module'])->group(function () {
        Route::middleware(['permission:salas.listar'])->group(function () {
            Route::get('/salas', [\App\Http\Controllers\SalaController::class, 'index'])->name('salas.index');
        });

        Route::middleware(['permission:salas.criar'])->group(function () {
            Route::get('/salas/create', [\App\Http\Controllers\SalaController::class, 'create'])->name('salas.create');
            Route::post('/salas', [\App\Http\Controllers\SalaController::class, 'store'])->name('salas.store');
        });

        Route::middleware(['permission:salas.listar'])->group(function () {
            Route::get('/salas/{sala}', [\App\Http\Controllers\SalaController::class, 'show'])->name('salas.show');
        });

        Route::middleware(['permission:salas.editar'])->group(function () {
            Route::get('/salas/{sala}/edit', [\App\Http\Controllers\SalaController::class, 'edit'])->name('salas.edit');
            Route::put('/salas/{sala}', [\App\Http\Controllers\SalaController::class, 'update'])->name('salas.update');
            Route::patch('/salas/{sala}/toggle-status', [\App\Http\Controllers\SalaController::class, 'toggleStatus'])->name('salas.toggle-status');
            Route::post('/salas/solicitar-transferencia', [\App\Http\Controllers\SalaController::class, 'solicitarTransferencia'])->name('salas.solicitar-transferencia');
            Route::post('/salas/{sala}/adicionar-aluno', [\App\Http\Controllers\SalaController::class, 'adicionarAluno'])->name('salas.adicionar-aluno');
            Route::post('/salas/{sala}/transferir-aluno', [\App\Http\Controllers\SalaController::class, 'transferirAluno'])->name('salas.transferir-aluno');
            Route::delete('/salas/{sala}/remover-aluno', [\App\Http\Controllers\SalaController::class, 'removerAluno'])->name('salas.remover-aluno');
        });

        Route::middleware(['permission:salas.excluir'])->group(function () {
            Route::delete('/salas/{sala}', [\App\Http\Controllers\SalaController::class, 'destroy'])->name('salas.destroy');
        });
    });

    // Rota de exemplo para demonstrar as diretivas de permissão
    Route::get('/permissions/example', function () {
        return view('permissions.example');
    })->name('permissions.example');

    // Rotas do perfil do usuário - requer permissões específicas
    Route::middleware(['permission:perfil.visualizar'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
        Route::get('/profile/escola', [ProfileController::class, 'escola'])->name('profile.escola');
    });

    Route::middleware(['permission:perfil.editar'])->group(function () {
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/settings', [ProfileController::class, 'updateSettings'])->name('profile.settings.update');
    });

    // Rotas de administração - Gerenciamento de Salas dos Usuários
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        // Rotas de user-salas removidas conforme solicitado
    });

    // Rotas de Transferências de Alunos - requer licença do módulo de alunos e permissões específicas
    Route::middleware(['module.license:alunos_module'])->group(function () {
        Route::middleware(['permission:transferencias.ver'])->group(function () {
            Route::get('/transferencias', [\App\Http\Controllers\TransferenciaController::class, 'index'])->name('transferencias.index');
        });

        Route::middleware(['permission:transferencias.criar'])->group(function () {
            Route::get('/transferencias/create', [\App\Http\Controllers\TransferenciaController::class, 'create'])->name('transferencias.create');
            Route::post('/transferencias', [\App\Http\Controllers\TransferenciaController::class, 'store'])->name('transferencias.store');
            Route::delete('/transferencias/{transferencia}', [\App\Http\Controllers\TransferenciaController::class, 'destroy'])->name('transferencias.destroy');
        });

        Route::middleware(['permission:transferencias.ver'])->group(function () {
            Route::get('/transferencias/{transferencia}', [\App\Http\Controllers\TransferenciaController::class, 'show'])->name('transferencias.show');
        });

        Route::middleware(['permission:transferencias.aprovar'])->group(function () {
            Route::get('/transferencias/{transferencia}/aprovar', [\App\Http\Controllers\TransferenciaController::class, 'showAprovar'])->name('transferencias.show-aprovar');
            Route::patch('/transferencias/{transferencia}/aprovar', [\App\Http\Controllers\TransferenciaController::class, 'aprovar'])->name('transferencias.aprovar');
            Route::get('/transferencias/{transferencia}/rejeitar', [\App\Http\Controllers\TransferenciaController::class, 'showRejeitar'])->name('transferencias.show-rejeitar');
            Route::patch('/transferencias/{transferencia}/rejeitar', [\App\Http\Controllers\TransferenciaController::class, 'rejeitar'])->name('transferencias.rejeitar');
        });
    });


// Rotas de Planejamentos - requer licença do módulo acadêmico e permissões específicas
    Route::middleware(['module.license:academico_module'])->group(function () {
        Route::middleware(['permission:planejamentos.visualizar'])->group(function () {
            Route::get('/planejamentos', [PlanejamentoController::class, 'index'])->name('planejamentos.index');
            
            // Movida para permissão de visualização para permitir acesso
            Route::get('/planejamentos/get-ultimo-periodo-planejamento', 
                [PlanejamentoController::class, 'getUltimoPeriodoPlanejamento'])
                ->name('planejamentos.get-ultimo-periodo-planejamento');

        });

        // Rotas específicas devem vir ANTES das rotas com parâmetros para evitar conflitos
        Route::middleware(['permission:planejamentos.criar'])->group(function () {
            Route::get('/planejamentos/create', [PlanejamentoController::class, 'create'])->name('planejamentos.create');
            Route::post('/planejamentos', [PlanejamentoController::class, 'store'])->name('planejamentos.store');
            Route::get('/planejamentos/modalidades-com-salas', [PlanejamentoController::class, 'getModalidadesComSalas'])->name('planejamentos.modalidades-com-salas');

            Route::get('/planejamentos/tipos-professor-by-modalidade', [PlanejamentoController::class, 'getTiposProfessor'])->name('planejamentos.tipos-professor-by-modalidade');
            
            // Novas rotas do wizard unificado - DEVEM VIR ANTES DAS ROTAS COM PARÂMETROS
            Route::get('/planejamentos/wizard', [PlanejamentoController::class, 'wizard'])->name('planejamentos.wizard');
            Route::post('/planejamentos/wizard/store', [PlanejamentoController::class, 'wizardStore'])->name('planejamentos.wizard.store');
            Route::post('/planejamentos/wizard/init-draft', [PlanejamentoController::class, 'wizardInitDraft'])->name('planejamentos.wizard.init-draft');
            Route::post('/planejamentos/wizard/diario/upsert', [PlanejamentoController::class, 'wizardUpsertDiario'])->name('planejamentos.wizard.diario.upsert');
            Route::get('/planejamentos/wizard/step/{step}', [PlanejamentoController::class, 'wizardStep'])->name('planejamentos.wizard.step');
            Route::post('/planejamentos/wizard/validate-step', [PlanejamentoController::class, 'validateWizardStep'])->name('planejamentos.wizard.validate-step');
            
            // APIs para o wizard
            Route::get('/api/planejamentos/unidades', [PlanejamentoController::class, 'getUnidades'])->name('api.planejamentos.unidades');
            Route::get('/api/planejamentos/turnos', [PlanejamentoController::class, 'getTurnosByUnidade'])->name('api.planejamentos.turnos');
            Route::get('/api/planejamentos/turmas', [PlanejamentoController::class, 'getTurmasByUnidadeTurno'])->name('api.planejamentos.turmas');
            Route::get('/api/planejamentos/turmas-filtered', [PlanejamentoController::class, 'getTurmasFiltered'])->name('api.planejamentos.turmas-filtered');
            Route::get('/api/planejamentos/niveis-por-modalidade/{modalidade}', [PlanejamentoController::class, 'getNiveisPorModalidade'])->name('api.planejamentos.niveis-por-modalidade');
            Route::get('/api/planejamentos/disciplinas/{turma}', [PlanejamentoController::class, 'getDisciplinasByTurma'])->name('api.planejamentos.disciplinas');
            Route::get('/api/planejamentos/professores/{turma}/{disciplina}', [PlanejamentoController::class, 'getProfessoresByTurmaDisciplina'])->name('api.planejamentos.professores');
            Route::post('/api/planejamentos/verificar-compatibilidade', [PlanejamentoController::class, 'verificarCompatibilidade'])->name('api.planejamentos.verificar-compatibilidade');
            Route::post('/api/planejamentos/verificar-conflitos', [PlanejamentoController::class, 'verificarConflitos'])->name('api.planejamentos.verificar-conflitos');
            // Alias usado pelo wizard na etapa 3
            Route::post('/api/verificar-conflitos-planejamento', [PlanejamentoController::class, 'verificarConflitos'])->name('api.verificar-conflitos-planejamento');
            Route::get('/api/planejamentos/campos-experiencia', [PlanejamentoController::class, 'getCamposExperiencia'])->name('api.planejamentos.campos-experiencia');
            Route::get('/api/planejamentos/objetivos-aprendizagem', [PlanejamentoController::class, 'getObjetivosAprendizagem'])->name('api.planejamentos.objetivos-aprendizagem');
            Route::get('/api/planejamentos/saberes-conhecimentos', [PlanejamentoController::class, 'getSaberesConhecimentos'])->name('api.planejamentos.saberes-conhecimentos');

            // Detalhes de disciplina (usado na etapa 3 do wizard)
            Route::get('/api/disciplinas/{disciplina}', [PlanejamentoController::class, 'getDisciplinaDetalhes'])->name('api.disciplinas.detalhes');
            Route::get('/api/planejamentos/sugestoes-saberes', [PlanejamentoController::class, 'getSugestoesSaberes'])->name('api.planejamentos.sugestoes-saberes');
            
            // APIs para turmas (necessárias para o wizard de planejamentos)
            Route::get('/api/turmas/{turma}', [\App\Http\Controllers\TurmaController::class, 'show'])->name('api.turmas.show');
            Route::get('/api/turmas/{turma}/alunos', [\App\Http\Controllers\TurmaController::class, 'getAlunos'])->name('api.turmas.alunos');
        });

        // Rotas de conflitos específicas - DEVEM VIR ANTES DAS ROTAS COM PARÂMETROS
        Route::middleware(['module.license:academico_module', 'permission:planejamentos.aprovar'])->group(function () {
            // Rotas originais (mantidas para compatibilidade)
            Route::get('/planejamentos/conflitos', [PlanejamentoController::class, 'conflitos'])->name('planejamentos.conflitos');
            Route::post('/planejamentos/conflitos/verificar-todos', [PlanejamentoController::class, 'verificarTodosConflitos'])->name('planejamentos.conflitos.verificar-todos');
            
            // Novas rotas do sistema aprimorado de gestão de conflitos
            Route::get('/planejamentos/conflitos/gestao', [PlanejamentoController::class, 'conflitosIndex'])->name('planejamentos.conflitos.index');
            Route::post('/planejamentos/conflitos/verificar-todos-enhanced', [PlanejamentoController::class, 'verificarTodosConflitosEnhanced'])->name('planejamentos.conflitos.verificar-todos-enhanced');
            Route::post('/planejamentos/conflitos/verificar-personalizado', [PlanejamentoController::class, 'verificarConflitosPersonalizado'])->name('planejamentos.conflitos.verificar-personalizado');
            Route::get('/planejamentos/conflitos/lista-ajax', [PlanejamentoController::class, 'listaConflitosAjax'])->name('planejamentos.conflitos.lista-ajax');
            Route::get('/planejamentos/conflitos/{id}/detalhes', [PlanejamentoController::class, 'detalhesConflito'])->name('planejamentos.conflitos.detalhes');
            Route::post('/planejamentos/conflitos/{id}/ignorar', [PlanejamentoController::class, 'ignorarConflito'])->name('planejamentos.conflitos.ignorar');
            Route::post('/planejamentos/conflitos/{id}/resolver', [PlanejamentoController::class, 'resolverConflito'])->name('planejamentos.conflitos.resolver');
            Route::get('/planejamentos/conflitos/{id}/exportar', [PlanejamentoController::class, 'exportarConflito'])->name('planejamentos.conflitos.exportar');
            Route::get('/planejamentos/conflitos/relatorio', [PlanejamentoController::class, 'relatorioConflitos'])->name('planejamentos.conflitos.relatorio');
        });

        Route::middleware(['permission:planejamentos.visualizar'])->group(function () {
            // Rotas com parâmetros devem vir DEPOIS das rotas específicas
Route::get('/planejamentos/{planejamento}', [PlanejamentoController::class, 'show'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.show');
Route::get('/planejamentos/{planejamento}/detalhado', [PlanejamentoController::class, 'showDetalhado'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.detalhado');
            
            // Nova visualização de planejamento
Route::get('/planejamentos/{planejamento}/view', [PlanejamentoController::class, 'viewPlanejamento'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.view');
Route::get('/planejamentos/{planejamento}/preview', [PlanejamentoController::class, 'previewPlanejamento'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.preview');
Route::get('/planejamentos/{planejamento}/export/{format}', [PlanejamentoController::class, 'exportPlanejamento'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.export');

            // Cronograma Diário - acesso rápido do professor e detalhe por planejamento
            Route::get('/planejamentos/cronograma-dia', [PlanejamentoController::class, 'cronogramaDia'])->name('planejamentos.cronograma-dia');
Route::get('/planejamentos/{planejamento}/cronograma', [PlanejamentoController::class, 'cronogramaDetalhe'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.cronograma');
        });

        Route::middleware(['permission:planejamentos.editar'])->group(function () {
Route::get('/planejamentos/{planejamento}/edit', [PlanejamentoController::class, 'edit'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.edit');

            Route::post('/planejamentos/{planejamento}/detalhado', [PlanejamentoController::class, 'storeDetalhado'])->name('planejamentos.detalhado.store');
            Route::put('/planejamentos/{planejamento}/detalhado', [PlanejamentoController::class, 'updateDetalhado'])->name('planejamentos.detalhado.update');
            
            // Edição inline das seções
Route::get('/planejamentos/{planejamento}/edit-section/{section}', [PlanejamentoController::class, 'editSection'])
    ->where('planejamento', '[0-9]+')
    ->name('planejamentos.edit-section');
            Route::put('/planejamentos/{planejamento}/update-section/{section}', [PlanejamentoController::class, 'updateSection'])->name('planejamentos.update-section');
            Route::put('/planejamentos/{planejamento}', [PlanejamentoController::class, 'update'])->name('planejamentos.update');
        });

        Route::middleware(['permission:planejamentos.excluir'])->group(function () {
            Route::delete('/planejamentos/{planejamento}', [PlanejamentoController::class, 'destroy'])->name('planejamentos.destroy');
        });
    });

    // Rotas de aprovação de planejamentos - requer licença do módulo acadêmico e permissões específicas
    Route::middleware(['module.license:academico_module'])->group(function () {
        Route::middleware(['permission:planejamentos.aprovar'])->group(function () {
            Route::post('/planejamentos/{planejamento}/aprovar', [PlanejamentoController::class, 'aprovar'])->name('planejamentos.aprovar');
            Route::post('/planejamentos/{planejamento}/rejeitar', [PlanejamentoController::class, 'rejeitar'])->name('planejamentos.rejeitar');
            
            // Rota de exclusão de conflito específico (mantida aqui por usar parâmetro de planejamento)
            Route::delete('/planejamentos/{planejamento}/conflitos', [PlanejamentoController::class, 'excluirConflito'])->name('planejamentos.conflitos.excluir');
        });
    });

    // Rotas de Modalidades de Ensino - Administração
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/modalidades', [ModalidadeEnsinoController::class, 'index'])->name('admin.modalidades.index');
        Route::get('/admin/modalidades/create', [ModalidadeEnsinoController::class, 'create'])->name('admin.modalidades.create');
        Route::post('/admin/modalidades', [ModalidadeEnsinoController::class, 'store'])->name('admin.modalidades.store');
        Route::get('/admin/modalidades/{modalidade}', [ModalidadeEnsinoController::class, 'show'])->name('admin.modalidades.show');
        Route::get('/admin/modalidades/{modalidade}/edit', [ModalidadeEnsinoController::class, 'edit'])->name('admin.modalidades.edit');
        Route::put('/admin/modalidades/{modalidade}', [ModalidadeEnsinoController::class, 'update'])->name('admin.modalidades.update');
        Route::delete('/admin/modalidades/{modalidade}', [ModalidadeEnsinoController::class, 'destroy'])->name('admin.modalidades.destroy');
        Route::patch('/admin/modalidades/{modalidade}/toggle-status', [ModalidadeEnsinoController::class, 'toggleStatus'])->name('admin.modalidades.toggle-status');
    });

    // Rotas de Configuração Educacional - Apenas para clientes (show)
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/configuracao-educacional/{escola}', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'show'])->name('admin.configuracao-educacional.show');
        Route::post('/admin/configuracao-educacional/{escola}/modalidade', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'storeModalidade'])->name('admin.configuracao-educacional.store-modalidade');
        Route::post('/admin/configuracao-educacional/{escola}/nivel', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'storeNivel'])->name('admin.configuracao-educacional.store-nivel');
        Route::delete('/admin/configuracao-educacional/{escola}/modalidade/{modalidadeConfig}', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'destroyModalidade'])->name('admin.configuracao-educacional.destroy-modalidade');
        Route::delete('/admin/configuracao-educacional/{escola}/nivel/{nivelConfig}', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'destroyNivel'])->name('admin.configuracao-educacional.destroy-nivel');
        
        // Rotas para Templates BNCC
        Route::get('/admin/configuracao-educacional/{escola}/templates-bncc', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'getTemplatesBncc'])->name('admin.configuracao-educacional.templates-bncc');
        Route::post('/admin/configuracao-educacional/{escola}/aplicar-templates-bncc', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'aplicarTemplatesBncc'])->name('admin.configuracao-educacional.aplicar-templates-bncc');
        
        // Rotas para Disciplinas
        Route::get('/admin/configuracao-educacional/{escola}/disciplinas', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'getDisciplinas'])->name('admin.configuracao-educacional.disciplinas');
        Route::put('/admin/configuracao-educacional/{escola}/disciplina', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'updateDisciplina'])->name('admin.configuracao-educacional.update-disciplina');
        Route::put('/admin/configuracao-educacional/{escola}/disciplina-nivel', [\App\Http\Controllers\Admin\ConfiguracaoEducacionalController::class, 'updateDisciplinaNivel'])->name('admin.configuracao-educacional.update-disciplina-nivel');
    });

    // Rotas de Grupos Educacionais - Administração
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/grupos', [GrupoController::class, 'index'])->name('admin.grupos.index');
        Route::get('/admin/grupos/create', [GrupoController::class, 'create'])->name('admin.grupos.create');
        
        // Rotas AJAX para modais (devem vir ANTES das rotas com parâmetros)
        Route::get('/admin/grupos/listar', [GrupoController::class, 'listar'])->name('admin.grupos.listar');
        Route::get('/admin/grupos/listar-por-modalidade', [GrupoController::class, 'listarPorModalidade'])->name('admin.grupos.listar-por-modalidade');
        Route::get('/admin/grupos/modalidades-ensino', [GrupoController::class, 'getModalidadesEnsino'])->name('admin.grupos.modalidades-ensino');
        
        Route::post('/admin/grupos', [GrupoController::class, 'store'])->name('admin.grupos.store');
        Route::get('/admin/grupos/{grupo}', [GrupoController::class, 'show'])->name('admin.grupos.show');
        Route::get('/admin/grupos/{grupo}/edit', [GrupoController::class, 'edit'])->name('admin.grupos.edit');
        Route::put('/admin/grupos/{grupo}', [GrupoController::class, 'update'])->name('admin.grupos.update');
        Route::delete('/admin/grupos/{grupo}', [GrupoController::class, 'destroy'])->name('admin.grupos.destroy');
        
        Route::get('/admin/grupos/{grupo}/edit-modal', [GrupoController::class, 'editModal'])->name('admin.grupos.edit-modal');
        Route::get('/admin/grupos/{grupo}/show-modal', [GrupoController::class, 'showModal'])->name('admin.grupos.show-modal');
    });

    // Rotas de Turnos - Administração
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/turnos', [TurnoController::class, 'index'])->name('admin.turnos.index');
        Route::get('/admin/turnos/listar', [TurnoController::class, 'listar'])->name('admin.turnos.listar');
        Route::get('/admin/turnos/create', [TurnoController::class, 'create'])->name('admin.turnos.create');
        Route::post('/admin/turnos', [TurnoController::class, 'store'])->name('admin.turnos.store');
        Route::get('/admin/turnos/{turno}', [TurnoController::class, 'show'])->name('admin.turnos.show');
        Route::get('/admin/turnos/{turno}/edit', [TurnoController::class, 'edit'])->name('admin.turnos.edit');
        Route::put('/admin/turnos/{turno}', [TurnoController::class, 'update'])->name('admin.turnos.update');
        Route::delete('/admin/turnos/{turno}', [TurnoController::class, 'destroy'])->name('admin.turnos.destroy');
        Route::patch('/admin/turnos/{turno}/toggle-status', [TurnoController::class, 'toggleStatus'])->name('admin.turnos.toggle-status');
        
        // Rotas de Tempo Slots - Administração
        Route::get('/admin/turnos/{turno}/tempo-slots', [TempoSlotController::class, 'index'])->name('admin.turnos.tempo-slots.index');
        Route::get('/admin/turnos/{turno}/tempo-slots/create', [TempoSlotController::class, 'create'])->name('admin.turnos.tempo-slots.create');
        Route::post('/admin/turnos/{turno}/tempo-slots', [TempoSlotController::class, 'store'])->name('admin.turnos.tempo-slots.store');
        Route::get('/admin/turnos/{turno}/tempo-slots/{tempoSlot}', [TempoSlotController::class, 'show'])->name('admin.turnos.tempo-slots.show');
        Route::get('/admin/turnos/{turno}/tempo-slots/{tempoSlot}/edit', [TempoSlotController::class, 'edit'])->name('admin.turnos.tempo-slots.edit');
        Route::put('/admin/turnos/{turno}/tempo-slots/{tempoSlot}', [TempoSlotController::class, 'update'])->name('admin.turnos.tempo-slots.update');
        Route::delete('/admin/turnos/{turno}/tempo-slots/{tempoSlot}', [TempoSlotController::class, 'destroy'])->name('admin.turnos.tempo-slots.destroy');
        
        // Rotas AJAX para modais
        Route::get('/admin/turnos/{turno}/tempo-slots/{tempoSlot}/modal-show', [TempoSlotController::class, 'showModal'])->name('admin.turnos.tempo-slots.modal-show');
        Route::get('/admin/turnos/{turno}/tempo-slots/{tempoSlot}/modal-edit', [TempoSlotController::class, 'editModal'])->name('admin.turnos.tempo-slots.modal-edit');
    });

    // Rotas de Disciplinas - Administração
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/disciplinas', [DisciplinaController::class, 'index'])->name('admin.disciplinas.index');
        Route::get('/admin/disciplinas/create', [DisciplinaController::class, 'create'])->name('admin.disciplinas.create');
        Route::post('/admin/disciplinas', [DisciplinaController::class, 'store'])->name('admin.disciplinas.store');
        Route::get('/admin/disciplinas/{disciplina}', [DisciplinaController::class, 'show'])->name('admin.disciplinas.show');
        Route::get('/admin/disciplinas/{disciplina}/edit', [DisciplinaController::class, 'edit'])->name('admin.disciplinas.edit');
        Route::put('/admin/disciplinas/{disciplina}', [DisciplinaController::class, 'update'])->name('admin.disciplinas.update');
        Route::delete('/admin/disciplinas/{disciplina}', [DisciplinaController::class, 'destroy'])->name('admin.disciplinas.destroy');
    });

    // Rotas de Despesas - Administração com permissões específicas
    // Visualizar lista de despesas
    Route::middleware(['module.license:financeiro_module', 'permission:despesas.ver'])->group(function () {
        Route::get('/admin/despesas', [DespesaController::class, 'index'])->name('admin.despesas.index');
    });

    // Rotas de Recebimentos - Administração (visualização)
    Route::middleware(['module.license:financeiro_module', 'permission:recebimentos.ver'])->group(function () {
        Route::get('/admin/recebimentos', [\App\Http\Controllers\Admin\RecebimentosController::class, 'index'])->name('admin.recebimentos.index');
        Route::get('/admin/recebimentos/{invoice}/details', [\App\Http\Controllers\Admin\RecebimentosController::class, 'details'])->name('admin.recebimentos.details');
    });

    // Rotas de Recorrências - Administração
    // Visualização
    Route::middleware(['module.license:financeiro_module', 'permission:recorrencias.ver'])->group(function () {
        Route::get('/admin/recorrencias', [\App\Http\Controllers\Admin\RecorrenciasController::class, 'index'])->name('admin.recorrencias.index');
    });
    // Ações de recorrências (granularizadas)
    // Editar método e controlar estado (pausar/retomar)
    Route::middleware(['module.license:financeiro_module', 'permission:recorrencias.editar'])->group(function () {
        Route::put('/admin/recorrencias/{subscription}/method', [\App\Http\Controllers\Admin\RecorrenciasController::class, 'updateMethod'])
            ->whereNumber('subscription')->name('admin.recorrencias.update-method');
        Route::put('/admin/recorrencias/{subscription}/pause', [\App\Http\Controllers\Admin\RecorrenciasController::class, 'pause'])
            ->whereNumber('subscription')->name('admin.recorrencias.pause');
        Route::put('/admin/recorrencias/{subscription}/resume', [\App\Http\Controllers\Admin\RecorrenciasController::class, 'resume'])
            ->whereNumber('subscription')->name('admin.recorrencias.resume');
    });
    // Cancelar recorrência (ação destrutiva)
    Route::middleware(['module.license:financeiro_module', 'permission:recorrencias.cancelar'])->group(function () {
        Route::put('/admin/recorrencias/{subscription}/cancel', [\App\Http\Controllers\Admin\RecorrenciasController::class, 'cancel'])
            ->whereNumber('subscription')->name('admin.recorrencias.cancel');
    });

    // Criar despesas
    Route::middleware(['module.license:financeiro_module', 'permission:despesas.criar'])->group(function () {
        Route::post('/admin/despesas', [DespesaController::class, 'store'])->name('admin.despesas.store');
    });

    // Editar despesas (inclui modal de edição e atualização)
    Route::middleware(['module.license:financeiro_module', 'permission:despesas.editar'])->group(function () {
        Route::get('/admin/despesas/{despesa}/modal-edit', [DespesaController::class, 'editModal'])->name('admin.despesas.modal-edit');
        Route::put('/admin/despesas/{despesa}', [DespesaController::class, 'update'])->name('admin.despesas.update');
    });

    // Cancelar despesas
    Route::middleware(['module.license:financeiro_module', 'permission:despesas.cancelar'])->group(function () {
        Route::patch('/admin/despesas/{despesa}/cancelar', [DespesaController::class, 'cancel'])->name('admin.despesas.cancel');
    });

    // Rotas de Configurações - Página unificada com tabs
    Route::middleware(['permission:usuarios.editar'])->group(function () {
        Route::get('/admin/configuracoes', [\App\Http\Controllers\ConfiguracoesController::class, 'index'])->name('admin.configuracoes.index');
        
        // Rotas para gerenciamento de turmas
        Route::get('/turmas', [\App\Http\Controllers\TurmaController::class, 'index'])->name('turmas.index');
Route::get('/admin/turmas/{turma}', [\App\Http\Controllers\TurmaController::class, 'show'])->name('admin.turmas.show');
Route::get('/admin/turmas/{turma}/edit', [\App\Http\Controllers\TurmaController::class, 'edit'])->name('admin.turmas.edit');
Route::post('/admin/turmas', [\App\Http\Controllers\TurmaController::class, 'store'])->name('admin.turmas.store');
Route::put('/admin/turmas/{turma}', [\App\Http\Controllers\TurmaController::class, 'update'])->name('admin.turmas.update');
Route::delete('/admin/turmas/{turma}', [\App\Http\Controllers\TurmaController::class, 'destroy'])->name('admin.turmas.destroy');

        // Rotas para modais de turmas
        Route::get('/admin/turmas/listar-todas', [\App\Http\Controllers\TurmaController::class, 'listarTodas'])->name('admin.turmas.listar-todas');
        Route::post('/admin/turmas/transferir-aluno', [\App\Http\Controllers\TurmaController::class, 'transferirAluno'])->name('admin.turmas.transferir-aluno');
        Route::get('/admin/turmas/{turma}/alunos', [\App\Http\Controllers\TurmaController::class, 'getAlunos'])->name('admin.turmas.alunos');
        Route::get('/admin/turmas/{turma}/alunos-disponiveis', [\App\Http\Controllers\TurmaController::class, 'getAlunosDisponiveis'])->name('admin.turmas.alunos-disponiveis');
        Route::post('/admin/turmas/{turma}/adicionar-aluno', [\App\Http\Controllers\TurmaController::class, 'adicionarAluno'])->name('admin.turmas.adicionar-aluno');
        Route::post('/admin/turmas/{turma}/adicionar-alunos', [\App\Http\Controllers\TurmaController::class, 'adicionarAlunos'])->name('admin.turmas.adicionar-alunos');
        Route::delete('/admin/turmas/{turma}/remover-aluno/{aluno}', [\App\Http\Controllers\TurmaController::class, 'removerAluno'])->name('admin.turmas.remover-aluno');
    });


    // Rotas de Relatórios - requer licença do módulo e permissões específicas
    Route::middleware(['module.license:relatorios_module', 'escola.scope'])->prefix('reports')->name('reports.')->group(function () {

        // Relatórios - visualizar lista e relatório específico
        Route::middleware(['permission:relatorios.ver'])->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');

            // Aqui usamos whereNumber para garantir que {report} é ID
            Route::get('/{report}', [\App\Http\Controllers\ReportController::class, 'view'])
                ->whereNumber('report')
                ->name('show');
        });

        // Relatórios - criar, salvar e excluir
        Route::middleware(['permission:relatorios.gerar'])->group(function () {
            Route::get('/create', [\App\Http\Controllers\ReportController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\ReportController::class, 'store'])->name('store');

            Route::delete('/{report}', [\App\Http\Controllers\ReportController::class, 'destroy'])
                ->whereNumber('report')
                ->name('destroy');
        });

        // Relatórios - exportar/download
        Route::middleware(['permission:relatorios.exportar'])->group(function () {
            Route::get('/{report}/download', [\App\Http\Controllers\ReportController::class, 'download'])
                ->whereNumber('report')
                ->name('download');
        });
    });
});

// Rotas de Notificações
Route::prefix('notifications')->name('notifications.')->middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
    Route::get('/unread', [\App\Http\Controllers\NotificationController::class, 'unread'])->name('unread');
    Route::get('/count', [\App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('count');
    Route::get('/diagnostic', [\App\Http\Controllers\NotificationController::class, 'diagnostic'])->name('diagnostic');
    Route::patch('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
    Route::patch('/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::patch('/mark-multiple-read', [\App\Http\Controllers\NotificationController::class, 'markMultipleAsRead'])->name('mark-multiple-read');
    Route::delete('/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/delete-multiple', [\App\Http\Controllers\NotificationController::class, 'deleteMultiple'])->name('delete-multiple');

    // Rotas administrativas para criar notificações
    Route::middleware(['auth', 'permission:usuarios.editar'])->group(function () {
        Route::get('/create', [\App\Http\Controllers\NotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\NotificationController::class, 'store'])->name('store');
    });
});

// Rotas para troca de escolas (sem middleware escola.context para evitar conflitos)
Route::middleware(['auth'])->prefix('escola-switch')->name('escola-switch.')->group(function () {
    Route::get('/', [App\Http\Controllers\EscolaSwitchController::class, 'index'])->name('index');
    Route::post('/switch', [App\Http\Controllers\EscolaSwitchController::class, 'switch'])->name('switch');
    Route::get('/current', [App\Http\Controllers\EscolaSwitchController::class, 'current'])->name('current');
});

// Rotas de teste para o sistema de alertas (apenas em desenvolvimento)
if (app()->environment('local')) {
    Route::middleware(['auth', 'escola.context'])->group(function () {
        Route::get('/test-alerts', function () {
            return view('test-alerts');
        })->name('test.alerts');

        Route::post('/test-alert', function (\Illuminate\Http\Request $request) {
            $type = $request->input('type', 'info');
            $message = $request->input('message', 'Mensagem de teste');

            switch ($type) {
                case 'success':
                    \App\Services\AlertService::success($message);
                    break;
                case 'error':
                    \App\Services\AlertService::error($message);
                    break;
                case 'warning':
                    \App\Services\AlertService::warning($message);
                    break;
                case 'validation':
                    \App\Services\AlertService::validation($message, ['Campo 1 é obrigatório', 'Campo 2 deve ser um email válido']);
                    break;
                case 'system_error':
                    \App\Services\AlertService::systemError($message);
                    break;
                case 'access_denied':
                    \App\Services\AlertService::accessDenied($message);
                    break;
                default:
                    \App\Services\AlertService::info($message);
            }

            return redirect()->route('test.alerts');
        })->name('test.alert');

        // Rota de teste para disciplinas
        Route::get('/test-disciplinas', function () {
            $modalidades = \App\Models\ModalidadeEnsino::where('ativo', true)->pluck('nome', 'id');
            return view('test-disciplinas', compact('modalidades'));
        })->name('test.disciplinas');
    });
}

// Rotas do Painel Administrativo
use App\Http\Controllers\AdminController;

// Login específico para Super Administradores
Route::get('/superadmin/login', [\App\Http\Controllers\SuperAdminController::class, 'showLogin'])
    ->name('admin.superadmin.login');
Route::post('/superadmin/login', [\App\Http\Controllers\SuperAdminController::class, 'processLogin'])
    ->name('admin.superadmin.login.process');

// Painel de Super Administrador (acesso exclusivo)
Route::middleware('superadmin.only')->group(function () {
    Route::get('/superadmin', [\App\Http\Controllers\SuperAdminController::class, 'dashboard'])
        ->name('admin.superadmin.dashboard');

    // Gestão avançada de usuários
    Route::get('/superadmin/users', [\App\Http\Controllers\SuperAdminController::class, 'users'])
        ->name('admin.superadmin.users.index');
    Route::get('/superadmin/users/create-admin', [\App\Http\Controllers\SuperAdminController::class, 'createAdmin'])
        ->name('admin.superadmin.users.create-admin');
    Route::post('/superadmin/users/store-admin', [\App\Http\Controllers\SuperAdminController::class, 'storeAdmin'])
        ->name('admin.superadmin.users.store-admin');

    // Gerenciamento de licenças (exclusivo para super admin)
    Route::get('/superadmin/licencas', [\App\Http\Controllers\SuperAdminController::class, 'licencas'])
        ->name('admin.superadmin.licencas.index');

    // Relatórios consolidados (exclusivo para super admin)
    Route::get('/superadmin/relatorios', [\App\Http\Controllers\SuperAdminController::class, 'relatorios'])
        ->name('admin.superadmin.relatorios');

    // Configurações do sistema
    Route::get('/superadmin/system/settings', [\App\Http\Controllers\SuperAdminController::class, 'systemSettings'])
        ->name('admin.superadmin.system.settings');
    Route::post('/superadmin/system/settings', [\App\Http\Controllers\SuperAdminController::class, 'updateSystemSettings'])
        ->name('admin.superadmin.system.update-settings');

    // Logs do sistema
    Route::get('/superadmin/system/logs', [\App\Http\Controllers\SuperAdminController::class, 'systemLogs'])
        ->name('admin.superadmin.system.logs');

    // Backup e manutenção
    Route::post('/superadmin/system/backup', [\App\Http\Controllers\SuperAdminController::class, 'createBackup'])
        ->name('admin.superadmin.system.backup');
    Route::post('/superadmin/system/clear-cache', [\App\Http\Controllers\SuperAdminController::class, 'clearCache'])
        ->name('admin.superadmin.system.clear-cache');
});



// Redirecionamento das rotas antigas de admin para corporativo (migração realizada)
Route::get('/admin/login', function () {
    return redirect()->route('corporativo.login');
});

Route::post('/admin/login', function () {
    return redirect()->route('corporativo.login');
});

// Redirecionamento de todas as rotas antigas de admin para corporativo
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard administrativo - redirecionar para corporativo
    Route::get('/dashboard', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('dashboard');

    // Ações do sistema - redirecionar para corporativo
    Route::post('/clear-cache', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('clear.cache');
    
    Route::post('/optimize-system', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('optimize.system');
    
    Route::post('/run-maintenance', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('run.maintenance');
    
    Route::post('/generate-report', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('generate.report');
    
    Route::get('/table-info/{tableName}', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('table.info');
    
    Route::get('/table-columns/{tableName}', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('table.columns');

    Route::get('/download-report/{filename}', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('download.report');

    // Relacionamentos entre tabelas do banco - redirecionar para corporativo
    Route::get('/database-relationships', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('database.relationships');
    
    Route::get('/api/database-relationships', function () {
        return redirect()->route('corporativo.dashboard');
    })->name('api.database.relationships');

    // Logout - redirecionar para corporativo
    Route::post('/logout', function () {
        return redirect()->route('corporativo.login');
    })->name('logout');
    
    // Capturar qualquer outra rota de admin e redirecionar
    Route::any('{any}', function () {
        return redirect()->route('corporativo.login');
    })->where('any', '.*');
});

// Rotas do Painel Corporativo (Super Admin/Suporte)
use App\Http\Controllers\CorporativoController;
use App\Http\Controllers\CorporativoPlansController;
use App\Http\Controllers\CorporativoModulesController;

// Login corporativo (sem middleware de autenticação)
Route::prefix('corporativo')->name('corporativo.')->group(function () {
    Route::post('/login', [CorporativoController::class, 'login'])->name('login.post');
    Route::get('/login', [CorporativoController::class, 'showLogin'])->name('login');
    
});

// Rotas protegidas do painel corporativo
Route::middleware(['admin.auth'])->prefix('corporativo')->name('corporativo.')->group(function () {
    // Dashboard corporativo
    Route::get('/dashboard', [CorporativoController::class, 'dashboard'])->name('dashboard');

    // Atualizações do Sistema - CRUD
    Route::prefix('atualizacoes')->name('atualizacoes.')->group(function () {
        Route::get('/', [SystemUpdateController::class, 'index'])->name('index');
        Route::get('/create', [SystemUpdateController::class, 'create'])->name('create');
        Route::post('/', [SystemUpdateController::class, 'store'])->name('store');
        Route::get('/{atualizacao}/edit', [SystemUpdateController::class, 'edit'])
            ->whereNumber('atualizacao')->name('edit');
        Route::put('/{atualizacao}', [SystemUpdateController::class, 'update'])
            ->whereNumber('atualizacao')->name('update');
        Route::delete('/{atualizacao}', [SystemUpdateController::class, 'destroy'])
            ->whereNumber('atualizacao')->name('destroy');
    });

    // Gerenciamento de usuários
    Route::get('/users', [CorporativoController::class, 'users'])->name('users');
    Route::get('/users/create', [CorporativoController::class, 'createUser'])->name('users.create');
    Route::post('/users', [CorporativoController::class, 'storeUser'])->name('users.store');
    Route::get('/users/{user}/edit', [CorporativoController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [CorporativoController::class, 'updateUser'])->name('users.update');

    // Visualização de permissões
    Route::get('/permissions', [CorporativoController::class, 'permissions'])->name('permissions');

    // Gestão de Módulos
    Route::get('/modules', [CorporativoModulesController::class, 'index'])->name('modules.index');
    Route::get('/modules/create', [CorporativoModulesController::class, 'create'])->name('modules.create');
    Route::post('/modules', [CorporativoModulesController::class, 'store'])->name('modules.store');
    Route::get('/modules/{module}/edit', [CorporativoModulesController::class, 'edit'])->name('modules.edit');
    Route::put('/modules/{module}', [CorporativoModulesController::class, 'update'])->name('modules.update');
    Route::put('/modules/{module}/deactivate', [CorporativoModulesController::class, 'deactivate'])->name('modules.deactivate');

    // Gerenciamento de escolas
    Route::get('/escolas', [CorporativoController::class, 'escolas'])->name('escolas');
    Route::get('/escolas/{id}', [CorporativoController::class, 'escolaDetalhes'])->name('escolas.detalhes');
    Route::get('/api/escolas', [CorporativoController::class, 'escolasApi'])->name('escolas.api');
    // API de planos (para selects e integrações internas)
    Route::get('/api/plans', [CorporativoPlansController::class, 'api'])->name('plans.api');

    // Planos - gerenciamento corporativo
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [CorporativoPlansController::class, 'index'])->name('index');
        Route::get('/create', [CorporativoPlansController::class, 'create'])->name('create');
        Route::post('/', [CorporativoPlansController::class, 'store'])->name('store');
        Route::get('/{plan}/edit', [CorporativoPlansController::class, 'edit'])->whereNumber('plan')->name('edit');
        Route::put('/{plan}', [CorporativoPlansController::class, 'update'])->whereNumber('plan')->name('update');
        Route::patch('/{plan}/toggle', [CorporativoPlansController::class, 'toggle'])->whereNumber('plan')->name('toggle');
    });

    // Gerenciamento de licenças
    Route::get('/licencas', [CorporativoController::class, 'licencas'])->name('licencas');

    // Relatórios
    Route::get('/relatorios', [CorporativoController::class, 'relatorios'])->name('relatorios');
    // KPIs
    Route::get('/kpis', [CorporativoController::class, 'kpis'])->name('kpis');
    
    // Query Builder
    Route::post('/query-builder', [CorporativoController::class, 'executeQueryBuilder'])->name('query.execute');
    Route::get('/query-builder', [CorporativoController::class, 'showQueryBuilder'])->name('query.builder');


    // Configuração Educacional - Administração (SuperAdmin/Suporte)
    Route::get('/configuracao-educacional', [\App\Http\Controllers\CorporativoConfiguracaoEducacionalController::class, 'index'])->name('configuracao-educacional.index');
    Route::get('/configuracao-educacional/relatorio', [\App\Http\Controllers\CorporativoConfiguracaoEducacionalController::class, 'relatorio'])->name('configuracao-educacional.relatorio');

    // Informações do sistema
    Route::get('/system-info', [CorporativoController::class, 'systemInfo'])->name('system.info');

    // Ações do sistema
    Route::post('/clear-cache', [CorporativoController::class, 'clearCache'])->name('clear.cache');
    Route::post('/optimize-system', [CorporativoController::class, 'optimizeSystem'])->name('optimize.system');
    Route::post('/run-maintenance', [CorporativoController::class, 'runMaintenance'])->name('run.maintenance');
    Route::post('/generate-report', [CorporativoController::class, 'generateReport'])->name('generate.report');
    Route::get('/table-info/{tableName}', [CorporativoController::class, 'getTableInfo'])->name('table.info');
    Route::get('/table-columns/{tableName}', [CorporativoController::class, 'getTableColumns'])->name('table.columns');
    Route::get('/download-report/{filename}', [CorporativoController::class, 'downloadReport'])->name('download.report');

    // Relacionamentos entre tabelas do banco
    Route::get('/database-relationships', [CorporativoController::class, 'databaseRelationships'])->name('database.relationships');
    Route::get('/api/database-relationships', [CorporativoController::class, 'getDatabaseRelationshipsApi'])->name('api.database.relationships');

    // Logout
    Route::post('/logout', [CorporativoController::class, 'logout'])->name('logout');
});

// Rotas do Sistema de Comunicação Escolar
use App\Http\Controllers\ConversaController;
use App\Http\Controllers\ComunicadoController;

Route::middleware(['auth', 'escola.context', 'module.license:comunicacao_module'])->group(function () {
    // Conversas - requer permissões específicas e licença do módulo de comunicação
    Route::prefix('conversas')->name('conversas.')->group(function () {
        // 🔹 Criar conversa (precisa vir ANTES do {conversa})
        Route::middleware(['permission:conversas.criar'])->group(function () {
            Route::get('/create', [ConversaController::class, 'create'])->name('create');
            Route::post('/', [ConversaController::class, 'store'])->name('store');
        });

        // 🔹 Ver conversas
        Route::middleware(['permission:conversas.ver'])->group(function () {
            Route::get('/', [ConversaController::class, 'index'])->name('index');
            Route::get('/lista', [ConversaController::class, 'carregarListaConversas'])->name('lista');
            Route::get('/{conversa}', [ConversaController::class, 'show'])
                ->whereNumber('conversa')->name('show');
            Route::get('/{conversa}/mensagens', [ConversaController::class, 'mensagens'])
                ->whereNumber('conversa')->name('mensagens');
            Route::get('/{conversa}/carregar-mensagens', [ConversaController::class, 'carregarMensagens'])
                ->whereNumber('conversa')->name('carregar-mensagens');
            Route::get('/{conversa}/mensagens-anteriores', [ConversaController::class, 'mensagensAnteriores'])
                ->whereNumber('conversa')->name('mensagens-anteriores');
            Route::get('/{conversa}/buscar-mensagens', [ConversaController::class, 'buscarMensagens'])
                ->whereNumber('conversa')->name('buscar-mensagens');
            Route::get('/{conversa}/buscar-usuarios', [ConversaController::class, 'buscarUsuarios'])
                ->whereNumber('conversa')->name('buscar-usuarios');
            Route::post('/{conversa}/marcar-lida', [ConversaController::class, 'marcarComoLida'])
                ->whereNumber('conversa')->name('marcar-lida');
        });

        // 🔹 Participar de conversas
        Route::middleware(['permission:conversas.participar'])->group(function () {
            Route::post('/{conversa}/mensagens', [ConversaController::class, 'enviarMensagem'])
                ->whereNumber('conversa')->name('enviar-mensagem');
            Route::post('/{conversa}/participantes', [ConversaController::class, 'adicionarParticipante'])
                ->whereNumber('conversa')->name('adicionar-participante');
            Route::delete('/{conversa}/participantes/{user}', [ConversaController::class, 'removerParticipante'])
                ->whereNumber('conversa')->whereNumber('user')->name('remover-participante');
            Route::patch('/{conversa}/arquivar', [ConversaController::class, 'arquivar'])
                ->whereNumber('conversa')->name('arquivar');
            Route::patch('/{conversa}/finalizar', [ConversaController::class, 'finalizar'])
                ->whereNumber('conversa')->name('finalizar');
            Route::delete('/{conversa}', [ConversaController::class, 'destroy'])
                ->whereNumber('conversa')->name('destroy');
        });
    });

    // Comunicados - Módulo com Feature Toggle e Licenciamento
    Route::middleware(['module.license:comunicacao_module'])->group(function () {
        Route::prefix('comunicados')->name('comunicados.')->group(function () {
            Route::get('/', [ComunicadoController::class, 'index'])->name('index');
            Route::get('/create', [ComunicadoController::class, 'create'])->name('create');
            Route::post('/', [ComunicadoController::class, 'store'])->name('store');
            Route::get('/{comunicado}', [ComunicadoController::class, 'show'])->name('show');
            Route::get('/{comunicado}/edit', [ComunicadoController::class, 'edit'])->name('edit');
            Route::put('/{comunicado}', [ComunicadoController::class, 'update'])->name('update');
            Route::delete('/{comunicado}', [ComunicadoController::class, 'destroy'])->name('destroy');
            Route::post('/{comunicado}/publicar', [ComunicadoController::class, 'publicar'])->name('publicar');
            Route::post('/{comunicado}/despublicar', [ComunicadoController::class, 'despublicar'])->name('despublicar');
            Route::post('/{comunicado}/confirmar', [ComunicadoController::class, 'confirmar'])->name('confirmar');
            Route::get('/{comunicado}/relatorio-confirmacoes', [ComunicadoController::class, 'relatorioConfirmacoes'])->name('relatorio-confirmacoes');
        });

        // Rota alternativa /comunicacao que redireciona para /comunicados
        Route::get('/comunicacao', function () {
            return redirect()->route('comunicados.index');
        })->name('comunicacao.index');
    });

    // Rotas de Grade de Aulas - requer licença do módulo acadêmico
    Route::middleware(['module.license:academico_module', 'escola.scope'])->group(function () {
        Route::prefix('grade-aulas')->name('grade-aulas.')->group(function () {
            // Rotas específicas primeiro (antes das rotas com parâmetros)
            Route::middleware(['permission:grade_aulas.criar'])->group(function () {
                Route::get('/create', [\App\Http\Controllers\GradeAulaController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\GradeAulaController::class, 'store'])->name('store');
            });

            // Rotas básicas do resource
            Route::middleware(['permission:grade_aulas.visualizar'])->group(function () {
                Route::get('/', [\App\Http\Controllers\GradeAulaController::class, 'index'])->name('index');
                Route::get('/{gradeAula}', [\App\Http\Controllers\GradeAulaController::class, 'show'])
                    ->where('gradeAula', '[0-9]+')
                    ->name('show');
            });

            Route::middleware(['permission:grade_aulas.editar'])->group(function () {
                Route::get('/{gradeAula}/edit', [\App\Http\Controllers\GradeAulaController::class, 'edit'])
                    ->where('gradeAula', '[0-9]+')
                    ->name('edit');
                Route::get('/{gradeAula}/edit-modal', [\App\Http\Controllers\GradeAulaController::class, 'editModal'])
                    ->where('gradeAula', '[0-9]+')
                    ->name('edit.modal');
                Route::put('/{gradeAula}', [\App\Http\Controllers\GradeAulaController::class, 'update'])
                    ->where('gradeAula', '[0-9]+')
                    ->name('update');
            });

            Route::middleware(['permission:grade_aulas.excluir'])->group(function () {
                Route::delete('/{gradeAula}', [\App\Http\Controllers\GradeAulaController::class, 'destroy'])
                    ->where('gradeAula', '[0-9]+')
                    ->name('destroy');
            });

            // Rotas auxiliares para consultas
            Route::middleware(['permission:grade_aulas.visualizar'])->group(function () {
                Route::get('/salas/disponiveis', [\App\Http\Controllers\GradeAulaController::class, 'salasDisponiveis'])->name('salas.disponiveis');
                Route::get('/professores/disponiveis', [\App\Http\Controllers\GradeAulaController::class, 'professoresDisponiveis'])->name('professores.disponiveis');
                Route::get('/turma/{turma}/grade', [\App\Http\Controllers\GradeAulaController::class, 'gradeTurma'])->name('turma.grade');
                Route::get('/sala/{sala}/ocupacao', [\App\Http\Controllers\GradeAulaController::class, 'ocupacaoSala'])->name('sala.ocupacao');
                Route::post('/verificar-conflitos', [\App\Http\Controllers\GradeAulaController::class, 'verificarConflitos'])->name('verificar.conflitos');
                
                // Novas rotas para sugestões inteligentes
                Route::post('/sugestoes/horarios', [\App\Http\Controllers\GradeAulaController::class, 'obterSugestoesHorarios'])->name('sugestoes.horarios');
                Route::post('/sugestoes/salas', [\App\Http\Controllers\GradeAulaController::class, 'obterSugestoesSalas'])->name('sugestoes.salas');
                Route::post('/professores/alternativos', [\App\Http\Controllers\GradeAulaController::class, 'obterProfessoresAlternativos'])->name('professores.alternativos');
                Route::post('/disciplinas/por-turma', [\App\Http\Controllers\GradeAulaController::class, 'obterDisciplinasPorTurma'])->name('disciplinas.por-turma');
                Route::post('/tempo-slots/por-turma', [\App\Http\Controllers\GradeAulaController::class, 'obterTempoSlotsPorTurma'])->name('tempo-slots.por-turma');
                // Endpoint JSON para listar aulas por turma (sem paginação)
                Route::get('/por-turma', [\App\Http\Controllers\GradeAulaController::class, 'listarPorTurma'])->name('por-turma');
            });
        });
    });
});

// Painel Financeiro por Escola
Route::middleware(['auth', 'escola.context', 'permission:finance.admin'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/settings', [FinanceAdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [FinanceAdminController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/test-email', [FinanceAdminController::class, 'testDunningEmail'])->name('settings.test_dunning_email');

    // Endpoints de configurações de e-mail por escola
    Route::get('/mail-settings', [FinanceAdminController::class, 'getMailSettings'])->name('mail_settings.get');
    Route::post('/mail-settings', [FinanceAdminController::class, 'saveMailSettings'])->name('mail_settings.save');
    Route::post('/mail-settings/verify-dns', [FinanceAdminController::class, 'verifyMailDNS'])->name('mail_settings.verify_dns');

    Route::get('/gateways', [FinanceAdminController::class, 'gateways'])->name('gateways');
    Route::post('/gateways', [FinanceAdminController::class, 'createGateway'])->name('gateways.create');
    Route::put('/gateways/{id}', [FinanceAdminController::class, 'updateGateway'])->name('gateways.update');
    Route::post('/gateways/test', [FinanceAdminController::class, 'testGatewayCredentials'])->name('gateways.test');
});

// Página unificada de configurações
Route::middleware(['auth', 'escola.context'])->get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');

// Rota de download do PDF anual do calend�rio (protegida por licen�a e permiss�o)
Route::middleware(['module.license:eventos_module', 'permission:eventos.ver'])->group(function () {
    Route::get('/calendario/pdf', [\App\Http\Controllers\CalendarController::class, 'downloadAnnualPdf'])->name('calendario.pdf');
});
