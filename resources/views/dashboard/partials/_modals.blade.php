@if (auth()->check() && is_null(auth()->user()->welcome_seen_at))
    <x-modal name="welcome-modal" :show="true" title="Bem-vindo ao Sistema de Gerenciamento Escolar! 🎉">
        <div class="space-y-4">
            <div class="text-gray-700 space-y-3">
                <p class="text-sm leading-relaxed">
                    É um prazer recebê-lo nesta jornada que vai transformar a gestão da sua instituição de ensino.
                    Estamos genuinamente felizes por você estar aqui e prontos para acompanhar cada passo seu rumo a uma
                    rotina mais organizada, eficiente e conectada.
                </p>

                <p class="text-sm leading-relaxed">
                    Esta plataforma foi cuidadosamente desenvolvida pensando nas necessidades reais de diretores,
                    coordenadores, professores, secretários, alunos e responsáveis. Nosso objetivo? Unir todos em um
                    único ambiente digital seguro, intuitivo e colaborativo, eliminando planilhas perdidas, e-mails
                    confusos e comunicações fragmentadas.
                </p>

                <p class="text-sm leading-relaxed">
                    Seja para registrar uma nota às pressas, enviar um comunicado urgente sobre suspensão de aulas,
                    consultar o histórico de um aluno ou planejar o calendário letivo do próximo semestre — tudo está a
                    poucos cliques, de forma simples e segura.
                </p>

                <p class="text-sm font-medium text-indigo-600">
                    Estamos aqui por você!
                </p>
            </div>

            <div class="pt-2 flex justify-end">
                <button id="welcome-dismiss-btn"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                    Começar
                </button>
            </div>
        </div>
    </x-modal>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('welcome-dismiss-btn');
            if (!btn) return;
            btn.addEventListener('click', async function() {
                try {
                    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
                    const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
                    await fetch("{{ route('welcome.dismiss') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        }
                    });
                } catch (e) {
                    console.error('Falha ao registrar boas-vindas vistas:', e);
                } finally {
                    // Fecha o modal na interface imediatamente
                    if (typeof closeModal === 'function') {
                        closeModal('welcome-modal');
                    } else {
                        const overlay = document.querySelector('[x-data]');
                        if (overlay) overlay.classList.add('hidden');
                    }
                }
            });
        });
    </script>
@endif

@if (function_exists('moduleEnabled')
        ? moduleEnabled('financeiro_module')
        : config('features.modules.financeiro_module') ?? true)
    <x-personalize-modal id="modalPersonalizarCards" title="Personalizar cards" errorId="modalPrefError">
        <x-slot name="body">
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-coins text-emerald-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Receitas (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-emerald-600"
                    data-card-key="finance-receitas">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-file-invoice-dollar text-rose-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Despesas (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-rose-600"
                    data-card-key="finance-despesas">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-exclamation-circle text-orange-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Inadimplência (mês)</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-orange-600"
                    data-card-key="finance-inadimplencia">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-life-ring text-indigo-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Tickets abertos</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-indigo-600"
                    data-card-key="finance-tickets">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-sync-alt text-teal-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">MRR</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-teal-600"
                    data-card-key="finance-mrr">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-credit-card text-cyan-600 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Método predominante</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-cyan-600"
                    data-card-key="finance-metodo">
            </label>
            <label class="flex items-center justify-between p-3 border rounded-md">
                <div class="flex items-center"><i class="fas fa-border-none text-gray-500 mr-2"></i><span
                        class="text-sm font-medium text-gray-900">Espaço em branco</span></div>
                <input type="checkbox" class="toggle-card form-checkbox h-4 w-4 text-gray-500"
                    data-card-key="finance-blank">
            </label>
        </x-slot>
        <x-slot name="footerRight">
            <button class="px-4 py-2 text-sm rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200"
                data-action="close-modal">Fechar</button>
            <button class="px-4 py-2 text-sm rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                data-action="restore-default">Restaurar padrão</button>
            <button
                class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-2"
                data-action="save-preferences">
                <span class="save-text">Salvar</span>
                <span class="save-loading hidden"><i class="fas fa-spinner fa-spin"></i></span>
            </button>
        </x-slot>
    </x-personalize-modal>
@endif

<x-personalize-modal id="modalPersonalizarAnalyticsCards" title="Personalizar cards" errorId="modalAnalyticsPrefError">
    <x-slot name="body">
        <!-- Métricas (verde) -->
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Taxa de Presença</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600"
                data-card-key="analytics-metric-presenca" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Alertas de Frequência</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600"
                data-card-key="analytics-metric-alertas" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Professores Ativos</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-emerald-600"
                data-card-key="analytics-metric-professores" checked>
        </label>
        <!-- Gráficos (ciano) -->
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Presenças por Dia</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-cyan-600"
                data-card-key="analytics-chart-dia" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Top 5 Salas</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-cyan-600"
                data-card-key="analytics-chart-sala" checked>
        </label>
        <!-- Tabelas (cinza) -->
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Professores Ativos (tabela)</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-gray-500"
                data-card-key="analytics-table-professores" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Alertas de Frequência (tabela)</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-gray-500"
                data-card-key="analytics-table-alertas" checked>
        </label>
        <!-- Recentes (índigo) -->
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Atividades Recentes</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-indigo-600"
                data-card-key="analytics-recentes-atividades" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Estatísticas Rápidas</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-indigo-600"
                data-card-key="analytics-recentes-estatisticas" checked>
        </label>
        <!-- Extras (teal) -->
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Últimos Alunos</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-teal-600"
                data-card-key="analytics-extra-ultimos-alunos" checked>
        </label>
        <label class="flex items-center justify-between p-3 border rounded-md">
            <span class="text-sm font-medium text-gray-900">Presenças de Hoje</span>
            <input type="checkbox" class="toggle-analytics-card form-checkbox h-4 w-4 text-teal-600"
                data-card-key="analytics-extra-presencas-hoje" checked>
        </label>
    </x-slot>
    <x-slot name="footerLeft">
        <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid"
            data-grid-id="analyticsMetricsGrid">Restaurar Métricas</button>
        <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid"
            data-grid-id="analyticsChartsGrid">Restaurar Gráficos</button>
        <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid"
            data-grid-id="analyticsTablesGrid">Restaurar Tabelas</button>
        <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid"
            data-grid-id="analyticsRecentGrid">Restaurar Recentes</button>
        <button class="text-xs text-blue-700 hover:underline" data-action="restore-analytics-grid"
            data-grid-id="analyticsExtraGrid">Restaurar Extras</button>
        <button class="ml-2 px-2 py-1 text-xs rounded-md bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
            data-action="restore-analytics-all">Restaurar tudo (Analytics)</button>
    </x-slot>
    <x-slot name="footerRight">
        <button class="px-4 py-2 text-sm rounded-md bg-gray-100 text-gray-800 hover:bg-gray-200"
            data-action="close-modal">Fechar</button>
        <button
            class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-2"
            data-action="save-analytics-preferences">
            <span class="save-text">Salvar</span>
            <span class="save-loading hidden"><i class="fas fa-spinner fa-spin"></i></span>
        </button>
    </x-slot>
</x-personalize-modal>
